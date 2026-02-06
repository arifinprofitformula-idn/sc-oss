<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Email Templates') }}
        </h2>
    </x-slot>

    <style>
        /* 3D Button Styles */
        .btn-3d {
            transition: all 0.1s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                0px 0px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
        }

        .btn-3d:active {
            transform: translateY(2px);
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                0px 0px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.5);
        }

        /* Blue Variant */
        .btn-3d-blue {
            background: linear-gradient(to bottom, #3b82f6, #2563eb);
            border: 1px solid #1d4ed8;
            box-shadow: 
                0px 4px 0px 0px #1e40af,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
            --btn-pulse-color: rgba(59, 130, 246, 0.5);
        }

        .btn-3d-blue:hover {
            background: linear-gradient(to bottom, #60a5fa, #3b82f6);
            transform: translateY(-1px);
            animation: pulse512 1.5s infinite;
        }

        .btn-3d-blue:active {
            background: linear-gradient(to bottom, #2563eb, #3b82f6);
            box-shadow: 
                0px 0px 0px 0px #1e40af,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.3);
        }

        /* Red Variant */
        .btn-3d-red {
            background: linear-gradient(to bottom, #ef4444, #dc2626);
            border: 1px solid #b91c1c;
            box-shadow: 
                0px 4px 0px 0px #991b1b,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
            --btn-pulse-color: rgba(239, 68, 68, 0.5);
        }

        .btn-3d-red:hover {
            background: linear-gradient(to bottom, #f87171, #ef4444);
            transform: translateY(-1px);
            animation: pulse512 1.5s infinite;
        }

        .btn-3d-red:active {
            background: linear-gradient(to bottom, #dc2626, #ef4444);
            box-shadow: 
                0px 0px 0px 0px #991b1b,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.3);
        }

        /* Gray Variant */
        .btn-3d-gray {
            background: linear-gradient(to bottom, #6b7280, #4b5563);
            border: 1px solid #374151;
            box-shadow: 
                0px 4px 0px 0px #1f2937,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
            --btn-pulse-color: rgba(107, 114, 128, 0.5);
        }

        .btn-3d-gray:hover {
            background: linear-gradient(to bottom, #9ca3af, #6b7280);
            transform: translateY(-1px);
            animation: pulse512 1.5s infinite;
        }

        .btn-3d-gray:active {
            background: linear-gradient(to bottom, #4b5563, #6b7280);
            box-shadow: 
                0px 0px 0px 0px #1f2937,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.3);
        }

        /* Pulse Animation */
        @keyframes pulse512 {
            0% { box-shadow: 0 0 0 0 var(--btn-pulse-color); }
            70% { box-shadow: 0 0 0 10px rgba(0, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
        }

        /* Shimmer Effect */
        .shimmer {
            position: relative;
            overflow: hidden;
        }

        .shimmer::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4 flex justify-between items-center">
                        <form action="{{ route('admin.email-templates.index') }}" method="GET" class="flex gap-2 w-full max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, subject or key..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="btn-3d btn-3d-gray shimmer px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">Search</button>
                        </form>
                        <a href="{{ route('admin.email-templates.create') }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                            {{ __('Add New Template') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name / Key</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brevo Sync</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($templates as $template)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $template->key }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($template->subject, 30) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($template->brevo_id)
                                            <span class="text-green-600 flex items-center gap-1" title="Synced">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                #{{ $template->brevo_id }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Not Synced</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('admin.email-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            
                                            <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Preview</a>

                                            <form action="{{ route('admin.email-templates.sync', $template) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-900" title="Sync to Brevo">
                                                    Sync
                                                </button>
                                            </form>

                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border" style="display: none;">
                                                    <div class="py-1">
                                                        <form action="{{ route('admin.email-templates.duplicate', $template) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Duplicate</button>
                                                        </form>
                                                        <a href="{{ route('admin.email-templates.export', $template) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export HTML</a>
                                                        <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No templates found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $templates->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
