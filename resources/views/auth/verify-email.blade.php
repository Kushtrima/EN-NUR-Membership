<x-app-layout>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h1 class="card-title text-center">Verify Email Address</h1>
        
        <div class="mb-3" style="color: #333; font-size: 16px; line-height: 1.6;">
            <p><strong>Thank you for joining EN NUR - MEMBERSHIP!</strong> 🎉</p>
            
            <p>To activate your profile and access all features, please follow these simple steps:</p>
            
            <ol style="margin-left: 20px; margin-top: 15px;">
                <li><strong>Go to your email inbox</strong> ({{ Auth::user()->email }})</li>
                <li><strong>Look for our email</strong> with the subject "Verify Email Address"</li>
                <li><strong>Click the "Verify Email Address" button</strong> in the email</li>
                <li><strong>Your profile will be activated</strong> and you can start using all features!</li>
            </ol>
            
            <p style="margin-top: 15px;"><em>Don't see the email? Check your spam/junk folder or click "Resend" below.</em></p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <strong>✅ Email Sent Successfully!</strong><br>
                A new verification email has been sent to <strong>{{ Auth::user()->email }}</strong>. 
                Please check your inbox and click the "Verify Email Address" button to activate your profile.
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Log Out</button>
            </form>
        </div>
    </div>
</x-app-layout> 