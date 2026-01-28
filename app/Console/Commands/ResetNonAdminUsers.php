<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CommissionLedger;
use App\Models\ReferralCommission;
use App\Models\Payout;
use App\Models\Cart;
use App\Models\UserProfile;
use App\Models\Store;
use App\Models\ReferralFollowUp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ResetNonAdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-non-admin {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all non-super-admin users and their related data safely.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will DELETE ALL non-admin users and their data. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Starting data cleanup...');

        // 1. Identify Super Admins to Keep
        // We use the role name 'SUPER_ADMIN' to identify them.
        $superAdmins = User::role('SUPER_ADMIN')->get();
        
        if ($superAdmins->isEmpty()) {
            $this->error('No SUPER_ADMIN found! Aborting to prevent full system wipe.');
            return;
        }

        $keepIds = $superAdmins->pluck('id')->toArray();
        $this->info('Found ' . count($keepIds) . ' Super Admin(s) to keep: IDs ' . implode(', ', $keepIds));

        // 2. Identify Users to Delete
        $usersToDelete = User::whereNotIn('id', $keepIds)->get();
        $deleteIds = $usersToDelete->pluck('id')->toArray();
        $count = count($deleteIds);

        if ($count === 0) {
            $this->info('No non-admin users found to delete.');
            return;
        }

        $this->info("Found {$count} users to delete.");

        // 3. Backup Data
        $this->backupData($deleteIds);

        // 4. Perform Deletion in Transaction
        DB::beginTransaction();
        try {
            $this->performDeletion($deleteIds);
            
            DB::commit();
            $this->info('Deletion completed successfully.');
            
            // 5. Verification
            $remainingUsers = User::count();
            $this->info("Remaining users: {$remainingUsers} (Should match Super Admin count: " . count($keepIds) . ")");
            
            if ($remainingUsers == count($keepIds)) {
                $this->info('VERIFICATION PASSED: Only Super Admins remain.');
            } else {
                $this->warn('VERIFICATION FAILED: Mismatch in expected user count.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during deletion: ' . $e->getMessage());
            $this->error('Transaction rolled back. No data was deleted.');
        }
    }

    protected function backupData(array $userIds)
    {
        $this->info('Backing up data...');
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = storage_path("app/backups/deleted_users_{$timestamp}.json");
        
        // Ensure directory exists
        if (!File::exists(dirname($backupPath))) {
            File::makeDirectory(dirname($backupPath), 0755, true);
        }

        // We backup core user data and a summary of their relations
        // Due to memory constraints, we chunk or limit, but here we'll try to export the users.
        $users = User::with(['profile', 'roles'])->whereIn('id', $userIds)->get();
        
        $data = [
            'meta' => [
                'timestamp' => $timestamp,
                'count' => count($userIds),
                'description' => 'Backup before db:reset-non-admin'
            ],
            'users' => $users->toArray()
        ];

        File::put($backupPath, json_encode($data, JSON_PRETTY_PRINT));
        
        $this->info("Backup saved to: {$backupPath}");
    }

    protected function performDeletion(array $userIds)
    {
        $bar = $this->output->createProgressBar(12);
        $bar->start();

        // Step 1: Nullify Referrer IDs to break self-references
        $this->info("\nNullifying referrer links...");
        User::whereIn('referrer_id', $userIds)->update(['referrer_id' => null]);
        $bar->advance();

        // Step 2: Delete Referral Commissions
        // Delete where user is referrer OR referred
        $this->info("\nDeleting Referral Commissions...");
        ReferralCommission::whereIn('referrer_id', $userIds)
            ->orWhereIn('referred_user_id', $userIds)
            ->delete();
        $bar->advance();

        // Step 3: Delete Referral Follow Ups
        $this->info("\nDeleting Referral Follow Ups...");
        ReferralFollowUp::whereIn('referrer_id', $userIds)
            ->orWhereIn('referred_user_id', $userIds)
            ->delete();
        $bar->advance();

        // Step 4: Delete Commission Ledgers & Payouts
        $this->info("\nDeleting Ledgers & Payouts...");
        CommissionLedger::whereIn('user_id', $userIds)->delete();
        Payout::whereIn('user_id', $userIds)->delete();
        $bar->advance();

        // Step 5: Delete Carts
        $this->info("\nDeleting Carts...");
        Cart::whereIn('user_id', $userIds)->delete();
        $bar->advance();

        // Step 6: Delete Orders (and related)
        $this->info("\nDeleting Orders & Payments...");
        // Get order IDs to delete related payments/items first if cascade isn't reliable
        $orders = Order::whereIn('user_id', $userIds)->get();
        $orderIds = $orders->pluck('id')->toArray();
        
        if (!empty($orderIds)) {
            // Delete Order Items
            DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
            // Delete Order Logs
            DB::table('order_logs')->whereIn('order_id', $orderIds)->delete();
            // Delete Payments linked to orders
            // Assuming payments have order_id or we find them via order
            // Check Payment model usually has order_id
            Payment::whereIn('order_id', $orderIds)->delete();
            
            // Delete Orders
            Order::whereIn('id', $orderIds)->delete();
        }
        $bar->advance();

        // Step 7: Delete Stores
        $this->info("\nDeleting Stores...");
        $stores = Store::whereIn('user_id', $userIds)->get();
        $storeIds = $stores->pluck('id')->toArray();
        if (!empty($storeIds)) {
            // Manually delete related store tables if they don't cascade
            if (\Schema::hasTable('store_contacts')) {
                DB::table('store_contacts')->whereIn('store_id', $storeIds)->delete();
            }
            if (\Schema::hasTable('store_operating_hours')) {
                DB::table('store_operating_hours')->whereIn('store_id', $storeIds)->delete();
            }
            if (\Schema::hasTable('store_shipping_options')) {
                DB::table('store_shipping_options')->whereIn('store_id', $storeIds)->delete();
            }
            Store::whereIn('id', $storeIds)->delete();
        }
        $bar->advance();

        // Step 8: Delete User Profiles
        $this->info("\nDeleting User Profiles...");
        UserProfile::whereIn('user_id', $userIds)->delete();
        $bar->advance();

        // Step 9: Delete Notifications
        $this->info("\nDeleting Notifications...");
        DB::table('notifications')->whereIn('notifiable_id', $userIds)->where('notifiable_type', 'App\Models\User')->delete();
        $bar->advance();

        // Step 10: Delete Audit Logs
        $this->info("\nDeleting Audit Logs...");
        // Assuming 'user_id' column exists in audit_logs
        // Also check if 'model_id' + 'model_type' = User needs deletion
        if (\Schema::hasColumn('audit_logs', 'user_id')) {
            DB::table('audit_logs')->whereIn('user_id', $userIds)->delete();
        }
        DB::table('audit_logs')->where('model_type', 'App\Models\User')->whereIn('model_id', $userIds)->delete();
        $bar->advance();

        // Step 11: Delete API/Integration Logs (Optional but good)
        $this->info("\nDeleting API Logs...");
        if (\Schema::hasColumn('api_logs', 'user_id')) {
             DB::table('api_logs')->whereIn('user_id', $userIds)->delete();
        }
        $bar->advance();

        // Step 12: Delete Users
        $this->info("\nDeleting Users...");
        User::whereIn('id', $userIds)->delete();
        $bar->advance();

        $bar->finish();
        $this->output->newLine();
    }
}
