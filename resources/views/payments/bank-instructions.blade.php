<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="2" />
    
    <div class="card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="icon-bank" style="font-size: 2rem;"></span>
                <h1 class="card-title" style="margin: 0; color: #1e3a8a;">Bank Transfer</h1>
            </div>
            <p style="color: #6c757d;">Complete your payment via secure bank transfer</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Customer Verification Form -->
            <div style="padding: 2rem; background: rgba(30, 58, 138, 0.05); border-radius: 8px; border-left: 4px solid #1e3a8a;">
                <h3 style="color: #1e3a8a; margin-bottom: 1rem;">Customer Verification</h3>
                <form id="bank-transfer-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">First Name *</label>
                            <input type="text" id="first-name" value="{{ explode(' ', auth()->user()->name)[0] ?? '' }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Last Name *</label>
                            <input type="text" id="last-name" value="{{ explode(' ', auth()->user()->name, 2)[1] ?? '' }}" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Email Address *</label>
                        <input type="email" id="email" value="{{ auth()->user()->email }}" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Account Holder Name *</label>
                        <input type="text" id="account-holder" placeholder="Name as it appears on your bank account" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                        <small style="color: #6c757d;">Must match the name on the bank transfer</small>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #495057;">Your Bank Name *</label>
                        <select id="bank-name" required style="width: 100%; padding: 0.75rem; border: 2px solid #28a745; border-radius: 6px; background: white; transition: all 0.3s ease;">
                            <option value="">Select your bank</option>
                            <option value="UBS">UBS - Union Bank of Switzerland</option>
                            <option value="Credit Suisse">Credit Suisse</option>
                            <option value="PostFinance">PostFinance</option>
                            <option value="Raiffeisen">Raiffeisen Bank</option>
                            <option value="Zürcher Kantonalbank">Zürcher Kantonalbank</option>
                            <option value="Basler Kantonalbank">Basler Kantonalbank</option>
                            <option value="Berner Kantonalbank">Berner Kantonalbank</option>
                            <option value="Julius Bär">Julius Bär</option>
                            <option value="Vontobel">Bank Vontobel</option>
                            <option value="Other">Other Swiss Bank</option>
                        </select>
                    </div>

                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 0.75rem; font-size: 0.875rem; margin-top: 1rem;">
                        <strong style="color: #0c5460;">🔒 Demo Mode:</strong> This is a demonstration. No real charges will be made.
                    </div>
                </form>
            </div>

            <!-- Payment Summary & Bank Details -->
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
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">Total:</span>
                        <span style="font-weight: bold; font-size: 1.3rem; color: #1F6E38;">{{ $payment->formatted_amount }}</span>
                    </div>
                </div>

                <!-- Bank Account Details -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #1e3a8a; margin-bottom: 0.5rem; font-size: 0.9rem;">🏦 Bank Account Details</h4>
                    <div style="font-size: 0.85rem; line-height: 1.4;">
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-weight: 500; color: #495057;">Account Holder:</span><br>
                            <span style="font-family: monospace;">EN NUR Association</span>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-weight: 500; color: #495057;">Bank:</span><br>
                            <span style="font-family: monospace;">Swiss National Bank</span>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-weight: 500; color: #495057;">IBAN:</span><br>
                            <span style="font-family: monospace; font-weight: bold; color: #1e3a8a;">CH93 0076 2011 6238 5295 7</span>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-weight: 500; color: #495057;">BIC/SWIFT:</span><br>
                            <span style="font-family: monospace;">POFICHBEXXX</span>
                        </div>
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 0.5rem; margin-top: 0.5rem;">
                            <span style="font-weight: 500; color: #856404;">Reference:</span><br>
                            <span style="font-family: monospace; font-weight: bold; color: #dc3545;">PAY-{{ $payment->id }}-{{ strtoupper(substr($payment->payment_type, 0, 3)) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Processing Timeline -->
                <div style="background: rgba(30, 58, 138, 0.05); border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; margin-top: 1rem;">
                    <h4 style="color: #1e3a8a; margin-bottom: 0.5rem; font-size: 0.9rem;">⏱️ Processing Timeline</h4>
                    <div style="font-size: 0.8rem; color: #6c757d;">
                        <div style="margin-bottom: 0.25rem;">📤 <strong>Day 0:</strong> You submit transfer</div>
                        <div style="margin-bottom: 0.25rem;">🔄 <strong>Day 1-2:</strong> Bank processing</div>
                        <div style="margin-bottom: 0.25rem;">✅ <strong>Day 3:</strong> Payment confirmed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="color: #1e3a8a; margin-bottom: 1rem;">📋 How to Complete Your Bank Transfer</h3>
            <ol style="margin: 0; padding-left: 1.5rem; color: #6c757d; line-height: 1.6;">
                <li style="margin-bottom: 0.5rem;">Log in to your online banking or visit your bank branch</li>
                <li style="margin-bottom: 0.5rem;">Create a new transfer using the bank details provided above</li>
                <li style="margin-bottom: 0.5rem;"><strong>Important:</strong> Include the reference number <code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 3px; color: #dc3545; font-weight: bold;">PAY-{{ $payment->id }}-{{ strtoupper(substr($payment->payment_type, 0, 3)) }}</code> in the transfer description</li>
                <li style="margin-bottom: 0.5rem;">Verify the amount is exactly <strong>{{ $payment->formatted_amount }}</strong></li>
                <li style="margin-bottom: 0.5rem;">Submit the transfer from your bank account</li>
                <li style="margin-bottom: 0.5rem;">Bank transfers typically take 1-3 business days to process</li>
                <li>You will receive a confirmation email once we verify your payment</li>
            </ol>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center;">
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                    <button onclick="processBankTransfer()" class="btn" style="background: #1e3a8a; color: white; padding: 1rem 2rem; font-size: 1.1rem; font-weight: bold; border-radius: 6px; border: 2px solid #1e3a8a; width: 100%; max-width: 400px;">
                    🏦 Bank Transfer
                </button>
                <a href="{{ route('payment.create') }}" class="btn btn-secondary" style="padding: 1rem 2rem;">
                    ← Back to Payment Options
                </a>
            </div>
        </div>
    </div>

    <style>
        .icon-bank {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #1e3a8a;
            border-radius: 6px;
        }
        .icon-bank::before { 
            content: '🏦';
            position: absolute;
            font-size: 20px;
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
        function processBankTransfer() {
            // Validate all required fields
            const firstName = document.getElementById('first-name').value.trim();
            const lastName = document.getElementById('last-name').value.trim();
            const email = document.getElementById('email').value.trim();
            const accountHolder = document.getElementById('account-holder').value.trim();
            const bankName = document.getElementById('bank-name').value.trim();

            // Check if all fields are filled
            if (!firstName) {
                showNotification('Please enter your first name', 'error');
                document.getElementById('first-name').focus();
                return;
            }

            if (!lastName) {
                showNotification('Please enter your last name', 'error');
                document.getElementById('last-name').focus();
                return;
            }

            if (!email || !email.includes('@')) {
                showNotification('Please enter a valid email address', 'error');
                document.getElementById('email').focus();
                return;
            }

            if (!accountHolder) {
                showNotification('Please enter the account holder name', 'error');
                document.getElementById('account-holder').focus();
                return;
            }

            if (!bankName) {
                showNotification('Please enter your bank name', 'error');
                document.getElementById('bank-name').focus();
                return;
            }

            // Show success message and redirect
            showNotification('Bank transfer details confirmed successfully!', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("payment.bank.success", $payment) }}?demo=1';
            }, 1500);
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
                max-width: 350px;
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

        // Real-time validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#28a745';
                        this.style.backgroundColor = 'rgba(40, 167, 69, 0.05)';
                    } else {
                        this.style.borderColor = '#dc3545';
                        this.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                    }
                });

                input.addEventListener('focus', function() {
                    this.style.borderColor = '#007bff';
                    this.style.backgroundColor = 'white';
                });
            });

            // Email validation
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    if (this.value && this.value.includes('@')) {
                        this.style.borderColor = '#28a745';
                        this.style.backgroundColor = 'rgba(40, 167, 69, 0.05)';
                    } else if (this.value) {
                        this.style.borderColor = '#dc3545';
                        this.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                    }
                });
            }
        });
    </script>
</x-app-layout>
