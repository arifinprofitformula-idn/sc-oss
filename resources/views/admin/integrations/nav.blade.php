<div class="mb-6 border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex space-x-8">
        <a href="{{ route('admin.integrations.shipping') }}"
           class="{{ request()->routeIs('admin.integrations.shipping') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            API Ongkir
        </a>

        <a href="{{ route('admin.integrations.payment') }}"
           class="{{ request()->routeIs('admin.integrations.payment') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            Payment Gateway
        </a>

        <a href="{{ route('admin.integrations.brevo') }}"
           class="{{ request()->routeIs('admin.integrations.brevo') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            Brevo (Email)
        </a>

        <a href="{{ route('admin.integrations.epi-ape') }}"
           class="{{ request()->routeIs('admin.integrations.epi-ape') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            EPI APE
        </a>

        <a href="{{ route('admin.email-templates.index') }}"
           class="{{ request()->routeIs('admin.email-templates.*') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            Email Templates
        </a>

        <a href="{{ route('admin.integrations.docs') }}"
           class="{{ request()->routeIs('admin.integrations.docs') 
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
            Documentation
        </a>
    </nav>
</div>
