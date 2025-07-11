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
            <!-- QR Code Payment Section -->
            <div style="padding: 2rem; background: rgba(255, 107, 53, 0.05); border-radius: 8px; border-left: 4px solid #FF6B35; text-align: center;">
                <h3 style="color: #FF6B35; margin-bottom: 1rem;">📱 TWINT QR Code Payment</h3>
                


                <!-- QR Code -->
                <div style="margin: 1.5rem 0;">
                    <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.95rem;">📱 Scan to Pay {{ $payment->formatted_amount }}</h4>
                    <div style="display: inline-block; padding: 1rem; background: white; border: 2px solid #FF6B35; border-radius: 8px;">
                        <img src="{{ asset('images/payment/twint-qr.png') }}" 
                             alt="TWINT QR Code" 
                             style="width: 200px; height: 200px; display: block;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="width: 200px; height: 200px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; display: none; align-items: center; justify-content: center; font-size: 0.9rem; color: #6c757d; text-align: center;">
                            QR Code<br>
                            <small>Please add twint-qr.png to<br>public/images/payment/</small>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #6c757d; margin-top: 0.5rem;">
                        Scan with your TWINT app
                    </p>
                </div>

                <!-- Alternative payment link -->
                <div style="margin-top: 1rem;">
                    <p style="font-size: 0.85rem; color: #6c757d; margin-bottom: 0.5rem;">Or use this payment link:</p>
                    <a href="https://pay.raisenow.io/qjjpg?lng=en" 
                       target="_blank" 
                       style="display: inline-block; padding: 0.5rem 1rem; background: #FF6B35; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                        Open TWINT Payment →
                    </a>
                </div>
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
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('payment.create') }}" 
               style="display: inline-block; padding: 0.75rem 2rem; border: 2px solid #1F6E38; background: transparent; color: #1F6E38; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;"
               onmouseover="this.style.background='#1F6E38'; this.style.color='white';"
               onmouseout="this.style.background='transparent'; this.style.color='#1F6E38';">
                ← Back to Payment Options
            </a>
            
            <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 6px; font-size: 0.85rem; color: #6c757d;">
                <strong>Note:</strong> After completing the TWINT payment, please return to this page and click "I have completed the payment" to confirm your transaction.
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


</x-app-layout>
