<x-app-layout>
    <style>
        .btn-3d {
            transition: all 0.1s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow:
                0 0 0 0 rgba(0, 0, 0, 0.5),
                0 0 0 0 rgba(255, 255, 255, 0.5),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5),
                inset 0 -1px 0 0 rgba(0, 0, 0, 0.2);
        }
        .btn-3d:active {
            transform: translateY(2px);
            box-shadow:
                0 0 0 0 rgba(0, 0, 0, 0.5),
                inset 0 1px 0 0 rgba(0, 0, 0, 0.2);
        }
        .btn-3d-blue {
            background: linear-gradient(to bottom, #3b82f6, #2563eb);
            border: 1px solid #1d4ed8;
            color: white;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow:
                0 4px 0 0 #1e40af,
                0 5px 5px 0 rgba(0, 0, 0, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.3);
        }
        .btn-3d-blue:hover {
            background: linear-gradient(to bottom, #60a5fa, #3b82f6);
        }
        .btn-3d-gold {
            background: linear-gradient(to bottom, #f59e0b, #d97706);
            border: 1px solid #b45309;
            color: white;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow:
                0 4px 0 0 #92400e,
                0 5px 5px 0 rgba(0, 0, 0, 0.2),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.3);
        }
        .btn-3d-gold:hover {
            background: linear-gradient(to bottom, #fbbf24, #f59e0b);
        }
        .card-3d {
            position: relative;
            border-radius: 0.75rem;
            box-shadow:
                0 20px 25px -5px rgba(15, 23, 42, 0.25),
                0 8px 10px -6px rgba(15, 23, 42, 0.2);
            transform: translateY(0);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card-3d:hover {
            transform: translateY(-2px);
            box-shadow:
                0 25px 30px -5px rgba(15, 23, 42, 0.35),
                0 10px 15px -6px rgba(15, 23, 42, 0.3);
        }
        .shimmer {
            position: relative;
            overflow: hidden;
        }
        .shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.35) 50%,
                rgba(255, 255, 255, 0) 100%);
            transform: skewX(-25deg);
            animation: shimmer 3s infinite;
            pointer-events: none;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Manajemen Role
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Atur role dan akses sistem untuk seluruh pengguna EPI-OSS.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    Total Role: {{ $roles->total() }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->has('general'))
                <div class="px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 card-3d border border-blue-100 dark:border-gray-700">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-xl">
                            <h3 class="text-sm font-semibold text-white">
                                {{ request('section') === 'create' ? 'Buat Role Baru' : 'Form Role' }}
                            </h3>
                            <p class="mt-1 text-xs text-blue-100">
                                Tentukan nama role, deskripsi, dan permission yang terkait.
                            </p>
                        </div>
                        <div class="px-6 pt-5 pb-4 space-y-4">
                            <form method="POST" action="{{ route('admin.rbac.roles.store') }}" class="space-y-4">
                                @csrf
                                <div class="space-y-4 bg-gray-50 dark:bg-gray-900/60 rounded-lg px-4 py-4">
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Role</label>
                                        <input
                                            name="name"
                                            value="{{ old('name') }}"
                                            class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Contoh: SUPER_ADMIN, ADMIN_OPERATIONAL"
                                            required
                                        />
                                        @error('name')
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
                                        <input
                                            name="description"
                                            value="{{ old('description') }}"
                                            class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Deskripsi singkat fungsi role"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Permissions</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900">
                                            @foreach($permissions as $p)
                                                <label class="inline-flex items-center space-x-2 text-xs sm:text-sm text-gray-700 dark:text-gray-200">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $p->name }}"
                                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                                    />
                                                    <span>{{ $p->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-start mt-3">
                                    <button class="btn-3d btn-3d-blue shimmer px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold tracking-wide uppercase">
                                        Buat Role
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 card-3d border border-blue-100 dark:border-gray-700">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Daftar Role
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Pantau role aktif dan permission yang melekat.
                                </p>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Halaman {{ $roles->currentPage() }} dari {{ $roles->lastPage() }}
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Permissions</th>
                                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($roles as $role)
                                        <tr class="hover:bg-blue-50 dark:hover:bg-gray-900 transition-colors">
                                            <td class="px-3 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                                                {{ $role->name }}
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap sm:whitespace-normal text-gray-600 dark:text-gray-300 text-xs sm:text-sm">
                                                {{ $role->description }}
                                            </td>
                                            <td class="px-3 py-3 max-w-lg">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($role->permissions as $p)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                            {{ $p->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-3 py-3 text-right whitespace-nowrap text-xs sm:text-sm">
                                                <a href="{{ route('admin.rbac.roles.edit', $role) }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-3 py-1 rounded-md text-[11px] sm:text-xs mr-2">
                                                    Edit
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.rbac.roles.destroy', $role) }}"
                                                    class="inline-block"
                                                    onsubmit="return confirm('Hapus role ini?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-3d btn-3d-gold shimmer inline-flex items-center px-3 py-1 rounded-md text-[11px] sm:text-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                            {{ $roles->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
