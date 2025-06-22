<x-app-layout>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">EN NUR - MEMBERSHIP</h1>
            <p class="hero-subtitle">Join our community and support our mission through membership and donations</p>
            
            @guest
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                </div>
            @else
                <div class="hero-actions">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('payment.create') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-credit-card"></i>
                        Make Payment
                    </a>
                </div>
            @endguest
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Xhamia EN NUR</h2>
                <p class="section-subtitle">EN NUR Mosque is a vibrant Islamic community center dedicated to serving our local Muslim community. We provide spiritual guidance, educational programs, and community support while fostering unity and Islamic values. Join us in building a stronger, more connected community.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M16 4C16.88 4 17.67 4.5 18 5.26L20 9H4L6 5.26C6.33 4.5 7.12 4 8 4H16M16 2H8C6.62 2 5.33 2.73 4.67 3.93L2 9V20C2 21.11 2.89 22 4 22H20C21.11 22 22 21.11 22 20V9L19.33 3.93C18.67 2.73 17.38 2 16 2M9 11V13H15V11H9M9 15V17H13V15H9Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Community Membership</h3>
                    <p class="feature-description">Join our vibrant community with a one-time membership fee of CHF 350 and gain access to exclusive benefits.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M12,21.35L10.55,20.03C5.4,15.36 2,12.27 2,8.5C2,5.41 4.42,3 7.5,3C9.24,3 10.91,3.81 12,5.08C13.09,3.81 14.76,3 16.5,3C19.58,3 22,5.41 22,8.5C22,12.27 18.6,15.36 13.45,20.03L12,21.35Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Support Our Cause</h3>
                    <p class="feature-description">Make a difference with flexible donation options of CHF 50, 100, 200, or 500 to support our mission.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10V11C15.4,11 16,11.4 16,12V16C16,16.6 15.6,17 15,17H9C8.4,17 8,16.6 8,16V12C8,11.4 8.4,11 9,11V10C9,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.2,9.2 10.2,10V11H13.8V10C13.8,9.2 12.8,8.2 12,8.2Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Secure Payments</h3>
                    <p class="feature-description">Safe and secure payment processing through trusted providers like Stripe and PayPal.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4V6L13.5 7.5C13.1 7.9 12.6 8.1 12 8.1S10.9 7.9 10.5 7.5L9 6V4L3 7V9L9 12L15 9M9 12L3 15V17L9 20L12 18.5V16.5L9 18L5 16L9 14L12 15.5V13.5L9 12Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Account Management</h3>
                    <p class="feature-description">Complete user registration, profile management, and payment history tracking system.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6M12,8A4,4 0 0,0 8,12A4,4 0 0,0 12,16A4,4 0 0,0 16,12A4,4 0 0,0 12,8Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Prayer Times</h3>
                    <p class="feature-description">Stay connected with accurate daily prayer times and Islamic calendar events to maintain your spiritual routine.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Islamic Education</h3>
                    <p class="feature-description">Access comprehensive Islamic education programs, Quran classes, and religious studies for all age groups.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #1F6E38 0%, #2d8f47 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(31, 110, 56, 0.3);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1F6E38;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: all 0.3s ease;
            border-top: 4px solid #C19A61;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1F6E38, #2d8f47);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        
        .feature-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1F6E38;
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: #6c757d;
            line-height: 1.6;
        }
        
        /* Button Styles */
        .btn-lg {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .btn-xl {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-outline:hover {
            background: white;
            color: #1F6E38;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .hero-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-actions .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-app-layout> 