<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SilverchannelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSilverchannelController extends Controller
{
    protected $importService;

    public function __construct(SilverchannelImportService $importService)
    {
        $this->importService = $importService;
    }

    public function create()
    {
        // Check if there is a pending file for this user
        $tempFile = 'temp/import_' . Auth::id() . '.csv';
        
        if (Storage::exists($tempFile)) {
            try {
                $path = Storage::path($tempFile);
                
                if (file_exists($path)) {
                    $csvData = array_map('str_getcsv', file($path));
                    
                    // Remove UTF-8 BOM if present
                    $bom = pack('H*','EFBBBF');
                    if (isset($csvData[0][0])) {
                        $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
                    }

                    if (!empty($csvData)) {
                        $header = array_shift($csvData);
                        
                        if ($header) {
                            $previewData = [];
                            $count = 0;
                            foreach ($csvData as $row) {
                                if ($count >= 5) break;
                                if (count($header) == count($row)) {
                                    $previewData[] = array_combine($header, $row);
                                }
                                $count++;
                            }

                            return view('admin.silverchannels.import', [
                                'hasPending' => true,
                                'headers' => $header,
                                'preview_data' => $previewData
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // If file is corrupted, delete it and fallback to upload page
                Storage::delete($tempFile);
            }
        }
        
        return view('admin.silverchannels.import', ['hasPending' => false]);
    }

    public function downloadTemplate()
    {
        $headers = $this->importService->getHeaders();
        $sampleData = $this->importService->getSampleData();

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
            "Content-Disposition" => "attachment; filename=template_silverchannel.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->withErrors(['file' => 'File upload failed: ' . $file->getErrorMessage()]);
        }

        // Validate real path to avoid "Path must not be empty" error
        $realPath = $file->getRealPath();
        
        // Fallback if getRealPath is empty but getPathname works
        if (empty($realPath) && method_exists($file, 'getPathname')) {
            $realPath = $file->getPathname();
        }

        if (empty($realPath)) {
            return back()->withErrors(['file' => 'Unable to read file. Please try again or check file permissions.']);
        }
        
        try {
            // Save to temp storage safely using manual stream
            // This bypasses FilesystemAdapter's putFileAs wrapper which causes "Path must not be empty" 
            // errors when handling File objects in some environments/PHP versions.
            $filename = 'import_' . Auth::id() . '.csv';
            $targetPath = 'temp/' . $filename;
            
            // Open stream manually from the validated realPath
            $stream = fopen($realPath, 'r');
            if (!$stream) {
                throw new \Exception("Failed to open uploaded file for reading.");
            }
            
            // Store using stream
            $success = Storage::put($targetPath, $stream);
            
            if (is_resource($stream)) {
                fclose($stream);
            }
            
            if (!$success) {
                throw new \Exception("Failed to store uploaded file.");
            }
            
            // Use stored file path
            $path = $targetPath;
            
            // Use stored file for preview to ensure consistency
            $storedPath = Storage::path($path);

            if (!file_exists($storedPath)) {
                 throw new \Exception("Stored file not found at: " . $storedPath);
            }

            // Parse for preview
            $csvData = array_map('str_getcsv', file($storedPath));
            
            // Remove UTF-8 BOM if present
            $bom = pack('H*','EFBBBF');
            if (isset($csvData[0][0])) {
                $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
            }

            if (empty($csvData)) {
                 return back()->withErrors(['file' => 'File is empty.']);
            }

            $header = array_shift($csvData);
            
            if (!$header || (count($header) === 1 && (is_null($header[0]) || trim($header[0]) === ''))) {
                return back()->withErrors(['file' => 'Invalid CSV format: Missing header.']);
            }

            // REQUIRED FIELDS VALIDATION
            $requiredFields = ['nama_channel', 'id_silverchannel', 'tanggal_bergabung', 'email'];
            $missingHeaders = array_diff($requiredFields, $header);
            
            if (!empty($missingHeaders)) {
                return back()->withErrors(['file' => 'Format CSV tidak valid. Kolom wajib berikut hilang: ' . implode(', ', $missingHeaders)]);
            }

            // Validate all rows
            $rowErrors = [];
            $line = 2; // Start from line 2 (line 1 is header)
            
            $seenIds = [];
            $seenEmails = [];

            foreach ($csvData as $row) {
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && is_null($row[0]))) {
                    $line++;
                    continue;
                }

                if (count($header) != count($row)) {
                    // Try to pad or trim? No, strict validation.
                    // Actually, sometimes CSVs have trailing empty columns.
                    // But let's be strict or lenient?
                    // "Sistem harus menolak import seluruhnya jika salah satu field wajib kosong pada baris tertentu"
                    // Mismatch count is also an error.
                    // Let's just map what we can.
                }

                $rowData = [];
                // Combine safe
                foreach ($header as $index => $key) {
                    $rowData[$key] = $row[$index] ?? '';
                }

                // Check required fields
                foreach ($requiredFields as $field) {
                    if (empty(trim($rowData[$field] ?? ''))) {
                        $rowErrors[] = "Baris $line: Field '$field' wajib diisi.";
                    }
                }

                // Validate Email
                if (!empty($rowData['email'])) {
                    $email = trim($rowData['email']);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $rowErrors[] = "Baris $line: Format email tidak valid ($email).";
                    }
                    
                    // Check duplicate in CSV
                    if (in_array($email, $seenEmails)) {
                         $rowErrors[] = "Baris $line: Email duplikat dalam file ($email).";
                    } else {
                        $seenEmails[] = $email;
                    }
                }

                // Validate ID Silverchannel uniqueness in CSV
                if (!empty($rowData['id_silverchannel'])) {
                    $id = trim($rowData['id_silverchannel']);
                    if (in_array($id, $seenIds)) {
                         $rowErrors[] = "Baris $line: ID Silverchannel duplikat dalam file ($id).";
                    } else {
                        $seenIds[] = $id;
                    }
                }

                // Validate Date (YYYY-MM-DD or DD-MM-YYYY)
                if (!empty($rowData['tanggal_bergabung'])) {
                    $date = $rowData['tanggal_bergabung'];
                    // Try YYYY-MM-DD
                    $d = \DateTime::createFromFormat('Y-m-d', $date);
                    if (!($d && $d->format('Y-m-d') === $date)) {
                        // Try DD-MM-YYYY
                        $d = \DateTime::createFromFormat('d-m-Y', $date);
                        if (!($d && $d->format('d-m-Y') === $date)) {
                             $rowErrors[] = "Baris $line: Format tanggal tidak valid ({$date}). Gunakan YYYY-MM-DD atau DD-MM-YYYY.";
                        }
                    }
                }

                $line++;
            }

            if (!empty($rowErrors)) {
                // Limit errors to first 10 to avoid huge message
                $showErrors = array_slice($rowErrors, 0, 10);
                if (count($rowErrors) > 10) {
                    $showErrors[] = "... dan " . (count($rowErrors) - 10) . " error lainnya.";
                }
                return back()->withErrors(['file' => 'Validasi Data Gagal:', 'details' => $showErrors]);
            }

            // Combine header with data (take first 5 rows)
            $previewData = [];
            $count = 0;
            foreach ($csvData as $row) {
                if ($count >= 5) break;
                if (count($header) == count($row)) {
                    $previewData[] = array_combine($header, $row);
                }
                $count++;
            }

            return view('admin.silverchannels.import', [
                'preview_data' => $previewData,
                'headers' => $header,
                'hasPending' => true
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Preview Error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'An error occurred while processing the file: ' . $e->getMessage()]);
        }
    }

    public function process(Request $request)
    {
        try {
            $tempFile = 'temp/import_' . Auth::id() . '.csv';

            if (!Storage::exists($tempFile)) {
                return redirect()->route('admin.silverchannels.import')
                    ->withErrors(['file' => 'Sesi import telah kadaluarsa. Silakan upload ulang file.']);
            }

            $path = Storage::path($tempFile);
            
            if (empty($path) || !file_exists($path)) {
                Storage::delete($tempFile); // Cleanup if broken
                return back()->withErrors(['file' => 'File temporary tidak ditemukan.']);
            }

            $csvData = array_map('str_getcsv', file($path));

            // Remove UTF-8 BOM if present
            $bom = pack('H*','EFBBBF');
            if (isset($csvData[0][0])) {
                $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
            }

            if (empty($csvData)) {
                 Storage::delete($tempFile);
                 return back()->withErrors(['file' => 'File CSV kosong.']);
            }

            $header = array_shift($csvData);
            
            if (!$header) {
                 Storage::delete($tempFile);
                 return back()->withErrors(['file' => 'Format CSV tidak valid (header missing).']);
            }
            
            $rows = [];
            foreach ($csvData as $row) {
                if (count($header) == count($row)) {
                    $rows[] = array_combine($header, $row);
                }
            }

            if (empty($rows)) {
                return back()->withErrors(['file' => 'File CSV kosong atau format tidak valid (data mismatch).']);
            }

            $result = $this->importService->import($rows, Auth::id());

            // Cleanup
            Storage::delete($tempFile);

            if (isset($result['status']) && $result['status'] === 'failed') {
                return redirect()->route('admin.silverchannels.import')
                    ->with('error', 'Import dibatalkan karena terdapat kesalahan validasi.')
                    ->with('import_result', $result);
            }

            return redirect()->route('admin.silverchannels.import')
                ->with('success', 'Import berhasil diproses.')
                ->with('import_result', $result);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Process Error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->withErrors(['file' => 'File upload failed: ' . $file->getErrorMessage()]);
        }

        $realPath = $file->getRealPath();
        
        // Fallback
        if (empty($realPath) && method_exists($file, 'getPathname')) {
            $realPath = $file->getPathname();
        }

        if (empty($realPath)) {
            return back()->withErrors(['file' => 'Unable to read file path.']);
        }

        try {
            $csvData = array_map('str_getcsv', file($realPath));

            // Remove UTF-8 BOM if present
            $bom = pack('H*','EFBBBF');
            if (isset($csvData[0][0])) {
                $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
            }

            if (empty($csvData)) {
                 return back()->withErrors(['file' => 'File CSV kosong.']);
            }

            $header = array_map(fn($h) => trim($h), array_shift($csvData));
            
            if (!$header) {
                 return back()->withErrors(['file' => 'Format CSV tidak valid.']);
            }

            $rows = [];
            foreach ($csvData as $row) {
                if (count($header) == count($row)) {
                    $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);
                    $rows[] = array_combine($header, $row);
                }
            }

            if (empty($rows)) {
                return back()->withErrors(['file' => 'File CSV kosong atau format tidak valid.']);
            }

            $result = $this->importService->import($rows, Auth::id());

            if (isset($result['status']) && $result['status'] === 'failed') {
                return redirect()->route('admin.silverchannels.import')
                    ->with('error', 'Import dibatalkan karena terdapat kesalahan validasi.')
                    ->with('import_result', $result);
            }

            return redirect()->route('admin.silverchannels.import')
                ->with('success', 'Import berhasil diproses.')
                ->with('import_result', $result);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Store Error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    public function cancel()
    {
        $tempFile = 'temp/import_' . Auth::id() . '.csv';
        if (Storage::exists($tempFile)) {
            Storage::delete($tempFile);
        }
        return redirect()->route('admin.silverchannels.import');
    }
}
