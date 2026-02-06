<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Email Template') }}
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.email-templates.update', $emailTemplate) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Template Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $emailTemplate->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Key -->
                        <div class="mb-4">
                            <x-input-label for="key" :value="__('Template Key')" />
                            <x-text-input id="key" class="block mt-1 w-full" type="text" name="key" :value="old('key', $emailTemplate->key)" required />
                            <x-input-error :messages="$errors->get('key')" class="mt-2" />
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <x-input-label for="subject" :value="__('Email Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject', $emailTemplate->subject)" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <!-- Body -->
                        <div class="mb-4">
                            <x-input-label for="body" :value="__('Email Content (HTML)')" />
                            <textarea id="body" name="body" rows="15" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" required>{{ old('body', $emailTemplate->body) }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <!-- Active Status -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <!-- Sync Brevo -->
                        <div class="block mt-4">
                            <label for="sync_brevo" class="inline-flex items-center">
                                <input id="sync_brevo" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="sync_brevo" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Sync changes to Brevo immediately') }}</span>
                            </label>
                            @if($emailTemplate->brevo_id)
                                <p class="text-xs text-green-600 ml-6">Current Brevo ID: {{ $emailTemplate->brevo_id }}</p>
                            @else
                                <p class="text-xs text-gray-500 ml-6">Will create a new template in Brevo if checked.</p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.email-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <a href="{{ route('admin.email-templates.preview', $emailTemplate) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 mr-4">Preview</a>
                            <button type="submit" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest ml-4">
                                {{ __('Update Template') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
