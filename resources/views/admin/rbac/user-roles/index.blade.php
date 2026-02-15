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
                    Manajemen Role Pengguna
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Lihat dan kelola role yang dimiliki setiap pengguna sistem.
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                Total Pengguna: {{ $users->total() }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 card-3d border border-blue-100 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="w-full md:w-auto">
                        <form method="GET" action="{{ route('admin.rbac.user-roles.index') }}" class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ request('q') }}"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Cari nama atau email pengguna"
                                />
                            </div>
                            <div class="w-full sm:w-auto">
                                <button class="btn-3d btn-3d-blue shimmer w-full sm:w-auto px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold tracking-wide uppercase">
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Roles</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-blue-50 dark:hover:bg-gray-900 transition-colors">
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300 text-xs sm:text-sm">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.rbac.user-roles.edit', $user) }}"
                                           class="btn-3d btn-3d-blue shimmer inline-flex items-center px-3 py-1 rounded-md text-[11px] sm:text-xs">
                                            Kelola Role
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        Tidak ada pengguna yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
