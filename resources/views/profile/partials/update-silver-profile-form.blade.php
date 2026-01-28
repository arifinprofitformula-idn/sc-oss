<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Detail Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Lengkapi data profil, kependudukan, dan alamat Anda.") }}
        </p>

        <!-- Completeness Bar -->
        <div class="mt-4">
            <div class="flex justify-between mb-1">
                <span class="text-base font-medium text-indigo-700 dark:text-indigo-400">Kelengkapan Profil</span>
                <span class="text-sm font-medium text-indigo-700 dark:text-indigo-400">{{ $user->profile_completeness }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $user->profile_completeness }}%"></div>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('profile.details.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Read Only Info -->
        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Akun</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="silver_channel_id" :value="__('ID Silverchannel')" />
                    <x-text-input id="silver_channel_id" class="block mt-1 w-full bg-gray-200 cursor-not-allowed" type="text" :value="$user->silver_channel_id" readonly />
                </div>
                <div>
                    <x-input-label for="referral_code" :value="__('Kode Referral Saya')" />
                    <x-text-input id="referral_code" class="block mt-1 w-full bg-gray-200 cursor-not-allowed" type="text" :value="$user->referral_code" readonly />
                </div>
                <div>
                    <x-input-label for="referrer" :value="__('Referred By')" />
                    <x-text-input id="referrer" class="block mt-1 w-full bg-gray-200 cursor-not-allowed" type="text" :value="$user->referrer ? $user->referrer->name . ' - ' . ($user->referrer->silver_channel_id ?? '-') : '-'" readonly />
                </div>
            </div>
        </div>

        <!-- Photo -->
        <div x-data="{ photoPreview: '{{ $user->profile && $user->profile->photo_path ? asset('storage/' . $user->profile->photo_path) : '' }}' }">
            <x-input-label for="photo" :value="__('Foto Profil')" />
            
            <div class="mt-2 mb-2" x-show="photoPreview" style="display: none;">
                <img :src="photoPreview" alt="Profile Photo" class="w-20 h-20 rounded-full object-cover shadow-sm">
            </div>

            <input id="photo" name="photo" type="file" 
                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" 
                   accept="image/png, image/jpeg, image/jpg, image/webp"
                   @change="
                        const file = $event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => { photoPreview = e.target.result; };
                            reader.readAsDataURL(file);
                        }
                   ">
            
            <div class="mt-1 text-xs text-gray-500 space-y-1">
                <p>Format: JPG, JPEG, PNG, WebP. Rasio 1:1, Min 300x300px, Max 1MB.</p>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <!-- Section: Profil -->
        <div class="border-t pt-4">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Data Profil & Kependudukan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <!-- NIK -->
                <div>
                    <x-input-label for="nik" :value="__('NIK (Nomor Induk Kependudukan)')" />
                    <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" :value="old('nik', $user->nik)" />
                    <x-input-error class="mt-2" :messages="$errors->get('nik')" />
                </div>
                
                <!-- Tempat Lahir -->
                <div>
                    <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                    <x-text-input id="birth_place" name="birth_place" type="text" class="mt-1 block w-full" :value="old('birth_place', $user->profile->birth_place ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('birth_place')" />
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                    <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $user->profile->birth_date ? $user->profile->birth_date->format('Y-m-d') : '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <!-- Gender -->
                <div>
                    <x-input-label for="gender" :value="__('Jenis Kelamin')" required />
                    <select id="gender" name="gender" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" {{ (old('gender', $user->profile->gender ?? '') == 'Laki-laki') ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ (old('gender', $user->profile->gender ?? '') == 'Perempuan') ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                </div>

                <!-- Religion -->
                <div>
                    <x-input-label for="religion" :value="__('Agama')" />
                    <select id="religion" name="religion" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Pilih Agama</option>
                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $rel)
                            <option value="{{ $rel }}" {{ (old('religion', $user->profile->religion ?? '') == $rel) ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('religion')" />
                </div>

                <!-- Marital Status -->
                <div>
                    <x-input-label for="marital_status" :value="__('Status Perkawinan')" />
                    <select id="marital_status" name="marital_status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Pilih Status</option>
                        @foreach(['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'] as $status)
                            <option value="{{ $status }}" {{ (old('marital_status', $user->profile->marital_status ?? '') == $status) ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('marital_status')" />
                </div>
            </div>

            <!-- Job -->
            <div>
                <x-input-label for="job" :value="__('Pekerjaan')" />
                <x-text-input id="job" name="job" type="text" class="mt-1 block w-full" :value="old('job', $user->profile->job ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('job')" />
            </div>
        </div>

        <!-- Section: Alamat -->
        <div class="border-t pt-4">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Alamat</h3>
            
            <div class="mb-4">
                <x-input-label for="address" :value="__('Alamat Lengkap')" />
                <textarea id="address" name="address" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <x-input-label for="city_name" :value="__('Kota / Kabupaten')" />
                    <x-text-input id="city_name" name="city_name" type="text" class="mt-1 block w-full" :value="old('city_name', $user->city_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('city_name')" />
                </div>
                <div>
                    <x-input-label for="postal_code" :value="__('Kode Pos')" />
                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $user->postal_code)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 mt-6">
            <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>

            @if (session('status') === 'profile-details-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>