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
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Role
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Perbarui deskripsi dan permission untuk role {{ $role->name }}.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-xl border border-blue-100 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gradient-to-r from-blue-600 to-indigo-600">
                    <div>
                        <div class="text-sm font-semibold text-white">
                            {{ $role->name }}
                        </div>
                        <div class="text-xs text-blue-100">
                            Guard: web
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4">
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

                    <form method="POST" action="{{ route('admin.rbac.roles.update', $role) }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Role</label>
                                <input
                                    value="{{ $role->name }}"
                                    class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed"
                                    disabled
                                />
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
                                <input
                                    name="description"
                                    value="{{ old('description', $role->description) }}"
                                    class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Deskripsi singkat peran role ini"
                                />
                                @error('description')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Permissions</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-72 overflow-y-auto border border-dashed border-gray-300 dark:border-gray-700 p-2 rounded-lg bg-gray-50 dark:bg-gray-900">
                                @foreach($permissions as $p)
                                    <label class="inline-flex items-center space-x-2 text-xs sm:text-sm text-gray-700 dark:text-gray-200">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $p->name }}"
                                            @checked(in_array($p->name, old('permissions', $role->permissions->pluck('name')->all())))
                                            class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span>{{ $p->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <a href="{{ route('admin.rbac.roles.index') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                                Kembali ke daftar role
                            </a>
                            <button class="btn-3d btn-3d-blue px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold tracking-wide uppercase">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
