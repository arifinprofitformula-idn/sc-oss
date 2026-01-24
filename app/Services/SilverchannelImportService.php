<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                '1'
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
                '1'
            ],
        ];
    }

    public function import(array $rows, $userId)
    {
        $this->successCount = 0;
        $this->failedCount = 0;
        $this->errors = [];
        $this->logs = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Assuming header is row 1
            
            // Normalize keys
            $row = array_change_key_case($row, CASE_LOWER);
            
            // Validation
            $validator = Validator::make($row, [
                'id_silverchannel' => 'required',
                'nama_channel' => 'required|string',
                'email' => 'required|email',
                'telepon' => 'nullable|string',
                'nik' => 'nullable|string|max:16',
                'alamat' => 'required|string',
                'kota' => 'required|string',
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
                'tanggal_bergabung' => 'required|date_format:d-m-Y',
                'status_aktif' => 'required',
            ]);

            if ($validator->fails()) {
                $this->failedCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => $validator->errors()->all()
                ];
                continue;
            }

            $data = $validator->validated();

            DB::beginTransaction();
            try {
                $status = filter_var($data['status_aktif'], FILTER_VALIDATE_BOOLEAN) ? 'ACTIVE' : 'INACTIVE';
                $joinDate = Carbon::createFromFormat('d-m-Y', $data['tanggal_bergabung'])->format('Y-m-d H:i:s');
                
                // Upsert by silver_channel_id
                $userData = [
                    'name' => $data['nama_channel'],
                    'email' => $data['email'],
                    'phone' => $data['telepon'] ?? null,
                    'nik' => $data['nik'] ?? null,
                    'address' => $data['alamat'],
                    'city_name' => $data['kota'],
                    'postal_code' => $data['kode_pos'] ?? null,
                    'status' => $status,
                    'referral_code' => $data['id_silverchannel'],
                ];

                $existing = User::where('silver_channel_id', $data['id_silverchannel'])->first();
                $action = $existing ? 'UPDATE' : 'CREATE';
                $oldValues = $existing ? $existing->toArray() : null;

                $user = User::updateOrCreate(
                    ['silver_channel_id' => $data['id_silverchannel']],
                    array_merge($userData, [
                        // For create, ensure password set; for update, it will be ignored if already set
                        'password' => $existing ? $existing->password : Hash::make('password'),
                    ])
                );
                $user->created_at = $joinDate;
                $user->save();
                
                if (!$existing) {
                    $user->assignRole('SILVERCHANNEL');
                }

                // Update detail & bank info directly on users table
                $user->gender = $data['jenis_kelamin'] ?? null;
                $user->birth_place = $data['tempat_lahir'] ?? null;
                $user->birth_date = $data['tanggal_lahir'] ?? null;
                $user->religion = $data['agama'] ?? null;
                $user->marital_status = $data['status_perkawinan'] ?? null;
                $user->job = $data['pekerjaan'] ?? null;
                $user->save();

                // Log
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

                DB::commit();
                $this->successCount++;
                $this->logs[] = "Row {$rowNumber}: {$action} ID {$data['id_silverchannel']} successfully.";

            } catch (\Exception $e) {
                DB::rollBack();
                $this->failedCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return [
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'errors' => $this->errors,
            'logs' => $this->logs,
        ];
    }
}
