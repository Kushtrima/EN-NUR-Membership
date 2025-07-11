<x-app-layout>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                    </svg>
                </div>
                <h1 class="auth-title">Mirë se erdhe përsëri</h1>

            </div>
            
            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="login" class="form-label">Email ose Username</label>
                    <input id="login" class="form-control" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Shkruaj email-in ose username-in tënd">

                    @error('login')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Fjalëkalimi</label>
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Shkruaj fjalëkalimin tënd">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" class="checkbox">
                        <span class="checkmark"></span>
                        <span>Më mbaj mend</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Harrove fjalëkalimin?
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-btn">Kyçu</button>
            </form>

            <div class="auth-footer">
                <p>Nuk ke llogari? <a href="{{ route('register') }}" class="auth-link">Regjistrohu këtu</a></p>
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

        .checkbox-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
            color: #495057;
        }

        .checkbox {
            margin-right: 0.5rem;
            transform: scale(1.1);
        }

        .forgot-link {
            color: #1F6E38;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #2d8f47;
        }

        .auth-btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #1F6E38, #2d8f47);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.75rem;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 110, 56, 0.3);
        }

        .auth-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .auth-footer p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
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
            font-size: 0.8rem;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
        }

        .error-message::before {
            content: '⚠';
            margin-right: 0.4rem;
        }

        @media (max-width: 768px) {
            .auth-container {
                padding: 1rem;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .auth-title {
                font-size: 1.5rem;
            }
            
            .auth-icon {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</x-app-layout> 