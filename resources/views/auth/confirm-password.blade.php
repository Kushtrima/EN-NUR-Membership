<x-app-layout>
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <h1 class="card-title text-center">Confirm Password</h1>
        
        <div class="mb-3" style="color: #6c757d; font-size: 0.875rem;">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
                @error('password')
                    <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn" style="width: 100%;">Confirm</button>
            </div>
        </form>
    </div>
</x-app-layout> 