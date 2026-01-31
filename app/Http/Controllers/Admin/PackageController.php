<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Product;
use App\Models\AuditLog;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::withTrashed();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        $packages = $query->latest()->paginate(10);

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.packages.create', compact('products'));
    }

    public function store(StorePackageRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->processImage($request->file('image'));
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['image' => 'Gagal memproses gambar: ' . $e->getMessage()]);
            }
        }

        $package = Package::create($data);

        if ($request->has('products')) {
            $syncData = [];
            foreach ($request->products as $product) {
                $syncData[$product['id']] = ['quantity' => $product['quantity']];
            }
            $package->products()->attach($syncData);
        }

        AuditLog::log('CREATE_PACKAGE', $package, null, $package->load('products')->toArray());

        if ($request->wantsJson()) {
            session()->flash('success', 'Paket berhasil dibuat.');
            return response()->json([
                'message' => 'Paket berhasil dibuat.',
                'redirect' => route('admin.packages.index'),
            ]);
        }

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dibuat.');
    }

    public function edit(Package $package)
    {
        $package->load('products');
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.packages.edit', compact('package', 'products'));
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        $oldValues = $package->toArray();
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Secure delete old image
            if (!empty($package->image) && Storage::disk('public')->exists($package->image)) {
                Storage::disk('public')->delete($package->image);
            }
            
            try {
                $data['image'] = $this->processImage($request->file('image'));
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['image' => 'Gagal memproses gambar: ' . $e->getMessage()]);
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $package->update($data);

            if ($request->has('products')) {
                $syncData = [];
                foreach ($request->products as $product) {
                    $syncData[$product['id']] = ['quantity' => $product['quantity']];
                }
                $package->products()->sync($syncData);
            } else {
                $package->products()->detach();
            }
            
            AuditLog::log('UPDATE_PACKAGE', $package, $oldValues, $package->load('products')->toArray());
            
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Package Update Error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan saat menyimpan paket.',
                    'errors' => ['system' => [$e->getMessage()]]
                ], 500);
            }
            
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan paket: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            session()->flash('success', 'Paket berhasil diperbarui.');
            return response()->json([
                'message' => 'Paket berhasil diperbarui.',
                'redirect' => route('admin.packages.index'),
            ]);
        }

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package)
    {
        $oldValues = $package->toArray();
        $package->delete();

        AuditLog::log('DELETE_PACKAGE', $package, $oldValues, null);

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dihapus (soft delete).');
    }

    public function restore($id)
    {
        $package = Package::withTrashed()->findOrFail($id);
        $package->restore();

        AuditLog::log('RESTORE_PACKAGE', $package, null, $package->toArray());

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dipulihkan.');
    }

    /**
     * Process image upload with compression and resizing.
     * Mirrors logic from UserProfileController.
     */
    private function processImage($file)
    {
        $imageContents = '';
        $extension = $file->getClientOriginalExtension();
        $targetExtension = 'jpg'; // Standardize to JPG for consistency

        // If > 1MB, compress
        if ($file->getSize() > 1024 * 1024) {
            $imageResource = imagecreatefromstring($file->get());
            if (!$imageResource) {
                 // Fallback if GD fails or format not supported, try standard store
                 // But user reported error with store(), so we prefer explicit put
                 return $file->store('packages', 'public');
            }

            $width = imagesx($imageResource);
            $height = imagesy($imageResource);
            $maxDim = 1000;

            // Resize if too big (e.g. > 1000px)
            if ($width > $maxDim || $height > $maxDim) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newHeight = $maxDim;
                    $newWidth = $maxDim * $ratio;
                }
                
                $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
                
                // Handle transparency
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                
                imagecopyresampled($newImage, $imageResource, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
                $imageResource = $newImage;
            } else {
                $newImage = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                $imageResource = $newImage;
            }

            // Output to buffer
            ob_start();
            imagejpeg($imageResource, null, 80); // 80% quality
            $imageContents = ob_get_clean();
        } else {
            // If < 1MB, convert to JPG for consistency
             $imageResource = @imagecreatefromstring($file->get());
             if ($imageResource) {
                $width = imagesx($imageResource);
                $height = imagesy($imageResource);
                
                $newImage = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                
                ob_start();
                imagejpeg($newImage, null, 90); 
                $imageContents = ob_get_clean();
             } else {
                 $imageContents = $file->get();
                 $targetExtension = $extension;
             }
        }

        // Ensure directory exists
        if (!Storage::disk('public')->exists('packages')) {
            Storage::disk('public')->makeDirectory('packages');
        }

        $filename = 'packages/' . Str::uuid() . '.' . $targetExtension;
        
        if (!Storage::disk('public')->put($filename, $imageContents)) {
             throw new \Exception('Gagal menyimpan file gambar paket.');
        }
        
        return $filename;
    }
}
