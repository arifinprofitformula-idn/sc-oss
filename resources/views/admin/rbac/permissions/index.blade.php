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
                    Manajemen Permission
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola permission granular yang digunakan oleh setiap role.
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                Total Permission: {{ $permissions->total() }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 card-3d border border-amber-200 dark:border-gray-700">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-500 to-yellow-500 rounded-t-xl">
                            <h3 class="text-sm font-semibold text-white">
                                Buat Permission Baru
                            </h3>
                            <p class="mt-1 text-xs text-amber-50">
                                Tambahkan permission baru untuk mengatur akses modul secara detail.
                            </p>
                        </div>
                        <div class="px-6 pt-5 pb-4 space-y-4">
                            <form method="POST" action="{{ route('admin.rbac.permissions.store') }}" class="space-y-4">
                                @csrf
                                <div class="space-y-4 bg-amber-50/60 dark:bg-gray-900/60 rounded-lg px-4 py-4">
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Permission</label>
                                        <input
                                            name="name"
                                            value="{{ old('name') }}"
                                            class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            placeholder="Contoh: products.manage, orders.view"
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
                                            class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            placeholder="Deskripsi singkat permission"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-start mt-3">
                                    <button class="btn-3d btn-3d-gold shimmer px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold tracking-wide uppercase">
                                        Buat Permission
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 card-3d border border-amber-200 dark:border-gray-700">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Daftar Permission
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Daftar permission yang tersedia dalam sistem.
                                </p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-b-2 border-amber-400">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($permissions as $permission)
                                        <tr class="hover:bg-amber-50 dark:hover:bg-gray-900 transition-colors">
                                            <td class="px-3 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                                                {{ $permission->name }}
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap sm:whitespace-normal text-gray-600 dark:text-gray-300 text-xs sm:text-sm">
                                                {{ $permission->description }}
                                            </td>
                                            <td class="px-3 py-3 text-right whitespace-nowrap text-xs sm:text-sm">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.rbac.permissions.destroy', $permission) }}"
                                                    onsubmit="return confirm('Hapus permission ini?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-3 py-1 rounded-md text-[11px] sm:text-xs">
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
                            {{ $permissions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
