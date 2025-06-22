<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="3" />
    
    <div class="card text-center">
        <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
        <h1 class="card-title">Payment Successful!</h1>
        <p style="font-size: 1.125rem; margin-bottom: 2rem;">
            Thank you for your {{ $payment->payment_type }}!
        </p>
        
        <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left;">
                <div>
                    <strong>Payment Type:</strong><br>
                    {{ ucfirst($payment->payment_type) }}
                </div>
                <div>
                    <strong>Amount:</strong><br>
                    {{ $payment->formatted_amount }}
                </div>
                <div>
                    <strong>Payment Method:</strong><br>
                    @if($payment->payment_method === 'stripe' && isset($payment->metadata['payment_method_type']))
                        @switch($payment->metadata['payment_method_type'])
                            @case('card')
                                <div style="display: flex; align-items: center; gap: 0.3rem;">
                                    <svg width="16" height="10" viewBox="0 0 32 20" fill="none">
                                        <rect width="32" height="20" rx="3" fill="#374151"/>
                                        <rect x="0.5" y="0.5" width="31" height="19" rx="2.5" stroke="#E5E7EB"/>
                                        <rect x="2" y="5" width="28" height="3" fill="#0070ba"/>
                                        <rect x="2" y="12" width="6" height="2" rx="1" fill="#9CA3AF"/>
                                    </svg>
                                    <span>
                                        Credit/Debit Card
                                        @if(isset($payment->metadata['payment_method_details']['brand']))
                                            ({{ ucfirst($payment->metadata['payment_method_details']['brand']) }}
                                            •••• {{ $payment->metadata['payment_method_details']['last4'] ?? '' }})
                                        @endif
                                    </span>
                                </div>
                                @break
                            @case('apple_pay')
                                <div style="display: flex; align-items: center; gap: 0.3rem;">
                                    <svg width="24" height="10" viewBox="0 0 48 20" fill="none">
                                        <path d="M8.93 3.07c-.53.63-1.42 1.12-2.25 1.05-.11-.86.31-1.79.82-2.37.53-.61 1.46-1.08 2.21-1.11.09.9-.25 1.8-.78 2.43z" fill="#374151"/>
                                        <path d="M15.57 18.24c-.96.98-1.91.84-2.85.5-.99-.35-1.9-.37-2.95 0-1.32.49-2.02.35-2.82-.5-4.56-4.66-3.88-11.67.28-12.02.99-.08 1.68.55 2.26.59.85-.59 1.82-.53 2.75 0 1.18-.94 2.85-.8 3.58.37-3.14 1.93-2.69 6.21.75 7.06z" fill="#374151"/>
                                        <path d="M25.5 6.5h3.2c1.8 0 3.1 1.2 3.1 3s-1.3 3-3.1 3h-1.9v3h-1.3V6.5zm1.3 4.8h1.8c1.1 0 1.8-.6 1.8-1.8s-.7-1.8-1.8-1.8h-1.8v3.6z" fill="#374151"/>
                                    </svg>
                                    <span>Apple Pay</span>
                                </div>
                                @break
                            @case('google_pay')
                                <div style="display: flex; align-items: center; gap: 0.3rem;">
                                    <svg width="28" height="10" viewBox="0 0 61 25" fill="none">
                                        <path d="M25.6 9.4c0-1.4-.6-2.7-1.6-3.6-1.1-.9-2.5-1.4-4-1.4-1.6 0-3 .5-4.1 1.4-1 .9-1.6 2.2-1.6 3.6 0 1.4.6 2.7 1.6 3.6 1.1.9 2.5 1.4 4.1 1.4 1.5 0 2.9-.5 4-1.4 1-.9 1.6-2.2 1.6-3.6zm-2.1 0c0 .8-.3 1.6-.9 2.2-.6.6-1.4.9-2.2.9s-1.6-.3-2.2-.9c-.6-.6-.9-1.4-.9-2.2s.3-1.6.9-2.2c.6-.6 1.4-.9 2.2-.9s1.6.3 2.2.9c.6.6.9 1.4.9 2.2z" fill="#374151"/>
                                        <path d="M44.8 4.6v8.2c0 3.4-2 4.8-4.4 4.8-2.2 0-3.6-1.5-4.1-2.7l1.8-.8c.3.7 1 1.5 2.3 1.5 1.5 0 2.4-.9 2.4-2.6v-.7c-.4.5-1.3 1-2.4 1-2.3 0-4.4-2-4.4-4.6 0-2.6 2.1-4.6 4.4-4.6 1.1 0 2 .5 2.4 1V4.6h2z" fill="#374151"/>
                                    </svg>
                                    <span>Google Pay</span>
                                </div>
                                @break
                            @default
                                {{ ucfirst($payment->payment_method) }}
                        @endswitch
                    @else
                        {{ ucfirst($payment->payment_method) }}
                    @endif
                </div>
                <div>
                    <strong>Transaction ID:</strong><br>
                    {{ $payment->transaction_id }}
                </div>
                <div>
                    <strong>Date:</strong><br>
                    {{ $payment->created_at->format('M d, Y H:i') }}
                </div>
                <div>
                    <strong>Status:</strong><br>
                    <span style="color: #28a745; font-weight: bold;">{{ ucfirst($payment->status) }}</span>
                </div>
            </div>
        </div>

        @if($payment->payment_type === 'membership')
            <div class="alert alert-success">
                <strong>Welcome to our community!</strong><br>
                Your membership is now active. You can access all member benefits immediately.
            </div>
        @else
            <div class="alert alert-success">
                <strong>Thank you for your donation!</strong><br>
                Your contribution helps us continue our mission and make a difference.
            </div>
        @endif

        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
            <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard</a>
            <a href="{{ route('payment.create') }}" class="btn btn-secondary">Make Another Payment</a>
        </div>
    </div>
</x-app-layout> 