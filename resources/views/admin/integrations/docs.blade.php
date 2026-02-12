<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sistem Integrasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div x-data="{ 
                            activeTab: new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('integration_docs_tab') || 'rajaongkir',
                            toggle(tab) {
                                this.activeTab = this.activeTab === tab ? null : tab;
                                localStorage.setItem('integration_docs_tab', this.activeTab);
                            }
                        }" 
                        class="space-y-4">

                        <!-- RajaOngkir Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('rajaongkir')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'rajaongkir'"
                                aria-controls="rajaongkir-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Integrasi RajaOngkir
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'rajaongkir'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'rajaongkir'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="rajaongkir-content"
                                x-show="activeTab === 'rajaongkir'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        Kami menggunakan RajaOngkir untuk menghitung biaya pengiriman dari lokasi distributor ke alamat pelanggan.
                                    </p>
                                    <ul>
                                        <li><strong>Situs Resmi:</strong> <a href="https://rajaongkir.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">rajaongkir.com</a></li>
                                        <li><strong>Dokumentasi:</strong> <a href="https://rajaongkir.com/docs/shipping-cost/getting_started/about" target="_blank" class="text-indigo-600 hover:text-indigo-900">Dokumentasi API</a></li>
                                        <li><strong>Tipe Akun:</strong> Starter (Gratis), Basic, Pro. Pastikan Anda memilih tipe yang benar di pengaturan.</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Panduan Konfigurasi</h4>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>Daftar di RajaOngkir (atau Komerce untuk V2).</li>
                                        <li>Masuk ke menu API Key dan salin kunci Anda.</li>
                                        <li>Tempel kunci di tab <a href="{{ route('admin.integrations.shipping') }}" class="text-indigo-600 hover:text-indigo-900">Pengaturan RajaOngkir</a>.</li>
                                        <li>Atur Base URL (Default V2: <code>https://rajaongkir.komerce.id/api/v1</code>).</li>
                                        <li>Cari dan pilih <strong>Asal Toko</strong> (Kecamatan).</li>
                                        <li>Pilih <strong>Kurir Aktif</strong> yang ingin Anda gunakan.</li>
                                        <li>Simpan Perubahan dan gunakan tombol "Tes Koneksi".</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">Pemecahan Masalah</h4>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li><strong>Koneksi Gagal:</strong> Verifikasi API Key dan Base URL.</li>
                                        <li><strong>Biaya Tidak Ditemukan:</strong> Periksa apakah rute didukung oleh kurir (mis. beberapa kurir tidak melayani rute tertentu).</li>
                                        <li><strong>Masalah Berat:</strong> Pastikan berat produk diatur dengan benar (dalam gram).</li>
                                        <li><strong>Dukungan V2:</strong> Integrasi ini dioptimalkan untuk RajaOngkir V2 (Komerce). Jika menggunakan V1/Starter, beberapa fitur seperti pencarian kecamatan mungkin berperilaku berbeda.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- API ID Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('api_id')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'api_id'"
                                aria-controls="api_id-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Integrasi API ID (Data Wilayah & Ongkir)
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'api_id'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'api_id'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="api_id-content"
                                x-show="activeTab === 'api_id'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        API ID digunakan sebagai penyedia data wilayah administratif Indonesia (Regional) dan alternatif kalkulasi ongkos kirim.
                                    </p>
                                    <ul>
                                        <li><strong>Situs Resmi:</strong> <a href="https://api.co.id" target="_blank" class="text-indigo-600 hover:text-indigo-900">api.co.id</a></li>
                                        <li><strong>Base URL Default:</strong> <code>https://use.api.co.id</code></li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Panduan Konfigurasi</h4>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>Daftar dan dapatkan API Key dari dashboard API ID.</li>
                                        <li>Masuk ke tab <a href="{{ route('admin.integrations.shipping') }}" class="text-indigo-600 hover:text-indigo-900">Pengaturan Pengiriman</a>.</li>
                                        <li>Pilih Provider Pengiriman: <strong>API ID</strong>.</li>
                                        <li>Masukkan <strong>API Key</strong> (x-api-co-id) Anda.</li>
                                        <li>Pastikan Base URL sesuai (Default: <code>https://use.api.co.id</code>).</li>
                                        <li>Simpan dan gunakan tombol "Tes Koneksi" untuk memverifikasi.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">Fitur & Pemecahan Masalah</h4>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li><strong>Data Wilayah:</strong> API ID menyediakan data hingga level Kelurahan/Desa.</li>
                                        <li><strong>Pencarian Lokasi:</strong> Mendukung pencarian berdasarkan nama kelurahan atau kecamatan.</li>
                                        <li><strong>Ongkos Kirim:</strong> Berat dihitung dalam satuan Kg (dibulatkan ke atas). Pastikan berat produk diinput dalam gram dengan benar.</li>
                                        <li><strong>Error 401:</strong> Biasanya disebabkan oleh API Key yang salah atau kadaluarsa.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('payment')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'payment'"
                                aria-controls="payment-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    Payment Gateway (Midtrans)
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'payment'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'payment'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="payment-content"
                                x-show="activeTab === 'payment'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        Midtrans digunakan untuk verifikasi pembayaran otomatis dan berbagai saluran pembayaran (Virtual Account, E-Wallet, dll).
                                    </p>
                                    <ul>
                                        <li><strong>Situs Resmi:</strong> <a href="https://midtrans.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">midtrans.com</a></li>
                                        <li><strong>Dokumentasi Teknis:</strong> <a href="https://docs.midtrans.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">docs.midtrans.com</a></li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Panduan Konfigurasi</h4>
                                    <ol>
                                        <li>Daftar di Midtrans (Passport).</li>
                                        <li>Akses Dashboard (Sandbox untuk pengujian, Production untuk live).</li>
                                        <li>Masuk ke Settings > Access Keys.</li>
                                        <li>Salin Merchant ID, Client Key, dan Server Key.</li>
                                        <li>Tempel di tab <a href="{{ route('admin.integrations.payment') }}" class="text-indigo-600 hover:text-indigo-900">Pengaturan Pembayaran</a>.</li>
                                        <li>Pastikan "Mode Produksi" tidak dicentang untuk pengujian.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Brevo Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('brevo')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'brevo'"
                                aria-controls="brevo-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    Brevo (Email Marketing)
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'brevo'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'brevo'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="brevo-content"
                                x-show="activeTab === 'brevo'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        Brevo (sebelumnya Sendinblue) menangani email transaksional dan kampanye pemasaran.
                                    </p>
                                    <ul>
                                        <li><strong>Situs Resmi:</strong> <a href="https://brevo.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">brevo.com</a></li>
                                        <li><strong>Dokumentasi API:</strong> <a href="https://developers.brevo.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">developers.brevo.com</a></li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Panduan Konfigurasi</h4>
                                    <ol>
                                        <li>Daftar di Brevo.</li>
                                        <li>Navigasi ke <strong>SMTP & API</strong> > <strong>API Keys</strong>.</li>
                                        <li>Buat API Key baru (v3).</li>
                                        <li>Masukkan kunci di tab <a href="{{ route('admin.integrations.email') }}" class="text-indigo-600 hover:text-indigo-900">Pengaturan Email</a>.</li>
                                        <li>Atur email dan nama pengirim yang terverifikasi.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">Dukungan Fitur</h4>
                                    <ul>
                                        <li><strong>Email Transaksional:</strong> Tingkat pengiriman tinggi untuk notifikasi pesanan.</li>
                                        <li><strong>Sinkronisasi Kontak:</strong> Tambahkan pengguna ke daftar tertentu untuk pemasaran.</li>
                                        <li><strong>Pelacakan:</strong> Pelacakan buka dan klik diaktifkan secara default di dashboard Brevo.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- EPI APE Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('epi_ape')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'epi_ape'"
                                aria-controls="epi_ape-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    Integrasi EPI APE (Auto Price Engine)
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'epi_ape'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'epi_ape'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="epi_ape-content"
                                x-show="activeTab === 'epi_ape'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        Sistem ini mengotomatisasi pembaruan harga "Distributor Price (Silverchannel)" pada aplikasi EPI-OSS dengan mengambil data dari API EPI APE.
                                    </p>
                                    
                                    <h4 class="mt-4 font-semibold">1. Persyaratan Server & Deployment</h4>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li><strong>PHP Version:</strong> 8.3+</li>
                                        <li><strong>Queue Worker:</strong> Supervisor harus aktif menjalankan <code>php artisan queue:work</code>.</li>
                                        <li><strong>Scheduler:</strong> Cron job server harus aktif menjalankan <code>php artisan schedule:run</code> setiap menit.</li>
                                        <li><strong>Koneksi Internet:</strong> Server harus bisa mengakses endpoint API EPI APE (whitelist IP jika diperlukan).</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">2. Konfigurasi Cron Job</h4>
                                    <p>Pastikan entry berikut ada di Crontab server (<code>crontab -e</code>):</p>
                                    <pre class="bg-gray-100 dark:bg-gray-900 p-2 rounded"><code>* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1</code></pre>
                                    <p class="text-sm mt-1">Scheduler internal Laravel akan menjalankan command <code>app:fetch-epi-prices</code> sesuai jadwal yang ditentukan (default: setiap jam/menit sesuai Kernel).</p>

                                    <h4 class="mt-4 font-semibold">3. Prosedur Operasional</h4>
                                    <h5 class="font-semibold text-sm mt-2">A. Menjalankan Manual (Testing)</h5>
                                    <p>Untuk memicu update harga di luar jadwal otomatis:</p>
                                    <pre class="bg-gray-100 dark:bg-gray-900 p-2 rounded"><code>php artisan app:fetch-epi-prices</code></pre>
                                    
                                    <h5 class="font-semibold text-sm mt-2">B. Rollback / Emergency Stop</h5>
                                    <p>Jika terjadi kesalahan update harga massal:</p>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>Matikan scheduler sementara (comment di crontab) atau matikan fitur di env: <code>EPI_APE_ENABLED=false</code> (jika didukung).</li>
                                        <li>Cek log audit di tabel <code>product_price_histories</code> untuk melihat harga sebelumnya.</li>
                                        <li>Lakukan restore harga manual atau via script database jika diperlukan.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">4. Monitoring & Troubleshooting</h4>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li><strong>Log File:</strong> Cek <code>storage/logs/laravel.log</code> untuk pesan "FetchEpiPricesCommand" atau "Job failed".</li>
                                        <li><strong>Database Audit:</strong> Tabel <code>product_price_histories</code> mencatat setiap perubahan harga.</li>
                                        <li><strong>Job Gagal:</strong> Cek tabel <code>failed_jobs</code> jika antrian macet. Jalankan <code>php artisan queue:retry all</code> untuk mencoba ulang.</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">5. Keamanan</h4>
                                    <p>API Endpoint EPI APE harus dilindungi. Pastikan:</p>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li>URL API dikonfigurasi di <code>.env</code> (jangan hardcode).</li>
                                        <li>Gunakan HTTPS untuk enkripsi data in-transit.</li>
                                        <li>Validasi payload respons API sebelum diproses ke database.</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">6. Integrasi Live Server</h4>
                                    <p class="text-sm">Saat ini service menggunakan data mock. Untuk live:</p>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>Buka <code>App\Services\EpiApePriceService.php</code>.</li>
                                        <li>Update method <code>fetchPrices()</code> untuk menggunakan <code>Http::get()</code> ke endpoint nyata.</li>
                                        <li>Pastikan respons API sesuai dengan format yang diharapkan (array of SKU, price, updated_at).</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Email Templates Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('email-templates')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'email-templates'"
                                aria-controls="email-templates-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path></svg>
                                    Panduan Template Email
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'email-templates'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'email-templates'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="email-templates-content"
                                x-show="activeTab === 'email-templates'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <p>
                                        Kelola konten email otomatis yang dikirim oleh sistem. Template mendukung variabel dinamis dan konten HTML.
                                    </p>

                                    <h4 class="mt-4 font-semibold">1. Konfigurasi</h4>
                                    <ol>
                                        <li>Pergi ke tab <a href="{{ route('admin.integrations.email') }}" class="text-indigo-600 hover:text-indigo-900">Integrasi -> API Email</a>.</li>
                                        <li>Gulir ke bawah ke bagian <strong>Email Templates</strong>.</li>
                                        <li>Klik <strong>Tambah Template Baru</strong> atau <strong>Edit</strong> yang sudah ada.</li>
                                        <li><strong>Key:</strong> Pengenal unik yang digunakan oleh sistem (misal: <code>order_confirmation</code>). Jangan ubah ini untuk template sistem.</li>
                                        <li><strong>Subject:</strong> Judul email (mendukung variabel).</li>
                                        <li><strong>Body:</strong> Konten HTML dari email.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">2. Parameter Dinamis</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Gunakan placeholder ini di Subject atau Body untuk menyisipkan data dinamis.</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <h5 class="font-bold text-sm mb-2">Variabel Global</h5>
                                            <ul class="text-xs space-y-1 font-mono">
                                                <li>@{{app_name}} - Nama Aplikasi</li>
                                                <li>@{{logo_url}} - URL Logo</li>
                                                <li>@{{support_email}} - Email Dukungan</li>
                                                <li>@{{year}} - Tahun Saat Ini</li>
                                            </ul>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <h5 class="font-bold text-sm mb-2">Konfirmasi Pesanan</h5>
                                            <ul class="text-xs space-y-1 font-mono">
                                                <li>@{{customer_name}} - Nama Pelanggan</li>
                                                <li>@{{order_number}} - ID Pesanan</li>
                                                <li>@{{total_amount}} - Total Harga</li>
                                                <li>@{{product_list}} - Tabel HTML item</li>
                                                <li>@{{payment_method}} - Tipe Pembayaran</li>
                                                <li>@{{shipping_courier}} - Nama Kurir</li>
                                                <li>@{{tracking_url}} - Link pelacakan pesanan</li>
                                            </ul>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <h5 class="font-bold text-sm mb-2">Selamat Datang Silverchannel</h5>
                                            <ul class="text-xs space-y-1 font-mono">
                                                <li>@{{name}} - Nama Pengguna</li>
                                                <li>@{{login_url}} - URL Halaman Login</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <h4 class="mt-4 font-semibold">3. Implementasi Developer</h4>
                                    <pre class="bg-gray-800 text-gray-100 p-3 rounded text-xs overflow-x-auto">
// Kirim Konfirmasi Pesanan
$order->notify(new \App\Notifications\OrderConfirmation($order));

// Kirim Email Selamat Datang
$user->notify(new \App\Notifications\WelcomeSilverchannel());</pre>

                                    <h4 class="mt-4 font-semibold">4. Pemecahan Masalah</h4>
                                    <ul class="list-disc ml-5 space-y-1 text-sm">
                                        <li><strong>Variabel tidak muncul:</strong> Pastikan sintaks variabel tepat <code>@{{variable_name}}</code> dengan kurung kurawal ganda.</li>
                                        <li><strong>Email tidak terkirim:</strong> Periksa konfigurasi Brevo dan log di tab Brevo.</li>
                                        <li><strong>Tampilan rusak:</strong> Gunakan gaya CSS inline untuk kompatibilitas maksimal di berbagai klien email.</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">5. Pengujian</h4>
                                    <p class="text-sm">
                                        Gunakan tombol <strong>Preview</strong> di daftar template untuk melihat tampilan email dengan data dummy. 
                                        Untuk pengujian nyata, gunakan fitur <strong>Tes Koneksi</strong> di pengaturan Brevo atau picu tindakan nyata (misal: buat pesanan tes).
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Developer Guide Section -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <button 
                                @click="toggle('dev_guide')"
                                type="button"
                                class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :aria-expanded="activeTab === 'dev_guide'"
                                aria-controls="dev-content"
                            >
                                <span class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    Panduan Developer & Contoh Kode
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <svg x-show="activeTab !== 'dev_guide'" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="activeTab === 'dev_guide'" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>
                            
                            <div 
                                id="dev-content"
                                x-show="activeTab === 'dev_guide'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                                style="display: none;"
                            >
                                <div class="prose dark:prose-invert max-w-none">
                                    <h4 class="font-semibold">Contoh Kode (Penggunaan Service)</h4>
                                    <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-x-auto">
// Injeksi IntegrationService
use App\Services\IntegrationService;

public function __construct(IntegrationService $service) {
    $this->service = $service;
}

// Ambil Konfigurasi
$apiKey = $this->service->get('rajaongkir_api_key');

// Log Panggilan Eksternal
$this->service->log('rajaongkir', '/cost', 'POST', $payload, $response, 200, 150);
                                    </pre>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>