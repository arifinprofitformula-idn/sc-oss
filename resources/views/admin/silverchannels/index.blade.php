<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Silverchannels') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="silverchannelManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <!-- Kolom 1: Search (40%) -->
                <div class="w-full md:w-[40%]">
                    <form method="GET" action="{{ route('admin.silverchannels.index') }}" class="w-full">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-11 pr-3 py-2 h-12 border border-gray-300 rounded-md leading-5 bg-[#F5F5F5] placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-blue-300 focus:ring focus:ring-blue-200 sm:text-sm text-[#333333]" placeholder="Search by name, email, phone...">
                        </div>
                    </form>
                </div>

                <!-- Kolom 2: Import Data (30%) -->
                <div class="w-full md:w-[30%]">
                    <a href="{{ route('admin.silverchannels.import') }}" style="background-color: #EEA727;" class="w-full h-12 hover:bg-opacity-90 text-white font-bold py-2 px-4 rounded inline-flex justify-center items-center shadow-lg transition duration-300 ease-in-out text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        {{ __('Import Data') }}
                    </a>
                </div>

                <!-- Kolom 3: Add New Silverchannel (30%) -->
                <div class="w-full md:w-[30%]">
                    <button @click="openCreateModal()" class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex justify-center items-center shadow-lg transition duration-300 ease-in-out text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add New Silverchannel') }}
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID / Referral Code</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email / Phone</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                                    <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($silverchannels as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->silver_channel_id ?? $user->referral_code ?? '-' }}</div>
                                            @if($user->referrer)
                                                <div class="text-xs text-gray-500 mt-1">Ref: {{ $user->referrer->name }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->city_name }}, {{ $user->province_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->status === 'ACTIVE')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ACTIVE
                                                </span>
                                            @elseif($user->status === 'PENDING_REVIEW' || $user->status === 'WAITING_VERIFICATION')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    {{ str_replace('_', ' ', $user->status) }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    {{ $user->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $user->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openEditModal({{ json_encode($user) }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                                                


                                                @if($user->status === 'PENDING_REVIEW' || $user->status === 'WAITING_VERIFICATION')
                                                    <form action="{{ route('admin.silverchannels.approve', $user) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 ml-2" onclick="return confirm('Approve this user?')">Approve</button>
                                                    </form>
                                                    <form action="{{ route('admin.silverchannels.reject', $user) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 ml-2" onclick="return confirm('Reject this user?')">Reject</button>
                                                    </form>
                                                @endif

                                                <button @click="confirmDelete('{{ route('admin.silverchannels.destroy', $user) }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 ml-2">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No Silverchannels found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $silverchannels->links() }}
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form :action="editMode ? '{{ url('admin/silverchannels') }}/' + form.id : '{{ route('admin.silverchannels.store') }}'" method="POST" enctype="multipart/form-data">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-6 sm:p-8">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-6" x-text="editMode ? 'Edit Silverchannel' : 'Add New Silverchannel'"></h3>
                            
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
                                            <input type="text" name="referrer_code" id="referrer_code" x-model="form.referrer_code" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                                                <span x-text="editMode ? 'New Password (Optional)' : 'Password'"></span>
                                            </label>
                                            <input type="password" name="password" id="password" x-model="form.password" :required="!editMode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Min. 8 characters">
                                            <p x-show="editMode" class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" x-model="form.password_confirmation" :required="!editMode && form.password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                                @endrole
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse sm:items-center gap-3 sm:gap-4">
                            <button type="submit" class="w-full h-10 inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Save
                            </button>
                            <button type="button" @click="showModal = false" class="w-full h-10 inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Form -->
        <form id="delete-form" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

    </div>

    <script>
        const API_ROUTES = {
            provinces: "{{ route('admin.silverchannels.locations.provinces') }}",
            cities: "{{ url('admin/silverchannels/locations/cities') }}"
        };

        function silverchannelManager() {
            return {
                showModal: false,
                editMode: false,
                form: {
                    id: null,
                    name: '',
                    email: '',
                    whatsapp: '',
                    province_id: '',
                    province_name: '',
                    city_id: '',
                    city_name: '',
                    referrer_code: '',
                    password: '',
                    password_confirmation: ''
                },
                provinces: [],
                cities: [],
                
                init() {
                    this.fetchProvinces();
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
                },

                resetForm() {
                    this.form = {
                        id: null,
                        name: '',
                        email: '',
                        whatsapp: '',
                        province_id: '',
                        province_name: '',
                        city_id: '',
                        city_name: '',
                        referrer_code: '',
                        password: '',
                        password_confirmation: ''
                    };
                    this.cities = [];
                },

                openCreateModal() {
                    this.resetForm();
                    this.editMode = false;
                    this.showModal = true;
                },
                
                openEditModal(user) {
                    this.form = {
                        id: user.id,
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
                        postal_code: user.postal_code || ''
                    };
                    
                    // Pre-fill cities
                    if (this.form.province_id) {
                        this.fetchCities(this.form.province_id);
                    }
                    
                    this.editMode = true;
                    this.showModal = true;
                },
                
                confirmDelete(url) {
                    if (confirm('Are you sure you want to delete this Silverchannel? This action cannot be undone.')) {
                        const form = document.getElementById('delete-form');
                        form.action = url;
                        form.submit();
                    }
                }
            }
        }
    </script>
</x-app-layout>
