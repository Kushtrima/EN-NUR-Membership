<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="2" />
    
    <div class="card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="icon-card" style="font-size: 2rem;"></span>
                <h1 class="card-title" style="margin: 0; color: #0070ba;">Stripe Payment</h1>
            </div>
            <p style="color: #6c757d;">Cards • Apple Pay • Google Pay • Secure Processing</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Payment Form -->
            <div style="padding: 2rem; background: rgba(0, 112, 186, 0.05); border-radius: 8px; border-left: 4px solid #0070ba;">
                <h3 style="color: #0070ba; margin-bottom: 1rem;">Payment Methods Available</h3>
                
                <!-- Payment Method Options -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <div class="payment-method-badge active" onclick="selectPaymentMethod('card')" data-method="card" style="padding: 0.5rem 1rem; background: #0070ba; color: white; border-radius: 6px; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.3rem;">
                        <svg width="16" height="10" viewBox="0 0 576 512" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 32C28.7 32 0 60.7 0 96l0 32 576 0 0-32c0-35.3-28.7-64-64-64L64 32zM576 224L0 224 0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-192zM112 352l64 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-64 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm112 16c0-8.8 7.2-16 16-16l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16z"/></svg>
                        Credit/Debit Card
                    </div>
                    <div class="payment-method-badge" onclick="selectPaymentMethod('apple_pay')" data-method="apple_pay" style="padding: 0.75rem 1.5rem; background: #f8f9fa; color: #6c757d; border: 2px solid #0070ba; border-radius: 6px; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg width="36" height="26" viewBox="0 0 576 512" fill="currentColor">
                            <path d="M302.2 218.4c0 17.2-10.5 27.1-29 27.1h-24.3v-54.2h24.4c18.4 0 28.9 9.8 28.9 27.1zm47.5 62.6c0 8.3 7.2 13.7 18.5 13.7 14.4 0 25.2-9.1 25.2-21.9v-7.7l-23.5 1.5c-13.3 .9-20.2 5.8-20.2 14.4zM576 79v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V79c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM127.8 197.2c8.4 .7 16.8-4.2 22.1-10.4 5.2-6.4 8.6-15 7.7-23.7-7.4 .3-16.6 4.9-21.9 11.3-4.8 5.5-8.9 14.4-7.9 22.8zm60.6 74.5c-.2-.2-19.6-7.6-19.8-30-.2-18.7 15.3-27.7 16-28.2-8.8-13-22.4-14.4-27.1-14.7-12.2-.7-22.6 6.9-28.4 6.9-5.9 0-14.7-6.6-24.3-6.4-12.5 .2-24.2 7.3-30.5 18.6-13.1 22.6-3.4 56 9.3 74.4 6.2 9.1 13.7 19.1 23.5 18.7 9.3-.4 13-6 24.2-6 11.3 0 14.5 6 24.3 5.9 10.2-.2 16.5-9.1 22.8-18.2 6.9-10.4 9.8-20.4 10-21zm135.4-53.4c0-26.6-18.5-44.8-44.9-44.8h-51.2v136.4h21.2v-46.6h29.3c26.8 0 45.6-18.4 45.6-45zm90 23.7c0-19.7-15.8-32.4-40-32.4-22.5 0-39.1 12.9-39.7 30.5h19.1c1.6-8.4 9.4-13.9 20-13.9 13 0 20.2 6 20.2 17.2v7.5l-26.4 1.6c-24.6 1.5-37.9 11.6-37.9 29.1 0 17.7 13.7 29.4 33.4 29.4 13.3 0 25.6-6.7 31.2-17.4h.4V310h19.6v-68zM516 210.9h-21.5l-24.9 80.6h-.4l-24.9-80.6H422l35.9 99.3-1.9 6c-3.2 10.2-8.5 14.2-17.9 14.2-1.7 0-4.9-.2-6.2-.3v16.4c1.2 .4 6.5 .5 8.1 .5 20.7 0 30.4-7.9 38.9-31.8L516 210.9z"/>
                        </svg>
                    </div>
                    <div class="payment-method-badge" onclick="selectPaymentMethod('google_pay')" data-method="google_pay" style="padding: 0.75rem 1.5rem; background: #f8f9fa; color: #6c757d; border: 2px solid #0070ba; border-radius: 6px; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg width="40" height="26" viewBox="0 0 640 512" fill="currentColor">
                            <path d="M105.7 215v41.3h57.1a49.7 49.7 0 0 1 -21.1 32.6c-9.5 6.6-21.7 10.3-36 10.3-27.6 0-50.9-18.9-59.3-44.2a65.6 65.6 0 0 1 0-41l0 0c8.4-25.5 31.7-44.4 59.3-44.4a56.4 56.4 0 0 1 40.5 16.1L176.5 155a101.2 101.2 0 0 0 -70.8-27.8 105.6 105.6 0 0 0 -94.4 59.1 107.6 107.6 0 0 0 0 96.2v.2a105.4 105.4 0 0 0 94.4 59c28.5 0 52.6-9.5 70-25.9 20-18.6 31.4-46.2 31.4-78.9A133.8 133.8 0 0 0 205.4 215zm389.4-4c-10.1-9.4-23.9-14.1-41.4-14.1-22.5 0-39.3 8.3-50.5 24.9l20.9 13.3q11.5-17 31.3-17a34.1 34.1 0 0 1 22.8 8.8A28.1 28.1 0 0 1 487.8 248v5.5c-9.1-5.1-20.6-7.8-34.6-7.8-16.4 0-29.7 3.9-39.5 11.8s-14.8 18.3-14.8 31.6a39.7 39.7 0 0 0 13.9 31.3c9.3 8.3 21 12.5 34.8 12.5 16.3 0 29.2-7.3 39-21.9h1v17.7h22.6V250C510.3 233.5 505.3 220.3 495.1 211zM475.9 300.3a37.3 37.3 0 0 1 -26.6 11.2A28.6 28.6 0 0 1 431 305.2a19.4 19.4 0 0 1 -7.8-15.6c0-7 3.2-12.8 9.5-17.4s14.5-7 24.1-7C470 265 480.3 268 487.6 273.9 487.6 284.1 483.7 292.9 475.9 300.3zm-93.7-142A55.7 55.7 0 0 0 341.7 142H279.1V328.7H302.7V253.1h39c16 0 29.5-5.4 40.5-15.9 .9-.9 1.8-1.8 2.7-2.7A54.5 54.5 0 0 0 382.3 158.3zm-16.6 62.2a30.7 30.7 0 0 1 -23.3 9.7H302.7V165h39.6a32 32 0 0 1 22.6 9.2A33.2 33.2 0 0 1 365.7 220.5zM614.3 201 577.8 292.7h-.5L539.9 201H514.2L566 320.6l-29.4 64.3H561L640 201z"/>
                        </svg>
                    </div>
                </div>
                
                <h4 style="color: #0070ba; margin-bottom: 1rem; font-size: 1rem;">Card Details</h4>
                <form id="stripe-demo-form" style="space-y: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Card Number *</label>
                        <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; font-family: monospace; background: white; transition: all 0.3s ease;">
                        <small style="color: #6c757d;">Enter your 16-digit card number</small>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Expiry Date *</label>
                            <input type="text" id="expiry-date" placeholder="MM/YY" maxlength="5" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; font-family: monospace; background: white; transition: all 0.3s ease;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">CVC *</label>
                            <input type="text" id="cvc" placeholder="123" maxlength="4" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; font-family: monospace; background: white; transition: all 0.3s ease;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Cardholder Name *</label>
                        <input type="text" id="cardholder-name" value="{{ auth()->user()->name }}" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                    </div>

                    <!-- Billing Address -->
                    <div style="border-top: 1px solid #e9ecef; padding-top: 1rem; margin-top: 1.5rem;">
                        <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.95rem;">Billing Address</h4>
                        
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Email Address *</label>
                            <input type="email" id="email" value="{{ auth()->user()->email }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Address *</label>
                                <input type="text" id="address" placeholder="Street address" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Postal Code *</label>
                                <input type="text" id="postal-code" placeholder="1234" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">City *</label>
                                <input type="text" id="city" placeholder="Zurich" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Country *</label>
                                <select id="country" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                    <option value="CH" selected>Switzerland</option>
                                    <option value="DE">Germany</option>
                                    <option value="AT">Austria</option>
                                    <option value="FR">France</option>
                                    <option value="IT">Italy</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem; margin-bottom: 1rem;">
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
                        <span style="font-weight: 500;">Processing Fee:</span>
                        <span style="color: #6c757d;">CHF {{ number_format(($payment->amount * 0.029 + 30) / 100, 2) }}</span>
                    </div>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">Total:</span>
                        <span style="font-weight: bold; font-size: 1.3rem; color: #1F6E38;">{{ $payment->formatted_amount }}</span>
                    </div>
                </div>

                <!-- Security Features -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #0070ba; margin-bottom: 0.5rem; font-size: 0.9rem;">🔐 Secure Payment</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.8rem; color: #6c757d;">
                        <li>256-bit SSL encryption</li>
                        <li>PCI DSS compliant</li>
                        <li>3D Secure authentication</li>
                        <li>Fraud protection included</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Processing Simulation -->
        <div id="processing-section" style="display: none; text-align: center; padding: 2rem; background: rgba(0, 112, 186, 0.05); border-radius: 8px; margin-bottom: 2rem;">
            <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #0070ba; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem;"></div>
            <h3 style="color: #0070ba; margin-bottom: 0.5rem;">Processing Payment...</h3>
            <p style="color: #6c757d; margin: 0;">Please wait while we securely process your payment</p>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center;">
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button id="pay-now-btn" onclick="processPayment()" class="btn" style="background: #0070ba; color: white; padding: 1rem 2rem; font-size: 1.1rem; font-weight: bold;">
                    <span id="pay-button-text">💳 Pay {{ $payment->formatted_amount }}</span>
                </button>
                <a href="{{ route('payment.create') }}" class="btn btn-secondary" style="padding: 1rem 2rem;">
                    ← Back to Payment Options
                </a>
            </div>
            <p style="font-size: 0.875rem; color: #6c757d; margin-top: 1rem; margin-bottom: 0;">
                By clicking "Pay Now", you agree to our terms and conditions.
            </p>
        </div>
    </div>

    <style>
        .icon-card {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 24px;
        }
        .icon-card::before { 
            content: '';
            position: absolute;
            width: 28px;
            height: 20px;
            background: #0070ba;
            border-radius: 4px;
            top: 2px;
            left: 2px;
        }
        .icon-card::after {
            content: '';
            position: absolute;
            top: 8px;
            left: 6px;
            width: 20px;
            height: 3px;
            background: white;
            border-radius: 1px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Input Styling */
        input:focus, select:focus {
            outline: none !important;
            border: 3px solid #28a745 !important;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2) !important;
            transform: scale(1.02);
        }

        input.invalid, select.invalid {
            border: 3px solid #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.05) !important;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1) !important;
        }

        input.valid, select.valid {
            border: 2px solid #28a745 !important;
            background-color: rgba(40, 167, 69, 0.05) !important;
        }

        input:hover, select:hover {
            border-color: #20c997 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            div[style*="grid-template-columns: 1fr 1fr"] {
                display: block !important;
            }
            
            div[style*="grid-template-columns: 1fr 1fr"] > div {
                margin-bottom: 1rem;
            }
        }
    </style>

    <script>
        let selectedPaymentMethod = 'card';
        
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            // Update button badges
            document.querySelectorAll('.payment-method-badge').forEach(badge => {
                badge.classList.remove('active');
                badge.style.background = '#f8f9fa';
                badge.style.color = '#6c757d';
                badge.style.border = '2px solid #0070ba';
            });
            
            const selectedBadge = document.querySelector(`[data-method="${method}"]`);
            selectedBadge.classList.add('active');
            selectedBadge.style.background = '#0070ba';
            selectedBadge.style.color = 'white';
            selectedBadge.style.border = '2px solid #0070ba';
            
            // Update pay button text
            const payButtonText = document.getElementById('pay-button-text');
            const amount = '{{ $payment->formatted_amount }}';
            
            switch(method) {
                case 'apple_pay':
                    payButtonText.innerHTML = `
                        <svg width="36" height="26" viewBox="0 0 576 512" fill="white" style="margin-right: 0.5rem;">
                            <path d="M302.2 218.4c0 17.2-10.5 27.1-29 27.1h-24.3v-54.2h24.4c18.4 0 28.9 9.8 28.9 27.1zm47.5 62.6c0 8.3 7.2 13.7 18.5 13.7 14.4 0 25.2-9.1 25.2-21.9v-7.7l-23.5 1.5c-13.3 .9-20.2 5.8-20.2 14.4zM576 79v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V79c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM127.8 197.2c8.4 .7 16.8-4.2 22.1-10.4 5.2-6.4 8.6-15 7.7-23.7-7.4 .3-16.6 4.9-21.9 11.3-4.8 5.5-8.9 14.4-7.9 22.8zm60.6 74.5c-.2-.2-19.6-7.6-19.8-30-.2-18.7 15.3-27.7 16-28.2-8.8-13-22.4-14.4-27.1-14.7-12.2-.7-22.6 6.9-28.4 6.9-5.9 0-14.7-6.6-24.3-6.4-12.5 .2-24.2 7.3-30.5 18.6-13.1 22.6-3.4 56 9.3 74.4 6.2 9.1 13.7 19.1 23.5 18.7 9.3-.4 13-6 24.2-6 11.3 0 14.5 6 24.3 5.9 10.2-.2 16.5-9.1 22.8-18.2 6.9-10.4 9.8-20.4 10-21zm135.4-53.4c0-26.6-18.5-44.8-44.9-44.8h-51.2v136.4h21.2v-46.6h29.3c26.8 0 45.6-18.4 45.6-45zm90 23.7c0-19.7-15.8-32.4-40-32.4-22.5 0-39.1 12.9-39.7 30.5h19.1c1.6-8.4 9.4-13.9 20-13.9 13 0 20.2 6 20.2 17.2v7.5l-26.4 1.6c-24.6 1.5-37.9 11.6-37.9 29.1 0 17.7 13.7 29.4 33.4 29.4 13.3 0 25.6-6.7 31.2-17.4h.4V310h19.6v-68zM516 210.9h-21.5l-24.9 80.6h-.4l-24.9-80.6H422l35.9 99.3-1.9 6c-3.2 10.2-8.5 14.2-17.9 14.2-1.7 0-4.9-.2-6.2-.3v16.4c1.2 .4 6.5 .5 8.1 .5 20.7 0 30.4-7.9 38.9-31.8L516 210.9z"/>
                        </svg>
                        Pay ${amount}`;
                    break;
                case 'google_pay':
                    payButtonText.innerHTML = `
                        <svg width="40" height="26" viewBox="0 0 640 512" fill="white" style="margin-right: 0.5rem;">
                            <path d="M105.7 215v41.3h57.1a49.7 49.7 0 0 1 -21.1 32.6c-9.5 6.6-21.7 10.3-36 10.3-27.6 0-50.9-18.9-59.3-44.2a65.6 65.6 0 0 1 0-41l0 0c8.4-25.5 31.7-44.4 59.3-44.4a56.4 56.4 0 0 1 40.5 16.1L176.5 155a101.2 101.2 0 0 0 -70.8-27.8 105.6 105.6 0 0 0 -94.4 59.1 107.6 107.6 0 0 0 0 96.2v.2a105.4 105.4 0 0 0 94.4 59c28.5 0 52.6-9.5 70-25.9 20-18.6 31.4-46.2 31.4-78.9A133.8 133.8 0 0 0 205.4 215zm389.4-4c-10.1-9.4-23.9-14.1-41.4-14.1-22.5 0-39.3 8.3-50.5 24.9l20.9 13.3q11.5-17 31.3-17a34.1 34.1 0 0 1 22.8 8.8A28.1 28.1 0 0 1 487.8 248v5.5c-9.1-5.1-20.6-7.8-34.6-7.8-16.4 0-29.7 3.9-39.5 11.8s-14.8 18.3-14.8 31.6a39.7 39.7 0 0 0 13.9 31.3c9.3 8.3 21 12.5 34.8 12.5 16.3 0 29.2-7.3 39-21.9h1v17.7h22.6V250C510.3 233.5 505.3 220.3 495.1 211zM475.9 300.3a37.3 37.3 0 0 1 -26.6 11.2A28.6 28.6 0 0 1 431 305.2a19.4 19.4 0 0 1 -7.8-15.6c0-7 3.2-12.8 9.5-17.4s14.5-7 24.1-7C470 265 480.3 268 487.6 273.9 487.6 284.1 483.7 292.9 475.9 300.3zm-93.7-142A55.7 55.7 0 0 0 341.7 142H279.1V328.7H302.7V253.1h39c16 0 29.5-5.4 40.5-15.9 .9-.9 1.8-1.8 2.7-2.7A54.5 54.5 0 0 0 382.3 158.3zm-16.6 62.2a30.7 30.7 0 0 1 -23.3 9.7H302.7V165h39.6a32 32 0 0 1 22.6 9.2A33.2 33.2 0 0 1 365.7 220.5zM614.3 201 577.8 292.7h-.5L539.9 201H514.2L566 320.6l-29.4 64.3H561L640 201z"/>
                        </svg>
                        Pay ${amount}`;
                    break;
                default:
                    payButtonText.innerHTML = `
                        <svg width="20" height="14" viewBox="0 0 576 512" fill="white" style="display: inline-block; margin-right: 0.5rem;"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 32C28.7 32 0 60.7 0 96l0 32 576 0 0-32c0-35.3-28.7-64-64-64L64 32zM576 224L0 224 0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-192zM112 352l64 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-64 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm112 16c0-8.8 7.2-16 16-16l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16z"/></svg>
                        Pay ${amount}`;
            }
            
            // Show/hide card form based on method
            const cardForm = document.getElementById('stripe-demo-form');
            if (method === 'apple_pay' || method === 'google_pay') {
                cardForm.style.opacity = '0.5';
                cardForm.style.pointerEvents = 'none';
                
                // Add info message
                let infoMessage = document.getElementById('wallet-info');
                if (!infoMessage) {
                    infoMessage = document.createElement('div');
                    infoMessage.id = 'wallet-info';
                    infoMessage.style.cssText = `
                        background: #d4edda;
                        border: 1px solid #c3e6cb;
                        color: #155724;
                        padding: 1rem;
                        border-radius: 6px;
                        margin-bottom: 1rem;
                        font-size: 0.9rem;
                    `;
                    cardForm.parentNode.insertBefore(infoMessage, cardForm);
                }
                
                if (method === 'apple_pay') {
                    infoMessage.innerHTML = `
                        <strong>🍎 Apple Pay Selected</strong><br>
                        In production, you would authenticate with Face ID/Touch ID on your Apple device. 
                        No card details needed - your payment info is securely stored in your Apple Wallet.
                    `;
                } else {
                    infoMessage.innerHTML = `
                        <strong>🟢 Google Pay Selected</strong><br>
                        In production, you would authenticate with your Google account. 
                        No card details needed - your payment info is securely stored in Google Pay.
                    `;
                }
            } else {
                cardForm.style.opacity = '1';
                cardForm.style.pointerEvents = 'auto';
                
                // Remove info message
                const infoMessage = document.getElementById('wallet-info');
                if (infoMessage) {
                    infoMessage.remove();
                }
            }
        }
        
        function processPayment() {
            // Validate form first
            if (!validateForm()) {
                return;
            }

            const payButton = document.getElementById('pay-now-btn');
            const processingSection = document.getElementById('processing-section');
            
            // Show processing
            payButton.style.display = 'none';
            processingSection.style.display = 'block';
            
            // Simulate payment processing
            setTimeout(() => {
                // Redirect to success page
                window.location.href = '{{ route("payment.stripe.success", $payment) }}?demo=1';
            }, 3000);
        }

        function showNotification(message, type = 'error') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 6px;
                color: white;
                font-weight: bold;
                z-index: 10000;
                max-width: 300px;
                background: ${type === 'error' ? '#dc3545' : '#28a745'};
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
                style.remove();
            }, 4000);
        }

        function validateForm() {
            // For wallet payments, skip card validation
            if (selectedPaymentMethod === 'apple_pay' || selectedPaymentMethod === 'google_pay') {
                showNotification(`${selectedPaymentMethod === 'apple_pay' ? 'Apple Pay' : 'Google Pay'} payment initiated!`, 'success');
                return true;
            }
            
            // Card payment validation
            const cardNumber = document.getElementById('card-number')?.value.replace(/\s/g, '') || '';
            const expiryDate = document.getElementById('expiry-date')?.value || '';
            const cvc = document.getElementById('cvc')?.value || '';
            const cardholderName = document.getElementById('cardholder-name')?.value || '';
            const email = document.getElementById('email')?.value || '';
            const address = document.getElementById('address')?.value || '';
            const city = document.getElementById('city')?.value || '';
            const postalCode = document.getElementById('postal-code')?.value || '';

            // Basic validation
            if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                showNotification('Please enter a valid 16-digit card number');
                return false;
            }

            if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                showNotification('Please enter expiry date in MM/YY format');
                return false;
            }

            if (cvc.length < 3 || !/^\d+$/.test(cvc)) {
                showNotification('Please enter a valid CVC code');
                return false;
            }

            if (!cardholderName.trim()) {
                showNotification('Please enter the cardholder name');
                return false;
            }

            if (!email.includes('@')) {
                showNotification('Please enter a valid email address');
                return false;
            }

            if (!address.trim() || !city.trim() || !postalCode.trim()) {
                showNotification('Please fill in all address fields');
                return false;
            }

            return true;
        }

        // Card number formatting and validation
        document.addEventListener('DOMContentLoaded', function() {
            // Card number formatting
            const cardNumberInput = document.getElementById('card-number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formattedValue;
                });
            }

            // Expiry date formatting
            const expiryInput = document.getElementById('expiry-date');
            if (expiryInput) {
                expiryInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }

            // CVC validation
            const cvcInput = document.getElementById('cvc');
            if (cvcInput) {
                cvcInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                });
            }

            // Real-time validation feedback
            if (cardNumberInput) {
                cardNumberInput.addEventListener('blur', function() {
                    const value = this.value.replace(/\s/g, '');
                    this.classList.remove('valid', 'invalid');
                    if (value.length > 0 && (value.length !== 16 || !/^\d+$/.test(value))) {
                        this.classList.add('invalid');
                    } else if (value.length > 0) {
                        this.classList.add('valid');
                    }
                });
            }

            if (expiryInput) {
                expiryInput.addEventListener('blur', function() {
                    this.classList.remove('valid', 'invalid');
                    if (this.value && !/^\d{2}\/\d{2}$/.test(this.value)) {
                        this.classList.add('invalid');
                    } else if (this.value) {
                        this.classList.add('valid');
                    }
                });
            }

            if (cvcInput) {
                cvcInput.addEventListener('blur', function() {
                    this.classList.remove('valid', 'invalid');
                    if (this.value && (this.value.length < 3 || !/^\d+$/.test(this.value))) {
                        this.classList.add('invalid');
                    } else if (this.value) {
                        this.classList.add('valid');
                    }
                });
            }

            // Add validation for all other fields
            const allInputs = document.querySelectorAll('input, select');
            allInputs.forEach(input => {
                if (input.hasAttribute('required')) {
                    input.addEventListener('blur', function() {
                        this.classList.remove('valid', 'invalid');
                        if (!this.value.trim()) {
                            this.classList.add('invalid');
                        } else {
                            this.classList.add('valid');
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout> 