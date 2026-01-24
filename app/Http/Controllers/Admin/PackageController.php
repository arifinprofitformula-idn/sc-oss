<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\AuditLog;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use Illuminate\Http\Request;

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
        return view('admin.packages.create');
    }

    public function store(StorePackageRequest $request)
    {
        $package = Package::create($request->validated());

        AuditLog::log('CREATE_PACKAGE', $package, null, $package->toArray());

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dibuat.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        $oldValues = $package->toArray();
        $package->update($request->validated());
        
        AuditLog::log('UPDATE_PACKAGE', $package, $oldValues, $package->toArray());

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
}
