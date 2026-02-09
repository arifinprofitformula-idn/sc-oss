<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Silverchannel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.silverchannels.update', $user) }}" method="POST" enctype="multipart/form-data" x-data="editSilverchannelForm({{ json_encode($user) }})">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Account Information -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Account Information</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" id="name" x-model="form.name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                                        <input type="email" name="email" id="email" x-model="form.email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Whatsapp -->
                                    <div>
                                        <label for="whatsapp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Whatsapp Number <span class="text-red-500">*</span></label>
                                        <input type="text" name="whatsapp" id="whatsapp" x-model="form.whatsapp" required placeholder="e.g. 628123456789" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Referrer -->
                                    <div>
                                        <label for="referrer_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referrer Code (Optional)</label>
                                        <input type="text" name="referrer_code" id="referrer_code" x-model="form.referrer_code" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <p class="text-xs text-gray-500 mt-1">Referrer code cannot be changed once registered.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Personal Information</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- NIK -->
                                    <div>
                                        <label for="nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK</label>
                                        <input type="text" name="nik" id="nik" x-model="form.nik" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Job -->
                                    <div>
                                        <label for="job" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job</label>
                                        <input type="text" name="job" id="job" x-model="form.job" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Birth Place -->
                                    <div>
                                        <label for="birth_place" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birth Place</label>
                                        <input type="text" name="birth_place" id="birth_place" x-model="form.birth_place" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Birth Date -->
                                    <div>
                                        <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birth Date</label>
                                        <input type="date" name="birth_date" id="birth_date" x-model="form.birth_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Gender -->
                                    <div>
                                        <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                                        <select name="gender" id="gender" x-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select Gender</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <!-- Religion -->
                                    <div>
                                        <label for="religion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Religion</label>
                                        <select name="religion" id="religion" x-model="form.religion" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select Religion</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Kristen">Kristen</option>
                                            <option value="Katolik">Katolik</option>
                                            <option value="Hindu">Hindu</option>
                                            <option value="Buddha">Buddha</option>
                                            <option value="Konghucu">Konghucu</option>
                                        </select>
                                    </div>
                                    <!-- Marital Status -->
                                    <div>
                                        <label for="marital_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marital Status</label>
                                        <select name="marital_status" id="marital_status" x-model="form.marital_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select Status</option>
                                            <option value="Belum Menikah">Belum Menikah</option>
                                            <option value="Menikah">Menikah</option>
                                            <option value="Cerai Hidup">Cerai Hidup</option>
                                            <option value="Cerai Mati">Cerai Mati</option>
                                        </select>
                                    </div>
                                    <!-- Photo -->
                                    <div>
                                        <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                                        <input type="file" name="photo" id="photo" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current photo</p>
                                        <template x-if="form.photo_url">
                                            <div class="mt-2">
                                                <img :src="form.photo_url" class="h-16 w-16 rounded-full object-cover">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Address Information</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Province -->
                                    <div>
                                        <label for="province_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Province <span class="text-red-500">*</span></label>
                                        <select name="province_id" id="province_id" x-model="form.province_id" @change="onProvinceChange($event)" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select Province</option>
                                            <template x-for="province in provinces" :key="province.province_id">
                                                <option :value="province.province_id" x-text="province.province"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="province_name" x-model="form.province_name">
                                    </div>
                                    <!-- City -->
                                    <div>
                                        <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City/District <span class="text-red-500">*</span></label>
                                        <select name="city_id" id="city_id" x-model="form.city_id" @change="onCityChange($event)" required :disabled="!form.province_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:opacity-50">
                                            <option value="">Select City</option>
                                            <template x-for="city in cities" :key="city.city_id">
                                                <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="city_name" x-model="form.city_name">
                                    </div>
                                    <!-- Postal Code -->
                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                                        <input type="text" name="postal_code" id="postal_code" x-model="form.postal_code" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <!-- Address -->
                                    <div class="sm:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Address</label>
                                        <textarea name="address" id="address" x-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Management (Super Admin Only) -->
                            @role('SUPER_ADMIN')
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Security</h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- New Password -->
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            New Password (Optional)
                                        </label>
                                        <input type="password" name="password" id="password" x-model="form.password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Min. 8 characters">
                                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" x-model="form.password_confirmation" :required="form.password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                            @endrole

                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('admin.silverchannels.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                                    Update Silverchannel
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        const API_ROUTES = {
            provinces: "{{ route('admin.silverchannels.locations.provinces') }}",
            cities: "{{ url('admin/silverchannels/locations/cities') }}"
        };

        function editSilverchannelForm(user) {
            return {
                form: {
                    name: user.name,
                    email: user.email,
                    whatsapp: user.phone || user.whatsapp,
                    province_id: user.province_id,
                    province_name: user.province_name,
                    city_id: user.city_id,
                    city_name: user.city_name,
                    referrer_code: user.referrer ? user.referrer.referral_code : '',
                    password: '',
                    password_confirmation: '',
                    nik: user.nik || '',
                    job: user.profile ? user.profile.job : '',
                    birth_place: user.profile ? user.profile.birth_place : '',
                    birth_date: user.profile && user.profile.birth_date ? user.profile.birth_date.split('T')[0] : '',
                    gender: user.profile ? user.profile.gender : '',
                    religion: user.profile ? user.profile.religion : '',
                    marital_status: user.profile ? user.profile.marital_status : '',
                    address: user.address || '',
                    postal_code: user.postal_code || '',
                    photo_url: user.profile && user.profile.photo_path ? '/storage/' + user.profile.photo_path : null
                },
                provinces: [],
                cities: [],

                init() {
                    this.fetchProvinces();
                    if (this.form.province_id) {
                        this.fetchCities(this.form.province_id);
                    }
                },

                fetchProvinces() {
                    fetch(API_ROUTES.provinces)
                        .then(response => response.json())
                        .then(data => {
                            this.provinces = data;
                        })
                        .catch(error => console.error('Error fetching provinces:', error));
                },
                
                fetchCities(provinceId) {
                    if(!provinceId) {
                        this.cities = [];
                        return;
                    }
                    fetch(`${API_ROUTES.cities}/${provinceId}`)
                        .then(response => response.json())
                        .then(data => {
                            this.cities = data;
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                },
                
                onProvinceChange(event) {
                    const select = event.target;
                    const index = select.selectedIndex;
                    const text = select.options[index].text;
                    this.form.province_name = text;
                    this.form.city_id = '';
                    this.form.city_name = '';
                    this.fetchCities(this.form.province_id);
                },

                onCityChange(event) {
                    const select = event.target;
                    const index = select.selectedIndex;
                    const text = select.options[index].text;
                    this.form.city_name = text;
                }
            }
        }
    </script>
</x-app-layout>
