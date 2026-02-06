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
        $tempFile = 'temp/product_import_' . Auth::id() . '.xlsx'; // We normalize to xlsx or store extension in session? 
        // Better approach: check for any extension or store filename in session
        
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
                
                // Read preview data
                $rows = Excel::toArray(new ProductImport(false), $path);
                
                if (!empty($rows) && !empty($rows[0])) {
                    $sheet = $rows[0];
                    $headers = array_keys($sheet[0]); // Assuming WithHeadingRow is used, keys are headers. 
                    // Actually, toArray with WithHeadingRow returns array of assoc arrays (keys are slugged headers).
                    // If we want raw headers, we might need different import or just display keys.
                    // Let's assume keys are readable enough or use mapping.
                    
                    // Actually, let's check ProductImport class to see if it uses WithHeadingRow
                    
                    $previewRows = array_slice($sheet, 0, 5);
                    
                    return view('admin.products.import', [
                        'hasPending' => true,
                        'headers' => array_keys($previewRows[0] ?? []),
                        'preview_data' => $previewRows
                    ]);
                }
            } catch (\Exception $e) {
                // Corrupt file?
                Storage::delete($tempFile);
                Log::error('Preview Error: ' . $e->getMessage());
            }
        }

        return view('admin.products.import', ['hasPending' => false]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'SKU', 'Nama Produk', 'Brand', 'Kategori', 'Harga (Silverchannel)', 'MSRP', 'Berat (gram)', 'Stok', 'Deskripsi', 'URL Gambar'
        ];

        // Create a dummy collection
        $data = collect([
            $headers,
            ['PROD001', 'Contoh Produk Emas', 'Silvergram', 'Logam Mulia', 1500000, 1600000, 5, 100, 'Deskripsi produk...', 'https://example.com/image.jpg']
        ]);

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
        }, 'template_import_produk.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = 'product_import_' . Auth::id() . '.' . $extension;
            
            // Clean up old files first
            $files = Storage::files('temp');
            foreach ($files as $f) {
                if (str_starts_with(basename($f), 'product_import_' . Auth::id() . '.')) {
                    Storage::delete($f);
                }
            }

            // Store new file
            $path = $file->storeAs('temp', $filename);
            
            return redirect()->route('admin.products.import');

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal mengupload file: ' . $e->getMessage()]);
        }
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

            DB::beginTransaction();

            $import = new ProductImport($request->boolean('update_existing', false));
            Excel::import($import, $path);

            // Log Audit
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'IMPORT_PRODUCTS',
                'model_type' => Product::class,
                'model_id' => null,
                'old_values' => null,
                'new_values' => ['file' => basename($tempFile), 'update_existing' => $request->boolean('update_existing', false)],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Cleanup
            Storage::delete($tempFile);

            return redirect()->route('admin.products.import')->with('import_result', [
                'success' => true,
                'message' => 'Impor produk berhasil dijalankan.',
                'count' => $import->getRowCount(), // Ensure ProductImport has getRowCount getter or implement it
                'total_rows' => $import->getRowCount(), // Placeholder if detailed stats not available
                'success_count' => $import->getRowCount(),
                'failed_count' => 0,
                'errors' => []
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'errors' => $failure->errors(),
                    'data' => $failure->values()
                ];
            }
            
            // Format for view
            return redirect()->route('admin.products.import')->with('import_result', [
                'success' => false,
                'message' => 'Validasi gagal.',
                'count' => 0,
                'total_rows' => 0,
                'success_count' => 0,
                'failed_count' => count($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Failed: ' . $e->getMessage());
            return redirect()->route('admin.products.import')->withErrors(['file' => 'Terjadi kesalahan saat impor: ' . $e->getMessage()]);
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
