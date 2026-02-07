<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Spatie\Permission\Models\Role;

class CleanupData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:cleanup-data {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely deletes all orders and non-super-admin users while preserving data integrity.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will PERMANENTLY DELETE all orders and non-super-admin users. This action cannot be undone. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $startTime = microtime(true);
        $this->info('Starting system cleanup...');

        DB::beginTransaction();

        try {
            // 1. Identify Super Admins to Preserve
            $superAdminRole = Role::where('name', 'SUPER_ADMIN')->first();
            
            if (!$superAdminRole) {
                $this->error('SUPER_ADMIN role not found in database. Aborting to prevent accidental data loss.');
                DB::rollBack();
                return;
            }

            // Get IDs of users with SUPER_ADMIN role
            $superAdminIds = User::role('SUPER_ADMIN')->pluck('id')->toArray();
            
            if (empty($superAdminIds)) {
                if (!$this->confirm('No Super Admin users found. This will delete ALL users. Continue?')) {
                    DB::rollBack();
                    return;
                }
            } else {
                $this->info('Found ' . count($superAdminIds) . ' Super Admin(s) (IDs: ' . implode(', ', $superAdminIds) . '). These will be preserved.');
            }

            // 2. Delete Orders
            // We use Eloquent to allow for file deletion if needed, but for bulk we want efficiency.
            // Since we want to ensure integrity, we iterate to delete files, then forceDelete.
            
            $this->info('Scanning orders...');
            $orders = Order::withTrashed()->get(); // Include soft-deleted orders
            $orderCount = $orders->count();
            
            if ($orderCount > 0) {
                $this->output->progressStart($orderCount);
                
                foreach ($orders as $order) {
                    // Delete Order Proof of Payment
                    if ($order->proof_of_payment && Storage::disk('public')->exists($order->proof_of_payment)) {
                        Storage::disk('public')->delete($order->proof_of_payment);
                    }

                    // Delete Related Payments Proofs
                    foreach ($order->payments()->withTrashed()->get() as $payment) {
                        if ($payment->proof_file && Storage::disk('public')->exists($payment->proof_file)) {
                            Storage::disk('public')->delete($payment->proof_file);
                        }
                    }

                    // Force delete the order.
                    // Because of 'cascadeOnDelete' in migrations for items, payments, logs, etc.,
                    // this will clean up related tables.
                    $order->forceDelete();
                    
                    $this->output->progressAdvance();
                }
                
                $this->output->progressFinish();
            }
            
            $this->info("Deleted $orderCount orders and related files.");

            // 3. Delete Users (except Super Admin)
            $this->info('Scanning users...');
            
            // PRE-STEP: Break self-referencing constraints (referrer_id)
            // We set referrer_id to null for all users to ensure we can delete them without FK violations.
            // Since we are deleting almost everyone, we can just bulk update.
            $this->info('Breaking self-referencing constraints...');
            User::query()->update(['referrer_id' => null]);

            $usersQuery = User::query();
            
            if (!empty($superAdminIds)) {
                $usersQuery->whereNotIn('id', $superAdminIds);
            }
            
            $users = $usersQuery->get();
            $userCount = $users->count();
            
            if ($userCount > 0) {
                $this->output->progressStart($userCount);
                
                foreach ($users as $user) {
                    // Delete Profile Picture
                    if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                        Storage::disk('public')->delete($user->profile_picture);
                    }
                    
                    // Delete User.
                    // Because of 'cascadeOnDelete' in migrations for commissions, ledgers, etc.,
                    // this will clean up related tables.
                    $user->delete(); // User model does not use SoftDeletes, so this is permanent.
                    
                    $this->output->progressAdvance();
                }
                
                $this->output->progressFinish();
            }
            
            $this->info("Deleted $userCount users and related data.");

            DB::commit();
            
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("--------------------------------------------------");
            $this->info("Cleanup Completed Successfully in {$duration} seconds.");
            $this->info("Summary:");
            $this->info("- Preserved Super Admins: " . count($superAdminIds));
            $this->info("- Deleted Orders: $orderCount");
            $this->info("- Deleted Users: $userCount");
            $this->info("--------------------------------------------------");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during cleanup: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            $this->line('Transaction rolled back. No data was modified.');
        }
    }
}
