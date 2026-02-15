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
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Kelola Role Pengguna
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Atur role untuk {{ $user->name }} agar sesuai dengan tugas operasionalnya.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-4">
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

                <div class="bg-white dark:bg-gray-800 card-3d border border-blue-100 dark:border-gray-700">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $user->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($userRoles as $roleName)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $roleName }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-5 py-4 text-xs text-gray-600 dark:text-gray-400">
                        Minimal satu role harus tetap dimiliki oleh pengguna ini. Beberapa role penting seperti SUPER_ADMIN dijaga agar selalu ada minimal satu di sistem.
                    </div>

                    <div class="p-5 border-t border-gray-100 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.rbac.user-roles.update', $user) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">Pilih Roles</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-72 overflow-y-auto border border-dashed border-gray-300 dark:border-gray-700 p-2 rounded-lg bg-gray-50 dark:bg-gray-900">
                                    @foreach($roles as $role)
                                        <label class="inline-flex items-center space-x-2 text-xs sm:text-sm text-gray-700 dark:text-gray-200">
                                            <input
                                                type="checkbox"
                                                name="roles[]"
                                                value="{{ $role->name }}"
                                                @checked(in_array($role->name, old('roles', $userRoles)))
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                                <a href="{{ route('admin.rbac.user-roles.index') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                                    Kembali ke daftar pengguna
                                </a>
                                <button
                                    type="submit"
                                    class="btn-3d btn-3d-blue shimmer px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold tracking-wide uppercase"
                                    onclick="return confirm('Anda yakin ingin mengubah role untuk pengguna ini?')"
                                >
                                    Simpan Perubahan Role
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
