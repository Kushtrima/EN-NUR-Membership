<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="2" />
    
    <div class="card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="icon-paypal" style="font-size: 2rem;"></span>
                <h1 class="card-title" style="margin: 0; color: #0070ba;">PayPal Payment</h1>
            </div>
            <p style="color: #6c757d;">Pay securely with your PayPal account</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- PayPal Login Section -->
            <div style="padding: 2rem; background: rgba(0, 112, 186, 0.05); border-radius: 8px; border-left: 4px solid #0070ba;">
                <h3 style="color: #0070ba; margin-bottom: 1rem;">Log in to PayPal</h3>
                <form id="paypal-demo-form" style="space-y: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Email or mobile number *</label>
                        <input type="email" id="paypal-email" value="{{ auth()->user()->email }}" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        <small style="color: #6c757d;">Your PayPal account email</small>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Password *</label>
                        <input type="password" id="paypal-password" placeholder="Enter your PayPal password" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                    </div>

                    <!-- Additional Payment Information -->
                    <div style="border-top: 1px solid #e9ecef; padding-top: 1rem; margin-top: 1.5rem;">
                        <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.95rem;">Payment Information</h4>
                        
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Full Name *</label>
                            <input type="text" id="paypal-name" value="{{ auth()->user()->name }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Address *</label>
                                <input type="text" id="paypal-address" placeholder="Street address" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Postal Code *</label>
                                <input type="text" id="paypal-postal" placeholder="1234" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">City *</label>
                                <input type="text" id="paypal-city" placeholder="Zurich" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Country *</label>
                                <select id="paypal-country" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                    <option value="CH" selected>Switzerland</option>
                                    <option value="DE">Germany</option>
                                    <option value="AT">Austria</option>
                                    <option value="FR">France</option>
                                    <option value="IT">Italy</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Phone Number</label>
                            <input type="tel" id="paypal-phone" placeholder="+41 12 345 67 89"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            <small style="color: #6c757d;">Optional - for account security</small>
                        </div>
                    </div>

                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem; margin-bottom: 1rem;">
                        <strong style="color: #0c5460;">🔒 Demo Mode:</strong> This is a demonstration. No real charges will be made.
                    </div>

                    <!-- Payment Methods -->
                    <div style="margin-top: 1.5rem;">
                        <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.9rem;">Choose your payment method:</h4>
                        
                        <div id="payment-method-balance" class="payment-method-option" style="border: 2px solid #0070ba; border-radius: 6px; padding: 1rem; margin-bottom: 0.5rem; background: rgba(0, 112, 186, 0.1); cursor: pointer; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <input type="radio" name="payment_method" value="balance" checked style="accent-color: #0070ba; cursor: pointer;">
                                <div>
                                    <div style="font-weight: 500; color: #0070ba;">PayPal Balance</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">CHF {{ number_format($payment->amount / 100, 2) }} available</div>
                                </div>
                            </div>
                        </div>

                        <div id="payment-method-visa" class="payment-method-option" style="border: 1px solid #ddd; border-radius: 6px; padding: 1rem; margin-bottom: 0.5rem; cursor: pointer; transition: all 0.3s ease; background: white;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <input type="radio" name="payment_method" value="visa" style="cursor: pointer;">
                                <div>
                                    <div style="font-weight: 500;">Visa ending in 1234</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">Expires 12/25</div>
                                </div>
                            </div>
                        </div>

                        <!-- Visa Card Details (Hidden by default) -->
                        <div id="visa-details" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem;">
                            <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.9rem;">💳 Enter Card Details</h4>
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Card Number *</label>
                                    <input type="text" id="visa-card-number" placeholder="1234 5678 9012 3456" maxlength="19" required
                                           style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                </div>
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">CVV *</label>
                                    <input type="text" id="visa-cvv" placeholder="123" maxlength="4" required
                                           style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Expiry Month *</label>
                                    <select id="visa-exp-month" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                        <option value="">Month</option>
                                        <option value="01">01 - January</option>
                                        <option value="02">02 - February</option>
                                        <option value="03">03 - March</option>
                                        <option value="04">04 - April</option>
                                        <option value="05">05 - May</option>
                                        <option value="06">06 - June</option>
                                        <option value="07">07 - July</option>
                                        <option value="08">08 - August</option>
                                        <option value="09">09 - September</option>
                                        <option value="10">10 - October</option>
                                        <option value="11">11 - November</option>
                                        <option value="12">12 - December</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Expiry Year *</label>
                                    <select id="visa-exp-year" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                        <option value="">Year</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                        <option value="2029">2029</option>
                                        <option value="2030">2030</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Cardholder Name *</label>
                                <input type="text" id="visa-cardholder-name" placeholder="John Doe" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem;">
                                <strong style="color: #155724;">🔒 Secure:</strong> Your card information is encrypted and secure.
                            </div>
                        </div>

                        <div id="payment-method-bank" class="payment-method-option" style="border: 1px solid #ddd; border-radius: 6px; padding: 1rem; cursor: pointer; transition: all 0.3s ease; background: white;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <input type="radio" name="payment_method" value="bank" style="cursor: pointer;">
                                <div>
                                    <div style="font-weight: 500;">Bank Account</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">Swiss Bank ••••5678</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Account Details (Hidden by default) -->
                        <div id="bank-details" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem;">
                            <h4 style="color: #495057; margin-bottom: 1rem; font-size: 0.9rem;">🏦 Bank Account Information</h4>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Bank Name *</label>
                                <select id="bank-name" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                    <option value="">Select your bank</option>
                                    <option value="ubs">UBS - Union Bank of Switzerland</option>
                                    <option value="credit-suisse">Credit Suisse</option>
                                    <option value="raiffeisen">Raiffeisen Bank</option>
                                    <option value="zuercher-kantonalbank">Zürcher Kantonalbank</option>
                                    <option value="postfinance">PostFinance</option>
                                    <option value="other">Other Swiss Bank</option>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Account Number *</label>
                                    <input type="text" id="bank-account-number" placeholder="CH93 0076 2011 6238 5295 7" required
                                           style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                </div>
                                <div>
                                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Sort Code *</label>
                                    <input type="text" id="bank-sort-code" placeholder="12345" maxlength="8" required
                                           style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Account Holder Name *</label>
                                <input type="text" id="bank-account-holder" placeholder="John Doe" required
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            </div>
                            <div style="background: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem;">
                                <strong style="color: #856404;">⚠️ Note:</strong> Bank transfers may take 1-3 business days to process.
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Payment Summary -->
            <div style="padding: 2rem; background: rgba(31, 110, 56, 0.05); border-radius: 8px; border-left: 4px solid #1F6E38;">
                <h3 style="color: #1F6E38; margin-bottom: 1rem;">Order Summary</h3>
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 50px; height: 50px; background: #1F6E38; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                            EN
                        </div>
                        <div>
                            <div style="font-weight: 500;">EN NUR Association</div>
                            <div style="font-size: 0.8rem; color: #6c757d;">{{ ucfirst($payment->payment_type) }} Payment</div>
                        </div>
                    </div>
                </div>

                <div style="space-y: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Subtotal:</span>
                        <span>{{ $payment->formatted_amount }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">PayPal Fee:</span>
                        <span style="color: #6c757d;">CHF {{ number_format(($payment->amount * 0.034 + 35) / 100, 2) }}</span>
                    </div>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="font-weight: bold; font-size: 1.1rem;">Total:</span>
                        <span style="font-weight: bold; font-size: 1.3rem; color: #1F6E38;">{{ $payment->formatted_amount }}</span>
                    </div>
                </div>

                <!-- PayPal Protection -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #0070ba; margin-bottom: 0.5rem; font-size: 0.9rem;">🛡️ PayPal Protection</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.8rem; color: #6c757d;">
                        <li>Buyer Protection Program</li>
                        <li>Secure encrypted transactions</li>
                        <li>24/7 fraud monitoring</li>
                        <li>Dispute resolution support</li>
                    </ul>
                </div>

                <!-- Shipping Address -->
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #495057; margin-bottom: 0.5rem; font-size: 0.9rem;">📍 Account Details</h4>
                    <div style="font-size: 0.8rem; color: #6c757d;">
                        <div>{{ auth()->user()->name }}</div>
                        <div>{{ auth()->user()->email }}</div>
                        <div>Switzerland</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Processing Simulation -->
        <div id="paypal-processing" style="display: none; text-align: center; padding: 2rem; background: rgba(0, 112, 186, 0.05); border-radius: 8px; margin-bottom: 2rem;">
            <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #0070ba; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem;"></div>
            <h3 style="color: #0070ba; margin-bottom: 0.5rem;">Processing PayPal Payment...</h3>
            <p style="color: #6c757d; margin: 0;">Please wait while we process your payment securely</p>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center;">
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button id="paypal-pay-btn" onclick="processPayPalPayment()" class="btn" style="background: #0070ba; color: white; padding: 1rem 2rem; font-size: 1.1rem; font-weight: bold;">
                    Continue with PayPal
                </button>
                <a href="{{ route('payment.create') }}" class="btn btn-secondary" style="padding: 1rem 2rem;">
                    ← Back to Payment Options
                </a>
            </div>
            <p style="font-size: 0.875rem; color: #6c757d; margin-top: 1rem; margin-bottom: 0;">
                By continuing, you agree to PayPal's User Agreement and Privacy Policy.
            </p>
        </div>
    </div>

    <style>
        .icon-paypal {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #0070ba;
            border-radius: 4px;
        }
        .icon-paypal::before { 
            content: 'P';
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 20px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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

        /* Payment method option styling */
        .payment-method-option:hover {
            box-shadow: 0 4px 12px rgba(0, 112, 186, 0.15) !important;
            transform: translateY(-2px);
        }

        .payment-method-option.selected {
            border: 2px solid #0070ba !important;
            background: rgba(0, 112, 186, 0.1) !important;
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
        function processPayPalPayment() {
            // Validate form first
            if (!validatePayPalForm()) {
                return;
            }

            const payButton = document.getElementById('paypal-pay-btn');
            const processingSection = document.getElementById('paypal-processing');
            
            // Show processing
            payButton.style.display = 'none';
            processingSection.style.display = 'block';
            
            // Simulate PayPal processing
            setTimeout(() => {
                // Redirect to success page
                window.location.href = '{{ route("payment.paypal.success", $payment) }}?demo=1';
            }, 3500);
        }

        function showNotification(message, type = 'error') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
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

        function validatePayPalForm() {
            const email = document.getElementById('paypal-email')?.value || '';
            const password = document.getElementById('paypal-password')?.value || '';
            const name = document.getElementById('paypal-name')?.value || '';
            const address = document.getElementById('paypal-address')?.value || '';
            const city = document.getElementById('paypal-city')?.value || '';
            const postal = document.getElementById('paypal-postal')?.value || '';
            
            // Get selected payment method
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'balance';

            // Basic validation
            if (!email || !email.includes('@')) {
                showNotification('Please enter a valid email address');
                return false;
            }

            if (!password || password.length < 6) {
                showNotification('Please enter your PayPal password (minimum 6 characters)');
                return false;
            }

            if (!name.trim()) {
                showNotification('Please enter your full name');
                return false;
            }

            if (!address.trim() || !city.trim() || !postal.trim()) {
                showNotification('Please fill in all address fields');
                return false;
            }

            // Validate payment method specific fields
            if (selectedMethod === 'visa') {
                const cardNumber = document.getElementById('visa-card-number')?.value || '';
                const cvv = document.getElementById('visa-cvv')?.value || '';
                const expMonth = document.getElementById('visa-exp-month')?.value || '';
                const expYear = document.getElementById('visa-exp-year')?.value || '';
                const cardholderName = document.getElementById('visa-cardholder-name')?.value || '';

                if (!cardNumber || cardNumber.replace(/\s/g, '').length < 13) {
                    showNotification('Please enter a valid card number');
                    return false;
                }

                if (!cvv || cvv.length < 3) {
                    showNotification('Please enter a valid CVV');
                    return false;
                }

                if (!expMonth || !expYear) {
                    showNotification('Please select card expiry date');
                    return false;
                }

                if (!cardholderName.trim()) {
                    showNotification('Please enter cardholder name');
                    return false;
                }
            } else if (selectedMethod === 'bank') {
                const bankName = document.getElementById('bank-name')?.value || '';
                const accountNumber = document.getElementById('bank-account-number')?.value || '';
                const sortCode = document.getElementById('bank-sort-code')?.value || '';
                const accountHolder = document.getElementById('bank-account-holder')?.value || '';

                if (!bankName) {
                    showNotification('Please select your bank');
                    return false;
                }

                if (!accountNumber || accountNumber.replace(/\s/g, '').length < 15) {
                    showNotification('Please enter a valid account number');
                    return false;
                }

                if (!sortCode || sortCode.length < 4) {
                    showNotification('Please enter a valid sort code');
                    return false;
                }

                if (!accountHolder.trim()) {
                    showNotification('Please enter account holder name');
                    return false;
                }
            }

            return true;
        }

        // Payment method selection functionality
        function selectPaymentMethod(method) {
            // Remove selection from all methods
            document.querySelectorAll('.payment-method-option').forEach(option => {
                option.classList.remove('selected');
                option.style.border = '1px solid #ddd';
                option.style.background = 'white';
                const title = option.querySelector('div div:first-child');
                if (title) {
                    title.style.color = '#495057';
                }
            });

            // Hide all detail sections
            document.getElementById('visa-details').style.display = 'none';
            document.getElementById('bank-details').style.display = 'none';

            // Highlight selected method
            const selectedOption = document.getElementById(`payment-method-${method}`);
            if (selectedOption) {
                selectedOption.classList.add('selected');
                selectedOption.style.border = '2px solid #0070ba';
                selectedOption.style.background = 'rgba(0, 112, 186, 0.1)';
                const title = selectedOption.querySelector('div div:first-child');
                if (title) {
                    title.style.color = '#0070ba';
                    title.style.fontWeight = '500';
                }
            }

            // Show relevant detail section
            if (method === 'visa') {
                document.getElementById('visa-details').style.display = 'block';
                // Focus on first input for better UX
                setTimeout(() => {
                    document.getElementById('visa-card-number').focus();
                }, 100);
            } else if (method === 'bank') {
                document.getElementById('bank-details').style.display = 'block';
                // Focus on first input for better UX
                setTimeout(() => {
                    document.getElementById('bank-name').focus();
                }, 100);
            }

            // Update button text based on selected method
            const payButton = document.getElementById('paypal-pay-btn');
            if (payButton) {
                switch(method) {
                    case 'balance':
                        payButton.innerHTML = '💰 Pay with PayPal Balance';
                        break;
                    case 'visa':
                        payButton.innerHTML = '💳 Pay with Visa Card';
                        break;
                    case 'bank':
                        payButton.innerHTML = '🏦 Pay with Bank Account';
                        break;
                    default:
                        payButton.innerHTML = 'Continue with PayPal';
                }
            }

            // Show selection feedback
            showNotification(`Selected: ${getPaymentMethodName(method)}`, 'success');
        }

        function getPaymentMethodName(method) {
            switch(method) {
                case 'balance': return 'PayPal Balance';
                case 'visa': return 'Visa ending in 1234';
                case 'bank': return 'Bank Account';
                default: return 'PayPal';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Payment method selection handlers
            document.querySelectorAll('.payment-method-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        selectPaymentMethod(radio.value);
                    }
                });
            });

            // Radio button change handlers
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        selectPaymentMethod(this.value);
                    }
                });
            });

            // Real-time validation feedback
            const emailInput = document.getElementById('paypal-email');
            const passwordInput = document.getElementById('paypal-password');

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    this.classList.remove('valid', 'invalid');
                    if (this.value && !this.value.includes('@')) {
                        this.classList.add('invalid');
                    } else if (this.value) {
                        this.classList.add('valid');
                    }
                });
            }

            if (passwordInput) {
                passwordInput.addEventListener('blur', function() {
                    this.classList.remove('valid', 'invalid');
                    if (this.value && this.value.length < 6) {
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

            // Phone number formatting
            const phoneInput = document.getElementById('paypal-phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.startsWith('41')) {
                        value = '+41 ' + value.substring(2).replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
                    } else if (value.length > 0) {
                        value = '+' + value;
                    }
                    e.target.value = value;
                });
            }

            // Card number formatting
            const cardNumberInput = document.getElementById('visa-card-number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                    e.target.value = value;
                    
                    // Basic card validation visual feedback
                    if (value.replace(/\s/g, '').length >= 13) {
                        e.target.style.borderColor = '#28a745';
                    } else {
                        e.target.style.borderColor = '#6c757d';
                    }
                });
            }

            // CVV formatting
            const cvvInput = document.getElementById('visa-cvv');
            if (cvvInput) {
                cvvInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }

            // Bank account number formatting (Swiss IBAN)
            const bankAccountInput = document.getElementById('bank-account-number');
            if (bankAccountInput) {
                bankAccountInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    value = value.replace(/(.{4})/g, '$1 ').trim();
                    e.target.value = value;
                    
                    // Swiss IBAN validation visual feedback
                    if (value.startsWith('CH') && value.replace(/\s/g, '').length >= 19) {
                        e.target.style.borderColor = '#28a745';
                    } else {
                        e.target.style.borderColor = '#6c757d';
                    }
                });
            }

            // Sort code formatting
            const sortCodeInput = document.getElementById('bank-sort-code');
            if (sortCodeInput) {
                sortCodeInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }

            // Initialize with default selection
            selectPaymentMethod('balance');
        });
    </script>
</x-app-layout> 