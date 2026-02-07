<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Imports\ProductImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    public function create()
    {
        // Simpler approach: Look for file matching pattern
        $files = Storage::files('temp');
        $tempFile = null;
        foreach ($files as $file) {
            if (str_starts_with(basename($file), 'product_import_' . Auth::id() . '.')) {
                $tempFile = $file;
                break;
            }
        }

        if ($tempFile && Storage::exists($tempFile)) {
            try {
                $path = Storage::path($tempFile);
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $previewRows = [];
                $headers = [];

                if (in_array(strtolower($extension), ['csv', 'txt'])) {
                    // Manual CSV processing to bypass ZipArchive requirement
                    $csvData = array_map('str_getcsv', file($path));
                    
                    // Remove UTF-8 BOM if present
                    $bom = pack('H*','EFBBBF');
                    if (isset($csvData[0][0])) {
                        $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
                    }

                    if (!empty($csvData)) {
                        $headers = array_shift($csvData); // Get header
                        // Take first 5 rows
                        $previewRows = array_slice($csvData, 0, 5);
                        
                        // Combine if possible for better preview
                        foreach ($previewRows as &$row) {
                            if (count($headers) == count($row)) {
                                $row = array_combine($headers, $row);
                            }
                        }
                    }
                } else {
                    // Excel processing (requires ZipArchive)
                    $rows = Excel::toArray(new ProductImport(false), $path);
                    if (!empty($rows) && !empty($rows[0])) {
                        $sheet = $rows[0];
                        $previewRows = array_slice($sheet, 0, 5);
                        $headers = array_keys($previewRows[0] ?? []);
                    }
                }
                
                if (!empty($previewRows)) {
                    return view('admin.products.import', [
                        'hasPending' => true,
                        'headers' => $headers,
                        'preview_data' => $previewRows
                    ]);
                }
            } catch (\Throwable $e) {
                // Catch generic Throwable to handle Class Not Found (ZipArchive)
                // Cleanup temp file to avoid stuck state
                Storage::delete($tempFile);
                Log::error('Preview Error: ' . $e->getMessage());
                
                // If specific ZipArchive error, flash warning
                if (str_contains($e->getMessage(), 'ZipArchive')) {
                    session()->flash('warning', 'Format Excel (XLSX) tidak didukung server karena ekstensi ZIP tidak aktif. Silakan gunakan format CSV.');
                }
            }
        }

        return view('admin.products.import', ['hasPending' => false]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'SKU', 'Nama Produk', 'Brand', 'Kategori', 'Harga (Silverchannel)', 'MSRP', 'Berat (gram)', 'Stok', 'Deskripsi', 'URL Gambar'
        ];

        $sampleData = [
            ['PROD001', 'Contoh Produk Emas', 'Silvergram', 'Logam Mulia', 1500000, 1600000, 5, 100, 'Deskripsi produk...', 'https://example.com/image.jpg']
        ];

        // Manual CSV Export to bypass ZipArchive requirement
        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_produk.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        try {
            $file = $request->file('file');
            
            // Validate real path to avoid "Path must not be empty" error
            $realPath = $file->getRealPath();
            
            // Fallback if getRealPath is empty but getPathname works
            if (empty($realPath) && method_exists($file, 'getPathname')) {
                $realPath = $file->getPathname();
            }

            if (empty($realPath)) {
                return back()->withErrors(['file' => 'Gagal membaca file. Silakan coba lagi atau cek permission file.']);
            }

            $extension = $file->getClientOriginalExtension();

            // Check if ZipArchive is available for Excel files
            if (in_array(strtolower($extension), ['xlsx', 'xls']) && !class_exists('ZipArchive')) {
                return back()->withErrors(['file' => 'Format Excel (XLSX/XLS) tidak didukung karena ekstensi ZipArchive tidak aktif di server. Silakan gunakan format CSV.']);
            }

            $filename = 'product_import_' . Auth::id() . '.' . $extension;
            $targetPath = 'temp/' . $filename;
            
            // Clean up old files first
            $files = Storage::files('temp');
            foreach ($files as $f) {
                if (str_starts_with(basename($f), 'product_import_' . Auth::id() . '.')) {
                    Storage::delete($f);
                }
            }

            // Save to temp storage safely using manual stream
            // This bypasses FilesystemAdapter's putFileAs wrapper which causes "Path must not be empty" 
            $stream = fopen($realPath, 'r');
            if (!$stream) {
                throw new \Exception("Gagal membuka file yang diupload.");
            }
            
            $success = Storage::put($targetPath, $stream);
            
            if (is_resource($stream)) {
                fclose($stream);
            }
            
            if (!$success) {
                throw new \Exception("Gagal menyimpan file ke temporary storage.");
            }
            
            return redirect()->route('admin.products.import');

        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Gagal mengupload file: ' . $e->getMessage()]);
        }
    }

    public function downloadErrorLog($filename)
    {
        // Security check: ensure filename matches pattern and user ID if we want strictness, 
        // but for now just check directory traversal
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(403);
        }

        $path = 'import_logs/' . $filename;
        if (!Storage::exists($path)) {
            abort(404);
        }

        return Storage::download($path, 'error_log_' . date('Y-m-d_H-i-s') . '.csv');
    }

    public function store(Request $request)
    {
        $request->validate([
            'update_existing' => 'boolean'
        ]);

        try {
            // Find temp file
            $files = Storage::files('temp');
            $tempFile = null;
            foreach ($files as $file) {
                if (str_starts_with(basename($file), 'product_import_' . Auth::id() . '.')) {
                    $tempFile = $file;
                    break;
                }
            }

            if (!$tempFile || !Storage::exists($tempFile)) {
                return redirect()->route('admin.products.import')->withErrors(['file' => 'File import tidak ditemukan atau sesi berakhir.']);
            }

            $path = Storage::path($tempFile);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            // Remove global transaction to allow partial success
            // DB::beginTransaction();

            $import = new ProductImport($request->boolean('update_existing', false));
            $rowCount = 0;
            $failedCount = 0;
            $errors = [];
            $detailedErrors = []; // For CSV log

            if (in_array(strtolower($extension), ['csv', 'txt'])) {
                // Manual CSV Processing
                $csvData = array_map('str_getcsv', file($path));
                
                // Remove BOM
                $bom = pack('H*','EFBBBF');
                if (isset($csvData[0][0])) {
                    $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
                }

                if (empty($csvData)) {
                    throw new \Exception("File CSV kosong.");
                }

                $originalHeaders = array_shift($csvData);
                // Slugify headers to match Maatwebsite's behavior (which ProductImport expects)
                $headers = array_map(function($h) {
                    return \Illuminate\Support\Str::slug($h, '_');
                }, $originalHeaders);

                foreach ($csvData as $index => $row) {
                    $rowNum = $index + 2; // +2 because header is row 1 and index 0 is row 2
                    
                    if (count($headers) !== count($row)) {
                        $failedCount++;
                        $detailedErrors[] = [
                            'row' => $rowNum,
                            'column' => 'Format',
                            'value' => 'N/A',
                            'message' => 'Jumlah kolom tidak sesuai header.',
                            'suggestion' => 'Pastikan setiap baris memiliki jumlah kolom yang sama dengan header.'
                        ];
                        continue;
                    }
                    
                    $rowData = array_combine($headers, $row);
                    
                    // Validate
                    $validator = \Illuminate\Support\Facades\Validator::make($rowData, $import->rules());

                    if ($validator->fails()) {
                        $failedCount++;
                        $validationErrors = $validator->errors();
                        foreach ($validationErrors->messages() as $field => $messages) {
                            foreach ($messages as $msg) {
                                // Determine suggestion
                                $suggestion = 'Periksa kembali data Anda.';
                                if (str_contains($msg, 'required')) $suggestion = 'Kolom ini wajib diisi.';
                                if (str_contains($msg, 'numeric') || str_contains($msg, 'integer')) $suggestion = 'Pastikan format berupa angka.';
                                if (str_contains($msg, 'unique')) $suggestion = 'Data sudah terdaftar di sistem (duplikat).';
                                
                                $detailedErrors[] = [
                                    'row' => $rowNum,
                                    'column' => $field,
                                    'value' => $rowData[$field] ?? '',
                                    'message' => $msg,
                                    'suggestion' => $suggestion
                                ];
                            }
                        }
                        
                        // Limit displayed errors in UI
                        if (count($errors) < 10) {
                            $errors[] = [
                                'row' => $rowNum,
                                'errors' => $validator->errors()->all(),
                                'data' => $rowData
                            ];
                        }
                        continue;
                    }

                    // Attempt Save with per-row transaction
                    DB::beginTransaction();
                    try {
                        $model = $import->model($rowData);
                        if ($model) {
                            $model->save();
                        }
                        DB::commit();
                        $rowCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $failedCount++;
                        
                        $msg = $e->getMessage();
                        $suggestion = 'Hubungi administrator jika error berlanjut.';
                        if (str_contains($msg, 'Duplicate entry')) $suggestion = 'Data duplikat terdeteksi di database.';
                        if (str_contains($msg, 'Incorrect integer value')) $suggestion = 'Format angka tidak valid.';
                        
                        $detailedErrors[] = [
                            'row' => $rowNum,
                            'column' => 'System/Database',
                            'value' => 'N/A',
                            'message' => $msg,
                            'suggestion' => $suggestion
                        ];

                        if (count($errors) < 10) {
                            $errors[] = [
                                'row' => $rowNum,
                                'errors' => [$msg],
                                'data' => $rowData
                            ];
                        }
                    }
                }
            } else {
                // Excel Processing (Not focused for this task as per instruction to use CSV, but we keep it basic or disable)
                 throw new \Exception("Fitur import Excel sedang dinonaktifkan. Mohon gunakan CSV.");
            }

            // Log Audit
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'IMPORT_PRODUCTS',
                'model_type' => Product::class,
                'model_id' => null,
                'old_values' => null,
                'new_values' => [
                    'file' => basename($tempFile), 
                    'success' => $rowCount,
                    'failed' => $failedCount
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // DB::commit(); // Removed global commit

            // Cleanup temp file
            Storage::delete($tempFile);

            $logFilename = null;
            if ($failedCount > 0 && !empty($detailedErrors)) {
                $logFilename = 'import_errors_' . Auth::id() . '_' . time() . '.csv';
                
                // Generate CSV content
                $handle = fopen('php://temp', 'r+');
                fputcsv($handle, ['Baris', 'Kolom', 'Nilai Input', 'Pesan Error', 'Saran Perbaikan']);
                foreach ($detailedErrors as $err) {
                    fputcsv($handle, [
                        $err['row'],
                        $err['column'],
                        $err['value'],
                        $err['message'],
                        $err['suggestion']
                    ]);
                }
                rewind($handle);
                $content = stream_get_contents($handle);
                fclose($handle);
                
                Storage::put('import_logs/' . $logFilename, $content);
            }

            $result = [
                'success' => $failedCount === 0,
                'message' => $failedCount === 0 
                    ? 'Semua data berhasil diimpor.' 
                    : 'Import selesai dengan beberapa error (' . $failedCount . ' baris gagal).',
                'count' => $rowCount,
                'total_rows' => $rowCount + $failedCount,
                'success_count' => $rowCount,
                'failed_count' => $failedCount,
                'errors' => $errors, // Sample for UI
                'log_file' => $logFilename // For download
            ];

            if ($failedCount > 0) {
                 return redirect()->route('admin.products.import')->with('import_result', $result);
            }

            return redirect()->route('admin.products.import')->with('import_result', $result);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // ... logic for Excel validation if used ...
            // Since we disabled Excel branch, this might not be hit, but keep for safety
             DB::rollBack(); // If we were in transaction
             return back()->withErrors(['file' => 'Validasi gagal: ' . $e->getMessage()]);

        } catch (\Throwable $e) {
            // DB::rollBack(); // Global transaction removed
            Log::error('Import Failed: ' . $e->getMessage());
            
            $msg = $e->getMessage();
            if (str_contains($msg, 'ZipArchive')) {
                $msg = 'Ekstensi ZipArchive tidak aktif. Mohon gunakan file CSV.';
            }
            
            return redirect()->route('admin.products.import')->withErrors(['file' => 'Terjadi kesalahan saat impor: ' . $msg]);
        }
    }

    public function cancel()
    {
        $files = Storage::files('temp');
        foreach ($files as $file) {
            if (str_starts_with(basename($file), 'product_import_' . Auth::id() . '.')) {
                Storage::delete($file);
            }
        }
        return redirect()->route('admin.products.import');
    }
}
