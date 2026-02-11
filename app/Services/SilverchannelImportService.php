<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class SilverchannelImportService
{
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $errors = [];
    protected $logs = [];

    public function getHeaders(): array
    {
        return [
            'id_silverchannel',
            'nama_channel',
            'email',
            'telepon',
            'nik',
            'alamat',
            'kota',
            'kode_pos',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin', // L/P
            'agama',
            'status_perkawinan',
            'pekerjaan',
            'tanggal_bergabung',
            'status_aktif', // 1/0 or TRUE/FALSE
            'referrer_id', // ID Silverchannel of the referrer (MANDATORY)
        ];
    }

    public function getSampleData(): array
    {
        return [
            [
                'SC001',
                'Toko Emas Sejahtera',
                'sc001@example.com',
                '081234567890',
                '3201123456780001',
                'Jl. Raya Merdeka No. 123',
                'Jakarta Selatan',
                '12345',
                'Jakarta',
                '1990-01-01',
                'Laki-laki',
                'Islam',
                'Menikah',
                'Wiraswasta',
                '01-01-2024',
                '1',
                'REF001' // Example valid referrer
            ],
            [
                'SC002',
                'Berkah Gold',
                'sc002@example.com',
                '081987654321',
                '3201123456780002',
                'Jl. Sudirman No. 45',
                'Bandung',
                '40115',
                'Bandung',
                '1992-05-20',
                'Perempuan',
                'Kristen',
                'Belum Menikah',
                'Pedagang',
                '15-01-2024',
                '1',
                'SC001' // Referencing the first row if already exists, or an existing upline
            ],
        ];
    }

    public function import(array $rows, $userId)
    {
        $this->successCount = 0;
        $this->failedCount = 0;
        $this->errors = [];
        $this->logs = [];

        Log::info("Starting Silverchannel Import process by User ID: {$userId}", ['total_rows' => count($rows)]);

        // 1. Pre-processing & Bulk Lookup
        $silverChannelIds = [];
        $referrerCodes = [];

        // Collect IDs for lookup
        foreach ($rows as $row) {
            $row = array_change_key_case($row, CASE_LOWER);
            if (!empty($row['id_silverchannel'])) {
                $silverChannelIds[] = $row['id_silverchannel'];
            }
            if (!empty($row['referrer_id'])) {
                $referrerCodes[] = $row['referrer_id'];
            }
        }

        // Bulk Fetch Existing Users
        $existingUsersMap = User::whereIn('silver_channel_id', $silverChannelIds)
            ->get()
            ->keyBy('silver_channel_id');

        // Bulk Fetch Referrers
        $potentialReferrers = User::whereIn('silver_channel_id', $referrerCodes)
            ->orWhereIn('referral_code', $referrerCodes)
            ->get();
        
        $referrerMap = [];
        foreach ($potentialReferrers as $ref) {
            if ($ref->silver_channel_id) $referrerMap[$ref->silver_channel_id] = $ref->id;
            if ($ref->referral_code) $referrerMap[$ref->referral_code] = $ref->id;
        }

        // 2. Validation Phase (All-or-Nothing)
        $validationErrors = [];
        $validatedRows = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $row = array_change_key_case($row, CASE_LOWER);

            // Basic Validation
            $validator = Validator::make($row, [
                'id_silverchannel' => 'required',
                'nama_channel' => 'required|string',
                'email' => 'required|email',
                'telepon' => 'nullable|string',
                'nik' => 'nullable|string|max:16',
                'alamat' => 'nullable|string',
                'kota' => 'nullable|string',
                'kode_pos' => 'nullable|string',
                'tempat_lahir' => 'nullable|string',
                'tanggal_lahir' => 'nullable|date_format:Y-m-d',
                'jenis_kelamin' => 'nullable|string',
                'agama' => 'nullable|string',
                'status_perkawinan' => 'nullable|string',
                'pekerjaan' => 'nullable|string',
                'nama_bank' => 'nullable|string',
                'no_rekening' => 'nullable|string',
                'pemilik_rekening' => 'nullable|string',
                'tanggal_bergabung' => 'required',
                'status_aktif' => 'required',
                'referrer_id' => 'required|string', // MANDATORY
            ]);

            if ($validator->fails()) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => $validator->errors()->all()
                ];
                continue; 
            }

            // Referrer Existence Validation
            $referrerCode = $row['referrer_id'];
            if (!isset($referrerMap[$referrerCode])) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => ["Referrer ID '{$referrerCode}' tidak ditemukan di sistem. Pastikan referrer sudah terdaftar."]
                ];
                continue;
            }

            $validatedRows[] = [
                'row_number' => $rowNumber,
                'data' => $validator->validated(),
                'referrer_db_id' => $referrerMap[$referrerCode]
            ];
        }

        // If any validation errors occurred, abort everything
        if (count($validationErrors) > 0) {
            Log::warning("Import failed due to validation errors", ['errors' => $validationErrors]);
            
            // Check if there is a referrer specific error to customize the general message if needed, 
            // but the requirement says "Import dibatalkan karena referrer ID tidak tercatat..." 
            // We'll append this to the response.
            
            return [
                'success_count' => 0,
                'failed_count' => count($rows),
                'errors' => $validationErrors,
                'logs' => ["Import dibatalkan karena terdapat error validasi (termasuk validasi referrer ID)."],
                'status' => 'failed'
            ];
        }

        // 3. Execution Phase (Atomic Transaction)
        DB::beginTransaction();
        try {
            foreach ($validatedRows as $item) {
                $data = $item['data'];
                $rowNumber = $item['row_number'];
                $referrerId = $item['referrer_db_id'];

                $status = filter_var($data['status_aktif'], FILTER_VALIDATE_BOOLEAN) ? 'ACTIVE' : 'INACTIVE';
                
                // Date Parsing Logic
                $joinDateStr = $data['tanggal_bergabung'];
                $joinDate = null;
                $formats = ['d-m-Y', 'Y-m-d', 'm/d/Y', 'n/j/Y', 'd/m/Y', 'Y/m/d'];
                
                foreach ($formats as $fmt) {
                    try {
                        $joinDate = Carbon::createFromFormat($fmt, $joinDateStr)->format('Y-m-d H:i:s');
                        break;
                    } catch (\Exception $e) { continue; }
                }

                if (!$joinDate) {
                    try {
                        $joinDate = Carbon::parse($joinDateStr)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                         // Should be caught in validation phase ideally, but fail-safe here
                         throw new \Exception("Format tanggal bergabung tidak valid di baris {$rowNumber}");
                    }
                }

                $scId = $data['id_silverchannel'];
                $existing = $existingUsersMap[$scId] ?? null;

                $action = $existing ? 'UPDATE' : 'CREATE';
                $oldValues = $existing ? $existing->toArray() : null;

                $userData = [
                    'name' => $data['nama_channel'],
                    'email' => $data['email'],
                    'phone' => !empty($data['telepon']) ? $data['telepon'] : null,
                    'nik' => !empty($data['nik']) ? $data['nik'] : null,
                    'address' => !empty($data['alamat']) ? $data['alamat'] : null,
                    'city_name' => !empty($data['kota']) ? $data['kota'] : null,
                    'postal_code' => !empty($data['kode_pos']) ? $data['kode_pos'] : null,
                    'status' => $status,
                    'referral_code' => $scId,
                    'referrer_id' => $referrerId,
                ];

                if ($existing) {
                    $user = $existing;
                    $user->fill($userData);
                } else {
                    $user = new User($userData);
                    $user->silver_channel_id = $scId;
                    $user->password = Hash::make('password');
                }
                
                $user->created_at = $joinDate;
                $user->save();
                
                if (!$existing) {
                    $user->assignRole('SILVERCHANNEL');
                }

                // Details
                $user->gender = !empty($data['jenis_kelamin']) ? $data['jenis_kelamin'] : null;
                $user->birth_place = !empty($data['tempat_lahir']) ? $data['tempat_lahir'] : null;
                $user->birth_date = !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null;
                $user->religion = !empty($data['agama']) ? $data['agama'] : null;
                $user->marital_status = !empty($data['status_perkawinan']) ? $data['status_perkawinan'] : null;
                $user->job = !empty($data['pekerjaan']) ? $data['pekerjaan'] : null;
                $user->save();

                // Audit Log
                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'IMPORT_SILVERCHANNEL_' . $action,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'new_values' => $data,
                    'old_values' => $action === 'UPDATE' ? $oldValues : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $this->successCount++;
                $this->logs[] = "Row {$rowNumber}: {$action} ID {$scId} successfully.";
            }

            DB::commit();
            Log::info("Import success. Processed {$this->successCount} rows.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Import failed during execution phase: " . $e->getMessage());
            
            return [
                'success_count' => 0,
                'failed_count' => count($rows),
                'errors' => [[
                    'row' => 'General',
                    'data' => [],
                    'errors' => ["Terjadi kesalahan sistem saat memproses data: " . $e->getMessage()]
                ]],
                'logs' => ["Import dibatalkan karena kesalahan sistem."],
                'status' => 'failed'
            ];
        }

        return [
            'success_count' => $this->successCount,
            'failed_count' => 0,
            'errors' => [],
            'logs' => $this->logs,
            'status' => 'success'
        ];
    }
}
