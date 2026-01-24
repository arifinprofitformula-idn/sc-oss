<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div x-data="{ 
                            activeTab: localStorage.getItem('integration_docs_tab') || 'rajaongkir',
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
                                    RajaOngkir Integration
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
                                        We use RajaOngkir to calculate shipping costs from the distributor's location to the customer's address.
                                    </p>
                                    <ul>
                                        <li><strong>Official Website:</strong> <a href="https://rajaongkir.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">rajaongkir.com</a></li>
                                        <li><strong>Documentation:</strong> <a href="https://rajaongkir.com/docs/shipping-cost/getting_started/about" target="_blank" class="text-indigo-600 hover:text-indigo-900">API Documentation</a></li>
                                        <li><strong>Account Types:</strong> Starter (Free), Basic, Pro. Ensure you select the correct type in settings.</li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Setup Guide</h4>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>Register at RajaOngkir (or Komerce for V2).</li>
                                        <li>Go to API Key menu and copy your key.</li>
                                        <li>Paste the key in the <a href="{{ route('admin.integrations.rajaongkir') }}" class="text-indigo-600 hover:text-indigo-900">RajaOngkir Settings</a> tab.</li>
                                        <li>Set the Base URL (Default V2: <code>https://rajaongkir.komerce.id/api/v1</code>).</li>
                                        <li>Search and select your <strong>Store Origin</strong> (Subdistrict).</li>
                                        <li>Select <strong>Active Couriers</strong> you want to use.</li>
                                        <li>Save Changes and use "Test Connection" button.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">Troubleshooting</h4>
                                    <ul class="list-disc ml-5 space-y-1">
                                        <li><strong>Connection Failed:</strong> Verify API Key and Base URL.</li>
                                        <li><strong>No Costs Found:</strong> Check if the route is supported by the courier (e.g. some couriers don't serve specific routes).</li>
                                        <li><strong>Weight Issues:</strong> Ensure product weights are set correctly (in grams).</li>
                                        <li><strong>V2 Support:</strong> This integration is optimized for RajaOngkir V2 (Komerce). If using V1/Starter, some features like subdistrict search might behave differently.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateway Section -->
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
                                        Midtrans is used for automatic payment verification and various payment channels (Virtual Account, E-Wallet, etc.).
                                    </p>
                                    <ul>
                                        <li><strong>Official Website:</strong> <a href="https://midtrans.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">midtrans.com</a></li>
                                        <li><strong>Technical Docs:</strong> <a href="https://docs.midtrans.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">docs.midtrans.com</a></li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Setup Guide</h4>
                                    <ol>
                                        <li>Register at Midtrans (Passport).</li>
                                        <li>Access the Dashboard (Sandbox for testing, Production for live).</li>
                                        <li>Go to Settings > Access Keys.</li>
                                        <li>Copy Merchant ID, Client Key, and Server Key.</li>
                                        <li>Paste them in the <a href="{{ route('admin.integrations.payment') }}" class="text-indigo-600 hover:text-indigo-900">Payment Settings</a> tab.</li>
                                        <li>Ensure "Production Mode" is unchecked for testing.</li>
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
                                        Brevo (formerly Sendinblue) handles transactional emails and marketing campaigns.
                                    </p>
                                    <ul>
                                        <li><strong>Official Website:</strong> <a href="https://brevo.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">brevo.com</a></li>
                                        <li><strong>API Docs:</strong> <a href="https://developers.brevo.com" target="_blank" class="text-indigo-600 hover:text-indigo-900">developers.brevo.com</a></li>
                                    </ul>

                                    <h4 class="mt-4 font-semibold">Setup Guide</h4>
                                    <ol>
                                        <li>Register at Brevo.</li>
                                        <li>Navigate to <strong>SMTP & API</strong> > <strong>API Keys</strong>.</li>
                                        <li>Generate a new API Key (v3).</li>
                                        <li>Enter the key in the <a href="{{ route('admin.integrations.brevo') }}" class="text-indigo-600 hover:text-indigo-900">Brevo Settings</a> tab.</li>
                                        <li>Set your verified sender email and name.</li>
                                    </ol>

                                    <h4 class="mt-4 font-semibold">Features Support</h4>
                                    <ul>
                                        <li><strong>Transactional Emails:</strong> High deliverability for order notifications.</li>
                                        <li><strong>Contact Sync:</strong> Add users to specific lists for marketing.</li>
                                        <li><strong>Tracking:</strong> Open and click tracking enabled by default in Brevo dashboard.</li>
                                    </ul>
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
                                    Developer Guide & Code Examples
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
                                    <h4 class="font-semibold">Code Example (Service Usage)</h4>
                                    <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-x-auto">
// Inject IntegrationService
use App\Services\IntegrationService;

public function __construct(IntegrationService $service) {
    $this->service = $service;
}

// Get Config
$apiKey = $this->service->get('rajaongkir_api_key');

// Log External Call
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
