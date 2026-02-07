<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Custom CSS for 3D Buttons -->
            <style>
                .btn-3d {
                    /* Custom properties for maintainability */
                    --btn-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    --btn-shadow-color: rgba(0, 0, 0, 0.2);
                    --btn-highlight: rgba(255, 255, 255, 0.2);
                    --btn-pulse-color: rgba(5, 186, 218, 0.4); /* Default cyan pulse */
                    
                    position: relative;
                    overflow: hidden;
                    transition: var(--btn-transition);
                    box-shadow: 
                        0 4px 6px -1px var(--btn-shadow-color),
                        0 2px 4px -1px var(--btn-shadow-color),
                        inset 0 1px 0 var(--btn-highlight);
                    z-index: 1;
                    background-size: 100% auto;
                }

                /* Gradient Overlay for Lighting Effect */
                .btn-3d::before {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: linear-gradient(to bottom, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.05) 100%);
                    opacity: 0;
                    transition: opacity 0.3s;
                    z-index: -1;
                }

                /* Hover State: Lift up + Deep Shadow + Pulse */
                .btn-3d:hover {
                    transform: translateY(-2px);
                    /* Combine 3D shadow with Pulse animation logic (handled in keyframes) */
                    background-position: right center;
                    background-size: 200% auto;
                    animation: pulse512 1.5s infinite;
                }
                
                .btn-3d:hover::before {
                    opacity: 1;
                }

                /* Active State: Press down */
                .btn-3d:active {
                    transform: translateY(1px);
                    box-shadow: 
                        0 2px 4px -1px var(--btn-shadow-color),
                        inset 0 2px 4px rgba(0,0,0,0.1);
                    animation: none;
                }

                /* Gold Variant */
                .btn-3d-gold {
                    background: linear-gradient(135deg, #EEA727 0%, #D97706 100%);
                    --btn-pulse-color: rgba(238, 167, 39, 0.6);
                }

                /* Blue Variant */
                .btn-3d-blue {
                    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
                    --btn-pulse-color: rgba(37, 99, 235, 0.6);
                }

                /* Pulse Animation */
                @keyframes pulse512 {
                    0% {
                        box-shadow: 
                            0 10px 15px -3px var(--btn-shadow-color),
                            0 4px 6px -2px var(--btn-shadow-color),
                            inset 0 1px 0 var(--btn-highlight),
                            0 0 0 0 var(--btn-pulse-color);
                    }
                    70% {
                        box-shadow: 
                            0 10px 15px -3px var(--btn-shadow-color),
                            0 4px 6px -2px var(--btn-shadow-color),
                            inset 0 1px 0 var(--btn-highlight),
                            0 0 0 10px rgba(255, 255, 255, 0);
                    }
                    100% {
                        box-shadow: 
                            0 10px 15px -3px var(--btn-shadow-color),
                            0 4px 6px -2px var(--btn-shadow-color),
                            inset 0 1px 0 var(--btn-highlight),
                            0 0 0 0 rgba(255, 255, 255, 0);
                    }
                }

                /* Shimmer Animation */
                @keyframes shimmer {
                    0% { transform: translateX(-100%) skewX(-15deg); }
                    100% { transform: translateX(200%) skewX(-15deg); }
                }
                
                .shimmer {
                    position: relative;
                    overflow: hidden;
                }
                
                .shimmer::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 50%;
                    height: 100%;
                    background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
                    transform: skewX(-15deg);
                    animation: shimmer 2s infinite;
                    pointer-events: none;
                }

                /* Reactor Switch CSS */
                .reactor-widget {
                    display: inline-block;
                    vertical-align: middle;
                    transform: scale(0.4);
                    transform-origin: left center;
                    margin-right: -70px; /* Compensation for scale spacing */
                }

                /* Hide input */
                .reactor-widget .reactor-switch input {
                  display: none;
                }

                /* Track / Shell */
                .reactor-widget .reactor-switch label {
                  width: 120px;
                  height: 55px;
                  position: relative;
                  display: block;
                  cursor: pointer;
                  border-radius: 999px;
                  overflow: hidden;
                  isolation: isolate;

                  /* Inactive: Dark Red Background */
                  background: linear-gradient(180deg, #2d0a0a 0%, #1a0505 100%);
                  border: 1px solid #ffffff10;

                  box-shadow:
                    inset 0 8px 20px #000a,
                    inset 0 1px 0 #ffffff08,
                    0 10px 28px #0007;

                  transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
                }

                /* Outer Rim */
                .reactor-widget .reactor-switch label::before {
                  content: "";
                  position: absolute;
                  inset: 2px;
                  border-radius: 999px;
                  box-shadow:
                    inset 0 0 0 1px #ffffff08,
                    inset 0 -4px 10px #000b,
                    inset 0 4px 10px #ffffff05;
                  z-index: 0;
                }

                /* Inner Core - Inactive (Red) */
                .reactor-widget .reactor-switch__core {
                  position: absolute;
                  inset: 6px;
                  border-radius: 999px;
                  /* Dark Red Gradient */
                  background: radial-gradient(110px 70px at 18% 50%, #ffffff0c, transparent 60%),
                    radial-gradient(120px 80px at 82% 50%, #00000080, transparent 65%),
                    linear-gradient(90deg, #451a1a, #2b0b0b);
                  box-shadow:
                    inset 0 0 0 1px #ffffff06,
                    inset 0 0 18px #000b;
                  transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
                  z-index: 1;
                }

                /* Energy Beam */
                .reactor-widget .reactor-switch__beam {
                  position: absolute;
                  inset: -60% -30%;
                  background: conic-gradient(
                    from 110deg,
                    transparent 0 18%,
                    #ff000018 26%, 
                    transparent 40% 55%, 
                    #ff4d4d18 66%, 
                    transparent 78% 100% 
                  );
                  filter: blur(20px);
                  opacity: 0.22;
                  animation: reactorBeam 6s linear infinite;
                  z-index: 0;
                }

                @keyframes reactorBeam {
                  to {
                    transform: translateX(18%) rotate(360deg);
                  }
                }

                /* Thumb */
                .reactor-widget .reactor-switch__thumb {
                  position: absolute;
                  top: 5px;
                  left: 5px;
                  width: 45px;
                  height: 45px;
                  border-radius: 50%;
                  z-index: 3;

                  background: radial-gradient(
                    circle at 25% 20%,
                    #ffffff 0%,
                    #ffeaf6 45%, 
                    #eab4b4 100% 
                  );

                  box-shadow:
                    0 8px 16px #0009,
                    inset 0 3px 8px #ffffff,
                    inset -6px -9px 14px #c6909066;

                  transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
                }

                /* Inner Lens */
                .reactor-widget .reactor-switch__thumb::before {
                  content: "";
                  position: absolute;
                  inset: 5px;
                  border-radius: 50%;
                  background: radial-gradient(circle at 30% 25%, #ffffff66, transparent 55%),
                    radial-gradient(circle at 75% 80%, #00000066, transparent 60%);
                  box-shadow: inset 0 0 0 1px #ffffff20;
                }

                /* Glow Tail */
                .reactor-widget .reactor-switch__thumb::after {
                  content: "";
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  width: 80px;
                  height: 80px;
                  transform: translate(-50%, -50%);
                  background: radial-gradient(circle, #ff000022 0%, transparent 60%),
                    radial-gradient(circle, #ff4d4d22 0%, transparent 65%);
                  filter: blur(12px);
                  opacity: 0;
                  transition: 600ms ease;
                }

                /* OFF / ON Text */
                .reactor-widget .reactor-switch__state {
                  position: absolute;
                  top: 50%;
                  transform: translateY(-50%);
                  font-weight: 800;
                  letter-spacing: 0.2em;
                  font-size: 10px;
                  user-select: none;
                  z-index: 2;
                  transition: 600ms ease;
                }

                .reactor-widget .reactor-switch__state--off {
                  right: 16px;
                  color: #e0c7c7b5;
                }
                .reactor-widget .reactor-switch__state--on {
                  left: 16px;
                  color: #001611;
                  opacity: 0;
                }

                /* === ON STATE (Green) === */
                .reactor-widget .reactor-switch input:checked + label {
                  border-color: #ffffff20;
                  box-shadow:
                    0 0 14px #00ff8833,
                    0 0 30px #7bffb533,
                    inset 0 8px 18px #ffffff18,
                    0 10px 28px #0009;
                }

                .reactor-widget .reactor-switch input:checked + label .reactor-switch__core {
                  /* Green Gradient */
                  background: linear-gradient(90deg, #00ff88, #7bffb5, #22c55e);
                  box-shadow:
                    inset 0 0 0 1px #ffffff18,
                    inset 0 0 20px #00ff8844;
                }

                .reactor-widget .reactor-switch input:checked + label .reactor-switch__beam {
                   background: conic-gradient(
                     from 110deg,
                     transparent 0 18%,
                     #00ff8818 26%, 
                     transparent 40% 55%, 
                     #7bffb518 66%, 
                     transparent 78% 100% 
                   );
                }

                /* Move Thumb */
                .reactor-widget .reactor-switch input:checked + label .reactor-switch__thumb {
                  left: 70px;
                  background: radial-gradient(
                    circle at 30% 25%,
                    #06331a 0%,
                    #031c0d 55%,
                    #001205 100%
                  );
                  box-shadow:
                    0 8px 18px #000c,
                    0 0 14px #00ff8855,
                    inset 0 0 12px #00ff8866,
                    inset -6px -9px 16px #00ff8855;
                }

                .reactor-widget .reactor-switch input:checked + label .reactor-switch__thumb::after {
                  opacity: 1;
                  background: radial-gradient(circle, #00ff8822 0%, transparent 60%),
                    radial-gradient(circle, #7bffb522 0%, transparent 65%);
                }

                /* Switch Text Visibility */
                .reactor-widget .reactor-switch input:checked + label .reactor-switch__state--off {
                  opacity: 0;
                }
                .reactor-widget .reactor-switch input:checked + label .reactor-switch__state--on {
                  opacity: 1;
                }

                /* Focus */
                .reactor-widget .reactor-switch input:focus-visible + label {
                  outline: 2px solid #ffffff44;
                  outline-offset: 4px;
                }
            </style>

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-2 text-sm text-gray-600 bg-white p-2 rounded shadow-sm border border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold text-gray-700">Harga Update :</span>
                    <span class="text-blue-600 font-bold">
                        {{ isset($lastPriceUpdate) && $lastPriceUpdate ? \Carbon\Carbon::parse($lastPriceUpdate)->locale('id')->isoFormat('dddd, D MMMM Y - [Pkl.] HH.mm [WIB]') : '-' }}
                    </span>
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('admin.products.import') }}" class="btn-3d btn-3d-gold shimmer text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm h-12">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import Produk
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="btn-3d btn-3d-blue shimmer text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm h-12">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Product
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand / Category</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (SC)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($product->image)
                                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover rounded" width="40" height="40" loading="lazy">
                                            @else
                                                <span class="text-gray-400">No Image</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->brand->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->category->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</div>
                                            @if($product->price_customer)
                                                <div class="text-xs text-gray-500 line-through">Rp {{ number_format($product->price_customer, 0, ',', '.') }}</div>
                                            @elseif($product->price_msrp)
                                                <div class="text-xs text-gray-500 line-through">Rp {{ number_format($product->price_msrp, 0, ',', '.') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($product->weight, 0, ',', '.') }} gr
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" x-data="{
                                            active: {{ $product->is_active ? 'true' : 'false' }},
                                            loading: false,
                                            error: null,
                                            async toggle() {
                                                this.error = null;
                                                const newState = this.active;
                                                this.loading = true;
                                                try {
                                                    const res = await fetch('{{ route('admin.products.update-active', $product) }}', {
                                                        method: 'PATCH',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'Accept': 'application/json',
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                        },
                                                        body: JSON.stringify({ is_active: newState ? 1 : 0 })
                                                    });
                                                    if (!res.ok) { throw new Error('Request failed'); }
                                                    const data = await res.json();
                                                    this.active = !!data.is_active;
                                                } catch (e) {
                                                    this.active = !newState;
                                                    this.error = 'Gagal';
                                                } finally {
                                                    this.loading = false;
                                                }
                                            }
                                        }">
                                            <div class="flex items-center">
                                                <div class="reactor-widget scale-[0.4] origin-left -mr-[70px]">
                                                    <div class="reactor-switch">
                                                        <input type="checkbox" id="toggle-{{ $product->id }}" x-model="active" @change="toggle" hidden>
                                                        <label for="toggle-{{ $product->id }}">
                                                            <div class="reactor-switch__core"></div>
                                                            <div class="reactor-switch__beam"></div>
                                                            <div class="reactor-switch__thumb"></div>
                                                            <span class="reactor-switch__state reactor-switch__state--off">OFF</span>
                                                            <span class="reactor-switch__state reactor-switch__state--on">ON</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex flex-col ml-2">
                                                    <span class="text-xs font-semibold" :class="active ? 'text-green-700' : 'text-red-700'" x-text="active ? 'Active' : 'Inactive'"></span>
                                                    <template x-if="loading">
                                                        <span class="text-[10px] text-gray-500">Updating...</span>
                                                    </template>
                                                    <template x-if="error">
                                                        <span class="text-[10px] text-red-600" x-text="error"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>


</x-app-layout>
