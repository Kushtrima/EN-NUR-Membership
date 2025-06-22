<x-app-layout>
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <h1 class="card-title text-center">Forgot Password</h1>
        
        <div class="mb-3" style="color: #6c757d; font-size: 0.875rem;">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </div>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('login') }}" style="color: #007bff; text-decoration: none;">
                    Back to login
                </a>

                <button type="submit" class="btn">Email Password Reset Link</button>
            </div>
        </form>
    </div>
</x-app-layout> 