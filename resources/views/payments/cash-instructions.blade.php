<x-app-layout>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M21,4H3A2,2 0 0,0 1,6V18A2,2 0 0,0 3,20H21A2,2 0 0,0 23,18V6A2,2 0 0,0 21,4M21,18H3V6H21V18Z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Cash Payment Instructions</h1>
            <p class="text-lg text-gray-600">Complete your {{ $payment->payment_type }} payment with cash</p>
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
            <div class="bg-green-50 px-6 py-4 border-b border-green-200">
                <h2 class="text-xl font-semibold text-green-800 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Payment Details
                </h2>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Payment ID</p>
                        <p class="text-lg font-mono text-gray-900">{{ $payment->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Amount</p>
                        <p class="text-2xl font-bold text-green-600">{{ $payment->formatted_amount }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Type</p>
                        <p class="text-lg text-gray-900 capitalize">{{ $payment->payment_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Pending Payment
                        </span>
                    </div>
                </div>
                
                <!-- Reference Number -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <p class="text-sm font-medium text-gray-700 mb-2">Payment Reference (Please bring this):</p>
                    <p class="text-xl font-mono font-bold text-gray-900 bg-white px-3 py-2 rounded border">CASH-{{ $payment->id }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route(\"payments.index\") }}" 
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                View My Payments
            </a>
            
            <a href="{{ route(\"dashboard\") }}" 
               class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
