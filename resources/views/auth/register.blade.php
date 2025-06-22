<x-app-layout>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                        <path d="M15,14C12.33,14 7,15.33 7,18V20H23V18C23,15.33 17.67,14 15,14M6,10V7H4V10H1V12H4V15H6V12H9V10M15,12A4,4 0 0,0 19,8A4,4 0 0,0 15,4A4,4 0 0,0 11,8A4,4 0 0,0 15,12Z"/>
                    </svg>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join the EN NUR community</p>
            </div>
            
            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your full name">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="Create a password">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in here</a></p>
            </div>
        </div>
    </div>

    <style>
        .auth-container {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1F6E38, #C19A61);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1F6E38, #2d8f47);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1F6E38;
            margin-bottom: 0.25rem;
        }

        .auth-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
        }

        .auth-form {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #1F6E38;
            background: white;
            box-shadow: 0 0 0 3px rgba(31, 110, 56, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .auth-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1F6E38, #2d8f47);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 110, 56, 0.3);
        }

        .auth-footer {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .auth-footer p {
            color: #6c757d;
            margin: 0;
        }

        .auth-link {
            color: #1F6E38;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: #2d8f47;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .error-message::before {
            content: '⚠';
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .auth-container {
                padding: 1rem;
            }

            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-title {
                font-size: 1.75rem;
            }
        }
    </style>
</x-app-layout> 