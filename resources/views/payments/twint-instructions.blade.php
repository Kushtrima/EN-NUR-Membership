<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="2" />
    
    <div class="card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="icon-twint" style="font-size: 2rem;"></span>
                <h1 class="card-title" style="margin: 0; color: #FF6B35;">TWINT Payment</h1>
            </div>
            <p style="color: #6c757d;">Complete your payment using the TWINT mobile app</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Customer Information Form -->
            <div style="padding: 2rem; background: rgba(255, 107, 53, 0.05); border-radius: 8px; border-left: 4px solid #FF6B35;">
                <h3 style="color: #FF6B35; margin-bottom: 1rem;">Customer Information</h3>
                <form id="twint-payment-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">First Name *</label>
                            <input type="text" id="twint-first-name" value="{{ explode(' ', auth()->user()->name)[0] ?? '' }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Last Name *</label>
                            <input type="text" id="twint-last-name" value="{{ explode(' ', auth()->user()->name, 2)[1] ?? '' }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Email Address *</label>
                        <input type="email" id="twint-email" value="{{ auth()->user()->email }}" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Mobile Phone Number *</label>
                        <input type="tel" id="twint-phone" placeholder="+41 79 123 45 67" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        <small style="color: #6c757d;">The phone number linked to your TWINT account</small>
                    </div>

                    <!-- QR Code Section -->
                    <div style="border-top: 1px solid #e9ecef; padding-top: 1rem; margin-top: 1.5rem; text-align: center;">
                        <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.95rem;">📱 Scan to Pay</h4>
                        <div style="width: 150px; height: 150px; background: white; border: 2px solid #FF6B35; border-radius: 8px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: #6c757d;">
                            QR Code<br>
                            <small>(Demo Mode)</small>
                        </div>
                        <p style="font-size: 0.85rem; color: #6c757d; margin: 0;">
                            Open TWINT app and scan this code
                        </p>
                    </div>

                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem; margin-top: 1rem;">
                        <strong style="color: #0c5460;">🔒 Demo Mode:</strong> This is a demonstration. No real charges will be made.
                    </div>
                </form>
            </div>

            <!-- Payment Summary -->
            <div style="padding: 2rem; background: rgba(31, 110, 56, 0.05); border-radius: 8px; border-left: 4px solid #1F6E38;">
                <h3 style="color: #1F6E38; margin-bottom: 1rem;">Payment Summary</h3>
                
                <div style="space-y: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Type:</span>
                        <span>{{ ucfirst($payment->payment_type) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Amount:</span>
                        <span style="font-weight: bold; font-size: 1.2rem; color: #1F6E38;">{{ $payment->formatted_amount }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Payment ID:</span>
                        <span style="font-family: monospace; font-size: 0.9rem;">#{{ $payment->id }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">TWINT Fee:</span>
                        <span style="color: #6c757d;">CHF 0.00</span>
                    </div>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">Total:</span>
                        <span style="font-weight: bold; font-size: 1.3rem; color: #1F6E38;">{{ $payment->formatted_amount }}</span>
                    </div>
                </div>

                <!-- TWINT Features -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #FF6B35; margin-bottom: 0.5rem; font-size: 0.9rem;">📱 TWINT Features</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.8rem; color: #6c757d;">
                        <li>Instant mobile payments</li>
                        <li>Secure Swiss payment system</li>
                        <li>No transaction fees</li>
                        <li>Real-time confirmation</li>
                    </ul>
                </div>

                <!-- Instructions -->
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #495057; margin-bottom: 0.5rem; font-size: 0.9rem;">📋 How to Pay</h4>
                    <ol style="margin: 0; padding-left: 1.5rem; font-size: 0.8rem; color: #6c757d;">
                        <li>Open TWINT app</li>
                        <li>Tap "Scan QR-Code"</li>
                        <li>Scan the QR code</li>
                        <li>Confirm payment</li>
                        <li>Receive confirmation</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center;">
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button onclick="processTwintPayment()" class="btn" style="background: #FF6B35; color: white; padding: 1rem 2rem; font-size: 1.1rem; font-weight: bold;">
                    📱 Pay {{ $payment->formatted_amount }}
                </button>
                <a href="{{ route('payment.create') }}" class="btn btn-secondary" style="padding: 1rem 2rem;">
                    ← Back to Payment Options
                </a>
            </div>
        </div>
    </div>

    <style>
        .icon-twint {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #FF6B35;
            border-radius: 6px;
        }
        .icon-twint::before { 
            content: 'T';
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 24px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        input:focus, select:focus {
            outline: none !important;
            border: 3px solid #28a745 !important;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2) !important;
            transform: scale(1.02);
        }

        @media (max-width: 768px) {
            div[style*="grid-template-columns: 1fr 1fr"] {
                display: block !important;
            }
        }
    </style>

    <script>
        function processTwintPayment() {
            window.location.href = '{{ route("payment.twint.success", $payment) }}?demo=1';
        }
    </script>
</x-app-layout>
