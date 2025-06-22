<x-app-layout>
    <!-- Payment Progress Steps -->
    <x-payment-steps :currentStep="1" />

    <div class="card">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <h1 class="card-title" style="margin-bottom: 0;">Make a Payment</h1>
            <div class="trust-badge">
                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
                <span style="font-size: 0.75rem; color: #1F6E38;">SSL Secured</span>
            </div>
        </div>
        <p>Choose from membership or donation options below. All payments are processed securely.</p>
    </div>

    @if(request('cancelled'))
        <div class="alert alert-info">
            Payment was cancelled. You can try again anytime.
        </div>
    @endif

    <div class="payment-grid">
        <!-- Membership Card -->
        <div class="card membership-card">

            <h2 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                    <path d="M5 16L3 6l5.5 4L12 4l3.5 6L21 6l-2 10H5zm2.7-2h8.6l.9-5.4-2.1 1.7L12 8l-3.1 2.3-2.1-1.7L7.7 14z"/>
                </svg>
                Membership
            </h2>
            
            <div class="membership-price-card">
                <div class="price-amount">
                    CHF {{ number_format($membershipAmount / 100, 2) }}
                </div>
                <div class="price-period">
                    Annual Membership (1 Year)
                </div>
            </div>
            
            <!-- Membership Benefits -->
            <div class="benefits-list">
                <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    What's Included:
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: #6c757d;">
                    <li style="margin-bottom: 0.25rem;">• Access to all mosque activities</li>
                    <li style="margin-bottom: 0.25rem;">• Member-only events and programs</li>
                    <li style="margin-bottom: 0.25rem;">• Community support and networking</li>
                    <li style="margin-bottom: 0.25rem;">• Prayer space priority access</li>
                </ul>
            </div>
            
            <div class="payment-methods">
                <h4 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: #333;">Choose Payment Method:</h4>
                
                <form method="POST" action="{{ route('payment.stripe') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="membership">
                    <input type="hidden" name="amount" value="{{ $membershipAmount }}">
                    <button type="submit" class="payment-btn stripe-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M492.4 220.8c-8.9 0-18.7 6.7-18.7 22.7h36.7c0-16-9.3-22.7-18-22.7zM375 223.4c-8.2 0-13.3 2.9-17 7l.2 52.8c3.5 3.7 8.5 6.7 16.8 6.7 13.1 0 21.9-14.3 21.9-33.4 0-18.6-9-33.2-21.9-33.1zM528 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM122.2 281.1c0 25.6-20.3 40.1-49.9 40.3-12.2 0-25.6-2.4-38.8-8.1v-33.9c12 6.4 27.1 11.3 38.9 11.3 7.9 0 13.6-2.1 13.6-8.7 0-17-54-10.6-54-49.9 0-25.2 19.2-40.2 48-40.2 11.8 0 23.5 1.8 35.3 6.5v33.4c-10.8-5.8-24.5-9.1-35.3-9.1-7.5 0-12.1 2.2-12.1 7.7 0 16 54.3 8.4 54.3 50.7zm68.8-56.6h-27V275c0 20.9 22.5 14.4 27 12.6v28.9c-4.7 2.6-13.3 4.7-24.9 4.7-21.1 0-36.9-15.5-36.9-36.5l.2-113.9 34.7-7.4v30.8H191zm74 2.4c-4.5-1.5-18.7-3.6-27.1 7.4v84.4h-35.5V194.2h30.7l2.2 10.5c8.3-15.3 24.9-12.2 29.6-10.5h.1zm44.1 91.8h-35.7V194.2h35.7zm0-142.9l-35.7 7.6v-28.9l35.7-7.6zm74.1 145.5c-12.4 0-20-5.3-25.1-9l-.1 40.2-35.5 7.5V194.2h31.3l1.8 8.8c4.9-4.5 13.9-11.1 27.8-11.1 24.9 0 48.4 22.5 48.4 63.8 0 45.1-23.2 65.5-48.6 65.6zm160.4-51.5h-69.5c1.6 16.6 13.8 21.5 27.6 21.5 14.1 0 25.2-3 34.9-7.9V312c-9.7 5.3-22.4 9.2-39.4 9.2-34.6 0-58.8-21.7-58.8-64.5 0-36.2 20.5-64.9 54.3-64.9 33.7 0 51.3 28.7 51.3 65.1 0 3.5-.3 10.9-.4 12.9z"/></svg>
                        <span class="payment-text">Pay with Stripe</span>
                        <span class="payment-cards">Cards • Apple Pay • Google Pay</span>
                    </button>
                </form>
                
                <form method="POST" action="{{ route('payment.paypal') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="membership">
                    <input type="hidden" name="amount" value="{{ $membershipAmount }}">
                    <button type="submit" class="payment-btn paypal-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M111.4 295.9c-3.5 19.2-17.4 108.7-21.5 134-.3 1.8-1 2.5-3 2.5H12.3c-7.6 0-13.1-6.6-12.1-13.9L58.8 46.6c1.5-9.6 10.1-16.9 20-16.9 152.3 0 165.1-3.7 204 11.4 60.1 23.3 65.6 79.5 44 140.3-21.5 62.6-72.5 89.5-140.1 90.3-43.4 .7-69.5-7-75.3 24.2zM357.1 152c-1.8-1.3-2.5-1.8-3 1.3-2 11.4-5.1 22.5-8.8 33.6-39.9 113.8-150.5 103.9-204.5 103.9-6.1 0-10.1 3.3-10.9 9.4-22.6 140.4-27.1 169.7-27.1 169.7-1 7.1 3.5 12.9 10.6 12.9h63.5c8.6 0 15.7-6.3 17.4-14.9 .7-5.4-1.1 6.1 14.4-91.3 4.6-22 14.3-19.7 29.3-19.7 71 0 126.4-28.8 142.9-112.3 6.5-34.8 4.6-71.4-23.8-92.6z"/></svg>
                        <span class="payment-text">Pay with PayPal</span>
                        <span class="payment-cards">PayPal Balance • Cards</span>
                    </button>
                </form>
                
                <form method="POST" action="{{ route('payment.twint') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="membership">
                    <input type="hidden" name="amount" value="{{ $membershipAmount }}">
                    <button type="submit" class="payment-btn twint-btn">
                        <svg class="payment-icon icon" viewBox="0 0 120 40" style="width: 1.8em; height: 1.1em;">
                            <!-- Official TWINT logo recreation -->
                            <g fill="currentColor">
                                <!-- T -->
                                <rect x="12" y="12" width="12" height="3"/>
                                <rect x="16.5" y="12" width="3" height="16"/>
                                <!-- W -->
                                <polygon points="30,12 33,12 35,22 37,12 40,12 42,22 44,12 47,12 43,28 40,28 38,18 36,28 33,28"/>
                                <!-- I -->
                                <rect x="52" y="12" width="3" height="16"/>
                                <!-- N -->
                                <rect x="60" y="12" width="3" height="16"/>
                                <polygon points="63,12 66,12 69,20 69,12 72,12 72,28 69,28 66,20 66,28 63,28"/>
                                <!-- T -->
                                <rect x="77" y="12" width="12" height="3"/>
                                <rect x="81.5" y="12" width="3" height="16"/>
                            </g>
                        </svg>
                        <span class="payment-text">Pay with TWINT</span>
                        <span class="payment-cards">Swiss Mobile Payment</span>
                    </button>
                </form>
                
                <form method="POST" action="{{ route('payment.bank') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="membership">
                    <input type="hidden" name="amount" value="{{ $membershipAmount }}">
                    <button type="submit" class="payment-btn bank-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M243.4 2.6l-224 96c-14 6-21.8 21-18.7 35.8S16.8 160 32 160l0 8c0 13.3 10.7 24 24 24l400 0c13.3 0 24-10.7 24-24l0-8c15.2 0 28.3-10.7 31.3-25.6s-4.8-29.9-18.7-35.8l-224-96c-8-3.4-17.2-3.4-25.2 0zM128 224l-64 0 0 196.3c-.6 .3-1.2 .7-1.8 1.1l-48 32c-11.7 7.8-17 22.4-12.9 35.9S17.9 512 32 512l448 0c14.1 0 26.5-9.2 30.6-22.7s-1.1-28.1-12.9-35.9l-48-32c-.6-.4-1.2-.7-1.8-1.1L448 224l-64 0 0 192-40 0 0-192-64 0 0 192-48 0 0-192-64 0 0 192-40 0 0-192zM256 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                        <span class="payment-text">Bank Transfer</span>
                        <span class="payment-cards">Direct Bank Payment</span>
                    </button>
                </form>
            </div>
            
            <div class="security-note">
                <small style="color: #6c757d; font-size: 0.75rem;">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    Your payment information is encrypted and secure. Processing time: 1-2 minutes
                </small>
            </div>
        </div>

        <!-- Donations Card -->
        <div class="card donations-card">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                Donations
            </h2>
            <p style="margin-bottom: 1.5rem; color: #6c757d;">
                Support our cause with a donation. Every contribution helps us achieve our mission.
            </p>
            
            <!-- Donation Impact -->
            <div class="donation-impact" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: #1F6E38;">
                    Your Impact:
                </h4>
                <div class="impact-list" style="font-size: 0.8rem; color: #6c757d;">
                    <div id="impact-text">CHF 50 - Supports weekly community activities</div>
                </div>
            </div>
            
            <!-- Donation Amount Selection -->
            <div class="donation-amounts">
                @foreach($donationAmounts as $index => $amount)
                    <div class="donation-card {{ $index === 0 ? 'selected' : '' }}" 
                         data-amount="{{ $amount }}"
                         onclick="selectDonation({{ $amount }}, this)">
                        <div class="donation-amount">CHF {{ number_format($amount / 100, 0) }}</div>
                        <div class="donation-frequency">One-time</div>
                    </div>
                @endforeach
            </div>

            <!-- Custom Amount -->
            <div class="custom-amount" style="margin: 1rem 0;">
                <label style="font-size: 0.9rem; color: #333; margin-bottom: 0.5rem; display: block;">
                    Or enter custom amount:
                </label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-weight: bold;">CHF</span>
                    <input type="number" id="custom-amount" min="10" max="10000" 
                           placeholder="100" 
                           style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"
                           onchange="selectCustomAmount(this.value)">
                </div>
            </div>

            <div class="payment-methods">
                <h4 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: #333;">Choose Payment Method:</h4>
                
                <form id="stripe-donation-form" method="POST" action="{{ route('payment.stripe') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="donation">
                    <input type="hidden" name="amount" id="stripe-donation-amount" value="{{ $donationAmounts[0] }}">
                    <button type="submit" class="payment-btn stripe-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M492.4 220.8c-8.9 0-18.7 6.7-18.7 22.7h36.7c0-16-9.3-22.7-18-22.7zM375 223.4c-8.2 0-13.3 2.9-17 7l.2 52.8c3.5 3.7 8.5 6.7 16.8 6.7 13.1 0 21.9-14.3 21.9-33.4 0-18.6-9-33.2-21.9-33.1zM528 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM122.2 281.1c0 25.6-20.3 40.1-49.9 40.3-12.2 0-25.6-2.4-38.8-8.1v-33.9c12 6.4 27.1 11.3 38.9 11.3 7.9 0 13.6-2.1 13.6-8.7 0-17-54-10.6-54-49.9 0-25.2 19.2-40.2 48-40.2 11.8 0 23.5 1.8 35.3 6.5v33.4c-10.8-5.8-24.5-9.1-35.3-9.1-7.5 0-12.1 2.2-12.1 7.7 0 16 54.3 8.4 54.3 50.7zm68.8-56.6h-27V275c0 20.9 22.5 14.4 27 12.6v28.9c-4.7 2.6-13.3 4.7-24.9 4.7-21.1 0-36.9-15.5-36.9-36.5l.2-113.9 34.7-7.4v30.8H191zm74 2.4c-4.5-1.5-18.7-3.6-27.1 7.4v84.4h-35.5V194.2h30.7l2.2 10.5c8.3-15.3 24.9-12.2 29.6-10.5h.1zm44.1 91.8h-35.7V194.2h35.7zm0-142.9l-35.7 7.6v-28.9l35.7-7.6zm74.1 145.5c-12.4 0-20-5.3-25.1-9l-.1 40.2-35.5 7.5V194.2h31.3l1.8 8.8c4.9-4.5 13.9-11.1 27.8-11.1 24.9 0 48.4 22.5 48.4 63.8 0 45.1-23.2 65.5-48.6 65.6zm160.4-51.5h-69.5c1.6 16.6 13.8 21.5 27.6 21.5 14.1 0 25.2-3 34.9-7.9V312c-9.7 5.3-22.4 9.2-39.4 9.2-34.6 0-58.8-21.7-58.8-64.5 0-36.2 20.5-64.9 54.3-64.9 33.7 0 51.3 28.7 51.3 65.1 0 3.5-.3 10.9-.4 12.9z"/></svg>
                        <span class="payment-text">Donate with Stripe</span>
                        <span class="payment-cards">Cards • Apple Pay • Google Pay</span>
                    </button>
                </form>
                
                <form id="paypal-donation-form" method="POST" action="{{ route('payment.paypal') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="donation">
                    <input type="hidden" name="amount" id="paypal-donation-amount" value="{{ $donationAmounts[0] }}">
                    <button type="submit" class="payment-btn paypal-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M111.4 295.9c-3.5 19.2-17.4 108.7-21.5 134-.3 1.8-1 2.5-3 2.5H12.3c-7.6 0-13.1-6.6-12.1-13.9L58.8 46.6c1.5-9.6 10.1-16.9 20-16.9 152.3 0 165.1-3.7 204 11.4 60.1 23.3 65.6 79.5 44 140.3-21.5 62.6-72.5 89.5-140.1 90.3-43.4 .7-69.5-7-75.3 24.2zM357.1 152c-1.8-1.3-2.5-1.8-3 1.3-2 11.4-5.1 22.5-8.8 33.6-39.9 113.8-150.5 103.9-204.5 103.9-6.1 0-10.1 3.3-10.9 9.4-22.6 140.4-27.1 169.7-27.1 169.7-1 7.1 3.5 12.9 10.6 12.9h63.5c8.6 0 15.7-6.3 17.4-14.9 .7-5.4-1.1 6.1 14.4-91.3 4.6-22 14.3-19.7 29.3-19.7 71 0 126.4-28.8 142.9-112.3 6.5-34.8 4.6-71.4-23.8-92.6z"/></svg>
                        <span class="payment-text">Donate with PayPal</span>
                        <span class="payment-cards">PayPal Balance • Cards</span>
                    </button>
                </form>
                
                <form id="twint-donation-form" method="POST" action="{{ route('payment.twint') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="donation">
                    <input type="hidden" name="amount" id="twint-donation-amount" value="{{ $donationAmounts[0] }}">
                    <button type="submit" class="payment-btn twint-btn">
                        <svg class="payment-icon icon" viewBox="0 0 120 40" style="width: 1.8em; height: 1.1em;">
                            <!-- Official TWINT logo recreation -->
                            <g fill="currentColor">
                                <!-- T -->
                                <rect x="12" y="12" width="12" height="3"/>
                                <rect x="16.5" y="12" width="3" height="16"/>
                                <!-- W -->
                                <polygon points="30,12 33,12 35,22 37,12 40,12 42,22 44,12 47,12 43,28 40,28 38,18 36,28 33,28"/>
                                <!-- I -->
                                <rect x="52" y="12" width="3" height="16"/>
                                <!-- N -->
                                <rect x="60" y="12" width="3" height="16"/>
                                <polygon points="63,12 66,12 69,20 69,12 72,12 72,28 69,28 66,20 66,28 63,28"/>
                                <!-- T -->
                                <rect x="77" y="12" width="12" height="3"/>
                                <rect x="81.5" y="12" width="3" height="16"/>
                            </g>
                        </svg>
                        <span class="payment-text">Donate with TWINT</span>
                        <span class="payment-cards">Swiss Mobile Payment</span>
                    </button>
                </form>
                
                <form id="bank-donation-form" method="POST" action="{{ route('payment.bank') }}" class="payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="donation">
                    <input type="hidden" name="amount" id="bank-donation-amount" value="{{ $donationAmounts[0] }}">
                    <button type="submit" class="payment-btn bank-btn">
                        <svg class="payment-icon icon" fill="white" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M243.4 2.6l-224 96c-14 6-21.8 21-18.7 35.8S16.8 160 32 160l0 8c0 13.3 10.7 24 24 24l400 0c13.3 0 24-10.7 24-24l0-8c15.2 0 28.3-10.7 31.3-25.6s-4.8-29.9-18.7-35.8l-224-96c-8-3.4-17.2-3.4-25.2 0zM128 224l-64 0 0 196.3c-.6 .3-1.2 .7-1.8 1.1l-48 32c-11.7 7.8-17 22.4-12.9 35.9S17.9 512 32 512l448 0c14.1 0 26.5-9.2 30.6-22.7s-1.1-28.1-12.9-35.9l-48-32c-.6-.4-1.2-.7-1.8-1.1L448 224l-64 0 0 192-40 0 0-192-64 0 0 192-48 0 0-192-64 0 0 192-40 0 0-192zM256 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                        <span class="payment-text">Donate via Bank Transfer</span>
                        <span class="payment-cards">Direct Bank Payment</span>
                    </button>
                </form>
            </div>
            
            <div class="selected-amount-display">
                Selected amount: <span id="selected-amount">CHF {{ number_format($donationAmounts[0] / 100, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="card faq-section">
        <h3 style="margin-bottom: 1rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
            </svg>
            Frequently Asked Questions
        </h3>
        <div class="faq-grid">
            <div class="faq-item">
                <strong>Is my payment secure?</strong>
                <p>Yes, all payments are processed through encrypted SSL connections with industry-standard security.</p>
            </div>
            <div class="faq-item">
                <strong>Can I get a refund?</strong>
                <p>Membership fees are non-refundable after 7 days. Donations are final but tax-deductible.</p>
            </div>
            <div class="faq-item">
                <strong>How long does processing take?</strong>
                <p>Stripe payments: 1-2 minutes. TWINT: Instant. Bank transfers: 1-3 business days.</p>
            </div>
            <div class="faq-item">
                <strong>Will I receive a receipt?</strong>
                <p>Yes, you'll receive an email receipt immediately after successful payment.</p>
            </div>
            <div class="faq-item">
                <strong>Which payment methods do you accept?</strong>
                <p>We accept credit/debit cards (Stripe), TWINT (Swiss mobile payment), and direct bank transfers.</p>
            </div>
        </div>
    </div>

    <style>
        /* SVG Icon Styles */
        .icon {
            width: 1em;
            height: 1em;
            display: inline-block;
            vertical-align: middle;
            fill: currentColor;
        }
        
        .payment-icon {
            width: 1.2em;
            height: 1.2em;
            margin-right: 0.5rem;
            fill: white !important;
        }
        
        .payment-btn svg.payment-icon {
            fill: white !important;
            opacity: 1 !important;
            visibility: visible !important;
            color: white !important;
        }
        
        .payment-btn:hover svg.payment-icon {
            fill: white !important;
            color: white !important;
        }
        
        /* Force colored icons on payment buttons to match text */
        .stripe-btn svg.payment-icon {
            fill: #1F6E38 !important;
            color: #1F6E38 !important;
        }
        
        .paypal-btn svg.payment-icon {
            fill: #0070ba !important;
            color: #0070ba !important;
        }
        
        .twint-btn svg.payment-icon {
            fill: #FF6B35 !important;
            color: #FF6B35 !important;
        }
        
        .bank-btn svg.payment-icon {
            fill: #6c757d !important;
            color: #6c757d !important;
        }
        
        /* White icons on hover */
        .stripe-btn:hover svg.payment-icon,
        .paypal-btn:hover svg.payment-icon,
        .twint-btn:hover svg.payment-icon,
        .bank-btn:hover svg.payment-icon {
            fill: white !important;
            color: white !important;
        }
        
        /* Icon Styles - Pure CSS Icons */
        .icon-lock {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
        }
        .icon-lock::before { 
            content: '';
            position: absolute;
            width: 10px;
            height: 8px;
            background: #1F6E38;
            border-radius: 2px;
            top: 6px;
            left: 2px;
        }
        .icon-lock::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 4px;
            width: 6px;
            height: 6px;
            border: 2px solid #1F6E38;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            background: transparent;
        }
        
        .icon-star {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
        }
        .icon-star::before { 
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-bottom: 5px solid #FFD700;
            top: 0;
            left: 0;
        }
        .icon-star::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-top: 5px solid #FFD700;
            bottom: 0;
            left: 0;
        }
        
        .icon-building {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
        }
        .icon-building::before { 
            content: '';
            position: absolute;
            width: 12px;
            height: 10px;
            border: 2px solid #1F6E38;
            border-radius: 2px;
            top: 2px;
            left: 1px;
        }
        .icon-building::after {
            content: '';
            position: absolute;
            width: 4px;
            height: 6px;
            background: #1F6E38;
            top: 5px;
            left: 5px;
        }
        
        .icon-check {
            position: relative;
            display: inline-block;
            width: 16px;
            height: 16px;
            background: rgba(31, 110, 56, 0.1);
            border-radius: 50%;
        }
        .icon-check::before { 
            content: '';
            position: absolute;
            width: 3px;
            height: 6px;
            border: solid #1F6E38;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            top: 3px;
            left: 6px;
        }
        
        .icon-card {
            position: relative;
            display: inline-block;
            width: 18px;
            height: 12px;
        }
        .icon-card::before { 
            content: '';
            position: absolute;
            width: 16px;
            height: 10px;
            background: #1F6E38;
            border-radius: 2px;
            top: 1px;
            left: 1px;
        }
        .icon-card::after {
            content: '';
            position: absolute;
            top: 4px;
            left: 3px;
            width: 12px;
            height: 2px;
            background: white;
            border-radius: 1px;
        }
        
        .icon-paypal {
            position: relative;
            display: inline-block;
            width: 16px;
            height: 16px;
            background: #0070ba;
            border-radius: 2px;
        }
        .icon-paypal::before { 
            content: 'P';
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .icon-heart {
            position: relative;
            display: inline-block;
            width: 18px;
            height: 16px;
        }
        .icon-heart::before {
            content: '';
            position: absolute;
            width: 10px;
            height: 8px;
            background: #dc3545;
            border-radius: 5px 5px 0 0;
            transform: rotate(-45deg);
            top: 2px;
            left: 4px;
        }
        .icon-heart::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 8px;
            background: #dc3545;
            border-radius: 5px 5px 0 0;
            transform: rotate(45deg);
            top: 2px;
            left: 4px;
        }
        
        .icon-gift {
            position: relative;
            display: inline-block;
            width: 16px;
            height: 16px;
            background: rgba(193, 154, 97, 0.1);
            border-radius: 3px;
        }
        .icon-gift::before { 
            content: '';
            position: absolute;
            width: 12px;
            height: 8px;
            border: 2px solid #C19A61;
            border-radius: 2px;
            top: 6px;
            left: 2px;
        }
        .icon-gift::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 14px;
            background: #C19A61;
            top: 1px;
            left: 7px;
        }
        
        .icon-help {
            position: relative;
            display: inline-block;
            width: 18px;
            height: 18px;
            background: #1F6E38;
            border-radius: 50%;
        }
        .icon-help::before { 
            content: '?';
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .icon-twint {
            position: relative;
            display: inline-block;
            width: 18px;
            height: 18px;
            background: #FF6B35;
            border-radius: 3px;
        }
        .icon-twint::before { 
            content: 'T';
            position: absolute;
            color: white;
            font-weight: bold;
            font-size: 14px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .icon-bank {
            position: relative;
            display: inline-block;
            width: 18px;
            height: 16px;
        }
        .icon-bank::before { 
            content: '';
            position: absolute;
            width: 16px;
            height: 10px;
            border: 2px solid #2c3e50;
            border-radius: 2px;
            top: 4px;
            left: 1px;
        }
        .icon-bank::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 9px solid transparent;
            border-right: 9px solid transparent;
            border-bottom: 4px solid #2c3e50;
            top: 0;
            left: 0;
        }

        /* Progress Bar Styles */
        .progress-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #1F6E38;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: #1F6E38;
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .progress-step.active .step-label {
            color: #1F6E38;
            font-weight: bold;
        }

        .progress-line {
            height: 2px;
            background: #e9ecef;
            flex: 1;
            margin: 0 1rem;
            position: relative;
            top: -20px;
        }

        /* Enhanced Card Styles */
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .membership-card {
            position: relative;
            border: 2px solid #1F6E38;
            background: white;
        }

        .recommended-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #C19A61;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .membership-price-card {
            background: white;
            border: 2px solid #1F6E38;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin: 1.5rem 0;
            box-shadow: 0 4px 15px rgba(31, 110, 56, 0.1);
        }

        .price-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #1F6E38;
            margin-bottom: 0.5rem;
        }

        .price-period {
            color: #1F6E38;
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .benefits-list {
            background: rgba(31, 110, 56, 0.05);
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            border-left: 4px solid #1F6E38;
        }

        /* Donation Styles */
        .donation-amounts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .donation-card {
            text-align: center;
            padding: 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .donation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(193,154,97,0.1), transparent);
            transition: left 0.5s;
        }

        .donation-card:hover::before {
            left: 100%;
        }

        .donation-card:hover {
            border-color: #C19A61;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(193, 154, 97, 0.2);
        }

        .donation-card.selected {
            border-color: #C19A61;
            background: #C19A61;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(193, 154, 97, 0.4);
        }

        .donation-amount {
            font-weight: bold;
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
        }

        .donation-frequency {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Payment Button Styles */
        .payment-methods {
            margin: 1.5rem 0;
        }

        .payment-form {
            margin-bottom: 0.75rem;
        }

        .payment-btn {
            width: 100%;
            padding: 1rem;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            background: white;
        }

        .stripe-btn {
            border-color: #1F6E38;
            color: #1F6E38;
        }

        .stripe-btn:hover {
            background: #1F6E38;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(31, 110, 56, 0.3);
        }

        .paypal-btn {
            border-color: #0070ba;
            color: #0070ba;
        }

        .paypal-btn:hover {
            background: #0070ba;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 112, 186, 0.3);
        }

        .twint-btn {
            border-color: #FF6B35;
            color: #FF6B35;
        }

        .twint-btn:hover {
            background: #FF6B35;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .bank-btn {
            border-color: #2c3e50;
            color: #2c3e50;
        }

        .bank-btn:hover {
            background: #2c3e50;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
        }

        .payment-icon {
            font-size: 1.5rem;
        }

        .payment-text {
            flex: 1;
            margin-left: 1rem;
        }

        .payment-cards {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .selected-amount-display {
            text-align: center;
            font-size: 1.1rem;
            color: #1F6E38;
            font-weight: bold;
            padding: 1rem;
            background: rgba(31, 110, 56, 0.1);
            border-radius: 8px;
            margin-top: 1rem;
        }

        .security-note {
            margin-top: 1rem;
            padding: 0.75rem;
            background: rgba(31, 110, 56, 0.05);
            border-radius: 6px;
            text-align: center;
        }

        /* FAQ Styles */
        .faq-section {
            margin-top: 2rem;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .faq-item {
            padding: 1rem;
            background: rgba(31, 110, 56, 0.05);
            border-radius: 8px;
            border-left: 4px solid #C19A61;
        }

        .faq-item strong {
            color: #1F6E38;
            display: block;
            margin-bottom: 0.5rem;
        }

        .faq-item p {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }

            .donation-amounts {
                grid-template-columns: 1fr;
            }

            .progress-bar {
                flex-direction: column;
                gap: 1rem;
            }

            .progress-line {
                display: none;
            }

            .payment-btn {
                padding: 1.25rem;
                font-size: 1.1rem;
            }

            .price-amount {
                font-size: 2.5rem;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading States */
        .payment-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .payment-btn.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Trust Elements */
        .trust-badge {
            background: rgba(31, 110, 56, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            border: 1px solid #1F6E38;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>

    <script>
        // Impact messages for different donation amounts
        const impactMessages = {
            5000: "CHF 50 - Supports weekly community activities",
            10000: "CHF 100 - Funds educational programs for a month",
            20000: "CHF 200 - Provides meals for community events",
            50000: "CHF 500 - Supports major facility improvements"
        };

        function selectDonation(amount, element, updateProgress = true) {
            // Update hidden inputs
            document.getElementById('stripe-donation-amount').value = amount;
            document.getElementById('paypal-donation-amount').value = amount;
            document.getElementById('twint-donation-amount').value = amount;
            document.getElementById('bank-donation-amount').value = amount;
            
            // Update selected amount display
            document.getElementById('selected-amount').textContent = 'CHF ' + (amount / 100).toFixed(0);
            
            // Update impact message
            const impactText = document.getElementById('impact-text');
            impactText.textContent = impactMessages[amount] || `CHF ${amount/100} - Every contribution makes a difference`;
            
            // Reset all donation cards
            document.querySelectorAll('.donation-card').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Select current card
            element.classList.add('selected');
            
            // Clear custom amount input
            document.getElementById('custom-amount').value = '';
            
            // Progress indication - only update if user manually selected
            if (updateProgress) {
                updateProgressStep(2);
            }
        }

        function selectCustomAmount(value) {
            // Clean and validate input
            const cleanValue = parseFloat(value);
            
            if (!cleanValue || isNaN(cleanValue) || cleanValue < 10 || cleanValue > 10000) {
                // Reset to default if invalid
                const firstCard = document.querySelector('.donation-card');
                if (firstCard) {
                    selectDonation({{ $donationAmounts[0] }}, firstCard, false);
                }
                return;
            }
            
            const amount = Math.round(cleanValue * 100);
            
            // Update hidden inputs
            const stripeAmountEl = document.getElementById('stripe-donation-amount');
            const paypalAmountEl = document.getElementById('paypal-donation-amount');
            const twintAmountEl = document.getElementById('twint-donation-amount');
            const bankAmountEl = document.getElementById('bank-donation-amount');
            const selectedAmountEl = document.getElementById('selected-amount');
            const impactTextEl = document.getElementById('impact-text');
            
            if (stripeAmountEl) stripeAmountEl.value = amount;
            if (paypalAmountEl) paypalAmountEl.value = amount;
            if (twintAmountEl) twintAmountEl.value = amount;
            if (bankAmountEl) bankAmountEl.value = amount;
            
            // Update selected amount display
            if (selectedAmountEl) selectedAmountEl.textContent = 'CHF ' + cleanValue.toFixed(0);
            
            // Update impact message
            if (impactTextEl) impactTextEl.textContent = `CHF ${cleanValue.toFixed(0)} - Your custom contribution makes a difference`;
            
            // Deselect preset cards
            document.querySelectorAll('.donation-card').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Progress indication
            updateProgressStep(2);
        }

        function updateProgressStep(step) {
            document.querySelectorAll('.progress-step').forEach((el, index) => {
                if (index < step) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }

        // Add loading states to payment buttons (but don't prevent submission)
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Don't prevent form submission - just add visual feedback
                console.log('Payment button clicked:', this.querySelector('.payment-text').textContent);
                
                // First click on membership buttons should go to step 2, then step 3
                const membershipCard = this.closest('.membership-card');
                if (membershipCard) {
                    updateProgressStep(2);
                    // Small delay before going to step 3
                    setTimeout(() => {
                        updateProgressStep(3);
                    }, 100);
                } else {
                    // Donation buttons go directly to step 3
                    updateProgressStep(3);
                }
                
                // Add loading state but don't disable the button immediately
                // This allows the form to submit first
                setTimeout(() => {
                    this.classList.add('loading');
                    this.disabled = true;
                }, 100);
                
                // Re-enable after 10 seconds (fallback in case redirect fails)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.disabled = false;
                }, 10000);
            });
        });
        
        // Initialize first donation amount
        document.addEventListener('DOMContentLoaded', function() {
            const firstCard = document.querySelector('.donation-card');
            if (firstCard) {
                selectDonation({{ $donationAmounts[0] }}, firstCard, false);
            }

            // Add form submission debugging
            const forms = document.querySelectorAll('.payment-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    
                    // Show a loading message
                    const button = form.querySelector('.payment-btn');
                    const originalText = button.querySelector('.payment-text').textContent;
                    button.querySelector('.payment-text').textContent = 'Processing...';
                    
                    // If form submission fails, restore original text after 3 seconds
                    setTimeout(() => {
                        if (button.querySelector('.payment-text').textContent === 'Processing...') {
                            button.querySelector('.payment-text').textContent = originalText;
                            button.classList.remove('loading');
                            button.disabled = false;
                        }
                    }, 3000);
                });
            });
        });

        // Tooltip functionality
        function showTooltip(element, text) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 0.5rem;
                border-radius: 4px;
                font-size: 0.8rem;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            
            setTimeout(() => tooltip.remove(), 3000);
        }
    </script>
</x-app-layout> 