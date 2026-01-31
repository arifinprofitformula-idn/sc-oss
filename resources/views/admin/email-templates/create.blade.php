<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Email Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.email-templates.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Template Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Key -->
                        <div class="mb-4">
                            <x-input-label for="key" :value="__('Template Key (Unique Identifier)')" />
                            <x-text-input id="key" class="block mt-1 w-full" type="text" name="key" :value="old('key')" required />
                            <p class="text-sm text-gray-500 mt-1">Used by the system to identify this template (e.g., forgot_password, order_confirmation).</p>
                            <x-input-error :messages="$errors->get('key')" class="mt-2" />
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <x-input-label for="subject" :value="__('Email Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <!-- Body -->
                        <div class="mb-4">
                            <x-input-label for="body" :value="__('Email Content (HTML)')" />
                            <textarea id="body" name="body" rows="15" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" required>{{ old('body') }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Available variables: @{{name}}, @{{reset_url}}, @{{count}}, @{{app_name}}, etc.</p>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <!-- Active Status -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <!-- Sync Brevo -->
                        <div class="block mt-4">
                            <label for="sync_brevo" class="inline-flex items-center">
                                <input id="sync_brevo" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="sync_brevo" value="1" {{ old('sync_brevo') ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Sync to Brevo immediately') }}</span>
                            </label>
                            <p class="text-xs text-gray-500 ml-6">If checked, this template will be created in Brevo using the configured API Key.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.email-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button class="ml-4">
                                {{ __('Create Template') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
