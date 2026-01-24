<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalApi;
use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExternalApiController extends Controller
{
    protected $apiService;

    public function __construct(ExternalApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $apis = ExternalApi::latest()->paginate(10);
        return view('admin.external-apis.index', compact('apis'));
    }

    public function create()
    {
        return view('admin.external-apis.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint_url' => 'required|url',
            'method' => ['required', Rule::in(['GET', 'POST', 'PUT', 'DELETE'])],
            'auth_type' => ['required', Rule::in(['none', 'api_key', 'bearer', 'basic'])],
            'parameters' => 'nullable|json',
            'auth_credentials' => 'nullable|array', // Will be handled dynamically in view
            'rate_limit_requests' => 'required|integer|min:1',
            'rate_limit_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['parameters'])) {
            $validated['parameters'] = json_decode($validated['parameters'], true);
        }

        $validated['is_active'] = $request->has('is_active');

        ExternalApi::create($validated);

        return redirect()->route('admin.external-apis.index')->with('success', 'API configuration created successfully.');
    }

    public function edit(ExternalApi $externalApi)
    {
        return view('admin.external-apis.edit', compact('externalApi'));
    }

    public function update(Request $request, ExternalApi $externalApi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint_url' => 'required|url',
            'method' => ['required', Rule::in(['GET', 'POST', 'PUT', 'DELETE'])],
            'auth_type' => ['required', Rule::in(['none', 'api_key', 'bearer', 'basic'])],
            'parameters' => 'nullable|json',
            'auth_credentials' => 'nullable|array',
            'rate_limit_requests' => 'required|integer|min:1',
            'rate_limit_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['parameters'])) {
            $validated['parameters'] = json_decode($validated['parameters'], true);
        }

        $validated['is_active'] = $request->has('is_active');

        // Merge credentials if not provided (to avoid overwriting with null if only partial update logic used in future, though here we submit all)
        // Ideally we might want to keep old credentials if fields are empty, but for now we assume form fills them.
        
        $externalApi->update($validated);

        return redirect()->route('admin.external-apis.index')->with('success', 'API configuration updated successfully.');
    }

    public function destroy(ExternalApi $externalApi)
    {
        $externalApi->delete();
        return redirect()->route('admin.external-apis.index')->with('success', 'API configuration deleted successfully.');
    }

    public function show(ExternalApi $externalApi)
    {
        $logs = $externalApi->logs()->take(50)->get();
        return view('admin.external-apis.show', compact('externalApi', 'logs'));
    }

    public function test(Request $request, ExternalApi $externalApi)
    {
        $overrideParams = $request->input('params', []);
        $result = $this->apiService->execute($externalApi, $overrideParams);

        return response()->json($result);
    }

    public function export()
    {
        $apis = ExternalApi::all()->makeVisible('auth_credentials')->toArray();
        $filename = 'external_apis_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->streamDownload(function () use ($apis) {
            echo json_encode($apis, JSON_PRETTY_PRINT);
        }, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('file');
        $json = json_decode(file_get_contents($file), true);

        if (!is_array($json)) {
            return back()->with('error', 'Invalid JSON file.');
        }

        foreach ($json as $apiData) {
            ExternalApi::updateOrCreate(
                ['name' => $apiData['name']], // Match by name to avoid duplicates
                [
                    'endpoint_url' => $apiData['endpoint_url'],
                    'method' => $apiData['method'],
                    'parameters' => $apiData['parameters'],
                    'auth_type' => $apiData['auth_type'],
                    'auth_credentials' => $apiData['auth_credentials'],
                    'rate_limit_requests' => $apiData['rate_limit_requests'],
                    'rate_limit_period' => $apiData['rate_limit_period'],
                    'is_active' => $apiData['is_active'],
                    'description' => $apiData['description'],
                ]
            );
        }

        return redirect()->route('admin.external-apis.index')->with('success', 'APIs imported successfully.');
    }
    
    public function docs()
    {
        return view('admin.external-apis.docs');
    }
}
