<x-app-layout>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h1 class="card-title text-center">Verifikoni Adresën e Email-it</h1>
        
        <div class="mb-3" style="color: #333; font-size: 16px; line-height: 1.6;">
            <p><strong>Faleminderit që u bashkuat me EN NUR - ANËTARËSIA!</strong> 🎉</p>
            
            <p>Për të aktivizuar profilin tuaj dhe për të hyrë në të gjitha funksionet, ju lutemi ndiqni këto hapa të thjeshtë:</p>
            
            <ol style="margin-left: 20px; margin-top: 15px;">
                <li><strong>Shkoni në kutinë tuaj të email-it</strong> ({{ Auth::user()->email }})</li>
                <li><strong>Kërkoni email-in tonë</strong> me temën "Verifikoni Adresën e Email-it"</li>
                <li><strong>Klikoni butonin "Verifikoni Adresën e Email-it"</strong> në email</li>
                <li><strong>Profili juaj do të aktivizohet</strong> dhe mund të filloni të përdorni të gjitha funksionet!</li>
            </ol>
            
            <p style="margin-top: 15px;"><em>Nuk e shihni email-in? Kontrolloni dosjen spam/junk ose klikoni "Ridërgo" më poshtë.</em></p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <strong>✅ Email-i u Dërgua me Sukses!</strong><br>
                Një email verifikimi i ri është dërguar në <strong>{{ Auth::user()->email }}</strong>. 
                Ju lutemi kontrolloni kutinë tuaj të email-it dhe klikoni butonin "Verifikoni Adresën e Email-it" për të aktivizuar profilin tuaj.
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn">Ridërgo Email-in e Verifikimit</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Dilni</button>
            </form>
        </div>
    </div>
</x-app-layout> 