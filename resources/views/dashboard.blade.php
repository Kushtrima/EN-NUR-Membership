<x-app-layout>
    <div class="card">
        <h1 class="card-title">Paneli im</h1>
        <p>Mirë se u kthyet, <strong>{{ auth()->user()->name }}</strong>!</p>
        
        @if(!auth()->user()->hasVerifiedEmail())
            <div class="alert alert-info">
                Adresa juaj e email-it nuk është verifikuar. Ju lutemi kontrolloni email-in tuaj për një lidhje verifikimi.
                <form method="POST" action="{{ route('verification.send') }}" style="display: inline; margin-left: 1rem;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Ridërgo Email-in e Verifikimit</button>
                </form>
            </div>
        @endif
    </div>

    <!-- Community Statistics -->
    <div class="card">
        <h2 class="card-title">Përmbledhje e Komunitetit</h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem;">
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Gjithsej Anëtarë</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['total_users'] }}
                </div>
                <small style="color: #666;">Anëtarë të regjistruar</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Anëtarë Aktivë</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    {{ $stats['membership_payments'] }}
                </div>
                <small style="color: #666;">Anëtarësi CHF 350</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Të Ardhurat Totale</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    CHF {{ number_format($stats['total_revenue'], 2) }}
                </div>
                <small style="color: #666;">Të gjitha pagesat e përfunduara</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Dhurimet Totale</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    CHF {{ number_format($stats['total_donations'], 2) }}
                </div>
                <small style="color: #666;">Kontributet e dhurimit</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Anëtarë të Rinj</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['recent_registrations'] }}
                </div>
                <small style="color: #666;">30 ditët e fundit</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Në Pritje</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #dc3545;">
                    {{ $stats['pending_payments'] }}
                </div>
                <small style="color: #666;">Në pritje të pagesës</small>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 class="card-title">Historiku im i Pagesave</h2>
        @if(auth()->user()->payments->count() > 0)
            @php
                $totalPaid = auth()->user()->payments->where('status', 'completed')->sum('amount') / 100;
                $totalDonations = auth()->user()->payments->where('payment_type', 'donation')->where('status', 'completed')->sum('amount') / 100;
            @endphp
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($totalPaid, 2) }}</div>
                    <small style="color: white;">Gjithsej të Paguara</small>
                </div>
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($totalDonations, 2) }}</div>
                    <small style="color: white;">Dhurime</small>
                </div>
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ auth()->user()->payments->where('status', 'completed')->count() }}</div>
                    <small style="color: white;">Pagesa</small>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Lloji</th>
                            <th>Shuma</th>
                            <th>Statusi</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->payments->sortByDesc('created_at')->take(5) as $payment)
                            <tr>
                                <td>
                                    @if($payment->payment_type === 'membership')
                                        <span style="color: #C19A61; font-weight: bold;">Anëtarësi</span>
                                    @else
                                        <span style="color: #1F6E38;">Dhurim</span>
                                    @endif
                                </td>
                                <td style="font-weight: bold;">{{ $payment->formatted_amount }}</td>
                                <td>
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; 
                                          background-color: {{ $payment->status === 'completed' ? '#d4f4d4' : '#fff3cd' }};
                                          color: {{ $payment->status === 'completed' ? '#1F6E38' : '#856404' }};">
                                        @if($payment->status === 'completed')
                                            E përfunduar
                                        @elseif($payment->status === 'pending')
                                            Në pritje
                                        @else
                                            {{ ucfirst($payment->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span>{{ $payment->created_at->format('M d, Y') }}</span>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('user.payments.receipt', $payment) }}" 
                                               style="background: #1F6E38; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;"
                                               title="Shkarko Faturën">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                                </svg>
                                                PDF
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(auth()->user()->payments->count() > 5)
                <p style="text-align: center; color: #666; margin-top: 1rem;">
                    Po shfaqen 5 pagesat më të fundit nga {{ auth()->user()->payments->count() }} gjithsej
                </p>
            @endif
        @else
            <div style="text-align: center; padding: 2rem; color: #666;">
                <p>Nuk keni kryer ende asnjë pagesë.</p>
                <p>Filloni me anëtarësinë tuaj vjetore për CHF 350</p>
            </div>
        @endif
    </div>
</x-app-layout> 