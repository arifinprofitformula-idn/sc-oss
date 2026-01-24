<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SilverchannelImportService;
use Illuminate\Http\Request;
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
        $tempFile = 'temp/import_' . auth()->id() . '.csv';
        $hasPending = Storage::exists($tempFile);
        
        return view('admin.silverchannels.import', compact('hasPending'));
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
        
        // Save to temp storage
        $path = $file->storeAs('temp', 'import_' . auth()->id() . '.csv');

        // Parse for preview
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        
        // Remove UTF-8 BOM if present
        $bom = pack('H*','EFBBBF');
        if (isset($csvData[0][0])) {
            $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
        }

        $header = array_shift($csvData);
        
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
    }

    public function process(Request $request)
    {
        $tempFile = 'temp/import_' . auth()->id() . '.csv';

        if (!Storage::exists($tempFile)) {
            return redirect()->route('admin.silverchannels.import')
                ->withErrors(['file' => 'Sesi import telah kadaluarsa. Silakan upload ulang file.']);
        }

        $path = Storage::path($tempFile);
        $csvData = array_map('str_getcsv', file($path));

        // Remove UTF-8 BOM if present
        $bom = pack('H*','EFBBBF');
        if (isset($csvData[0][0])) {
            $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
        }

        $header = array_shift($csvData);
        
        $rows = [];
        foreach ($csvData as $row) {
            if (count($header) == count($row)) {
                $rows[] = array_combine($header, $row);
            }
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File CSV kosong atau format tidak valid.']);
        }

        $result = $this->importService->import($rows, auth()->id());

        // Cleanup
        Storage::delete($tempFile);

        return redirect()->route('admin.silverchannels.import')
            ->with('import_result', $result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        // Remove UTF-8 BOM if present
        $bom = pack('H*','EFBBBF');
        if (isset($csvData[0][0])) {
            $csvData[0][0] = preg_replace("/^$bom/", '', $csvData[0][0]);
        }

        $header = array_map(fn($h) => trim($h), array_shift($csvData));
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

        $result = $this->importService->import($rows, auth()->id());

        return redirect()->route('admin.silverchannels.import')
            ->with('import_result', $result);
    }
    
    public function cancel()
    {
        $tempFile = 'temp/import_' . auth()->id() . '.csv';
        if (Storage::exists($tempFile)) {
            Storage::delete($tempFile);
        }
        return redirect()->route('admin.silverchannels.import');
    }
}
