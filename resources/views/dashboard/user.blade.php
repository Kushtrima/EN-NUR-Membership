<x-app-layout>
    <div class="card">
        <h1 class="card-title">
            Paneli im
        </h1>
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

    <!-- Membership Status -->
    <div class="card">
        <h2 class="card-title">Statusi i Anëtarësisë</h2>
        
        @if($userStats['has_membership'] && $userStats['active_membership_renewal'])
            @php
                $renewal = $userStats['active_membership_renewal'];
                $daysLeft = $renewal->calculateDaysUntilExpiry();
                $membershipStart = $renewal->membership_start_date;
                $membershipEnd = $renewal->membership_end_date;
                
                // Determine status and colors
                $isExpired = $daysLeft <= 0;
                $isExpiringSoon = $daysLeft > 0 && $daysLeft <= 30;
                $isActive = $daysLeft > 30;
                
                if ($isExpired) {
                    $statusColor = '#dc3545'; // Red
                    $statusText = 'Anëtarësia ka Skaduar';
                    $statusIcon = '❌';
                    $bgColor = 'rgba(220, 53, 69, 0.1)';
                } elseif ($isExpiringSoon) {
                    $statusColor = '#ff6c37'; // Orange
                    $statusText = 'Anëtarësia Skadon së Shpejti';
                    $statusIcon = '⚠️';
                    $bgColor = 'rgba(255, 108, 55, 0.1)';
                } else {
                    $statusColor = '#1F6E38'; // Green
                    $statusText = 'Anëtar Aktiv';
                    $statusIcon = '✓';
                    $bgColor = 'rgba(31, 110, 56, 0.1)';
                }
            @endphp
            
            <div style="background: {{ $bgColor }}; border-radius: 8px; padding: 1.5rem; border-left: 4px solid {{ $statusColor }};">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: {{ $statusColor }}; color: white; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        {{ $statusIcon }}
                    </div>
                    <div>
                        <h3 style="margin: 0; color: {{ $statusColor }};">{{ $statusText }}</h3>
                        @if($isExpired)
                            <p style="margin: 0; color: #666;">Anëtarësia juaj ka skaduar. Ju lutemi rinovoni për të vazhduar me shërbimet.</p>
                        @elseif($isExpiringSoon)
                            <p style="margin: 0; color: #666;">Anëtarësia juaj skadon së shpejti. Konsideroni rinovimin për të shmangur ndërprerjen.</p>
                        @else
                            <p style="margin: 0; color: #666;">Anëtarësia juaj është aktive dhe e vlefshme</p>
                        @endif
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Anëtar që nga:</strong><br>
                        <span style="color: {{ $statusColor }};">{{ $membershipStart->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <strong>E vlefshme deri më:</strong><br>
                        <span style="color: {{ $statusColor }};">{{ $membershipEnd->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <strong>Ditë të mbetura:</strong><br>
                        <span style="color: {{ $statusColor }};">
                            {{ $daysLeft > 0 ? $daysLeft . ' ditë' : 'KA SKADUAR (' . abs($daysLeft) . ' ditë më parë)' }}
                        </span>
                    </div>
                </div>
                
                @if($isExpired)
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8d7da; border-radius: 4px; border-left: 4px solid #dc3545;">
                        <strong>🚨 URGJENT: Anëtarësia ka Skaduar</strong><br>
                        Anëtarësia juaj ka skaduar {{ abs($daysLeft) }} ditë më parë. Ju lutemi rinovoni menjëherë për të rikthyer qasjen në të gjitha shërbimet.
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('payment.create') }}" style="background: #dc3545; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                                🔄 Rinovoni Anëtarësinë Tani
                            </a>
                        </div>
                    </div>
                @elseif($isExpiringSoon)
                    <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 4px; border-left: 4px solid #ffc107;">
                        <strong>⚠️ Kujtesë për Rinovim</strong><br>
                        Anëtarësia juaj skadon në {{ $daysLeft }} ditë. Rinovoni tani për të shmangur çdo ndërprerje në shërbime.
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('payment.create') }}" style="background: #ff6c37; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                                🔄 Rinovoni Herët & Kurseni Kohë
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div style="background: rgba(220, 53, 69, 0.1); border-radius: 8px; padding: 1.5rem; border-left: 4px solid #dc3545; text-align: center;">
                <div style="margin-bottom: 1rem;">
                    <div style="background: #dc3545; color: white; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                        !
                    </div>
                    <h3 style="margin: 0; color: #dc3545;">Asnjë Anëtarësi Aktive</h3>
                    <p style="margin: 0.5rem 0; color: #666;">Bashkohuni me komunitetin tonë me një anëtarësi vjetore</p>
                </div>
                
                <div style="background: white; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">CHF 350.00</div>
                    <div style="color: #666; font-size: 0.9rem;">Anëtarësi Vjetore</div>
                </div>
                
                <a href="{{ route('payment.create') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                    Merrni Anëtarësinë Tani
                </a>
            </div>
        @endif
    </div>

    <!-- Personal Statistics -->
    <div class="card">
        <h2 class="card-title">Statistikat e Mia</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($userStats['total_paid'], 2) }}</div>
                <small style="color: white;">Gjithsej të Paguara</small>
            </div>
            <div style="text-align: center; padding: 1rem; background-color: #C19A61; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($userStats['total_donations'], 2) }}</div>
                <small style="color: white;">Dhurime</small>
            </div>
            <div style="text-align: center; padding: 1rem; background-color: #28a745; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ $userStats['completed_payments'] }}</div>
                <small style="color: white;">E përfunduar</small>
            </div>
            @if($userStats['pending_payments'] > 0)
                <div style="text-align: center; padding: 1rem; background-color: #ffc107; border-radius: 12px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ $userStats['pending_payments'] }}</div>
                    <small style="color: white;">Në pritje</small>
                </div>
            @endif
        </div>
    </div>

    <!-- My Payment History -->
    <div class="card">
        <h2 class="card-title">Historiku im i Pagesave</h2>
        @if(auth()->user()->payments->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Lloji</th>
                            <th>Shuma</th>
                            <th>Statusi</th>
                            <th>Data</th>
                            <th>Veprimet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->payments->sortByDesc('created_at')->take(10) as $payment)
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
                                          background-color: {{ $payment->status === 'completed' ? '#d4f4d4' : ($payment->status === 'pending' ? '#fff3cd' : '#f8d7da') }};
                                          color: {{ $payment->status === 'completed' ? '#1F6E38' : ($payment->status === 'pending' ? '#856404' : '#721c24') }};">
                                        @if($payment->status === 'completed')
                                            E përfunduar
                                        @elseif($payment->status === 'pending')
                                            Në pritje
                                        @else
                                            {{ ucfirst($payment->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('payment.index') }}" 
                                               style="background: #1F6E38; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;"
                                               title="Shiko të Gjitha Pagesat & Eksporto PDF">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                                </svg>
                                                Shiko
                                            </a>
                                        @endif
                                        <button onclick="deletePayment({{ $payment->id }})" 
                                                style="background: #dc3545; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;"
                                                title="Fshi Pagesën">
                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(auth()->user()->payments->count() > 10)
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="{{ route('payment.index') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;">
                        Shiko të Gjitha Pagesat ({{ auth()->user()->payments->count() }})
                    </a>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 2rem; color: #666;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h3 style="margin-bottom: 0.5rem; color: #333;">Asnjë pagesë ende</h3>
                <p style="margin-bottom: 1.5rem;">Filloni me anëtarësinë tuaj vjetore për CHF 350</p>
                <a href="{{ route('payment.create') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;">
                    Krijo Pagesën e Parë
                </a>
            </div>
        @endif
    </div>

    <script>
        function deletePayment(paymentId) {
            if (confirm('Jeni i sigurt që doni ta fshini këtë pagesë? Ky veprim nuk mund të zhbëhet.')) {
                // Create a form dynamically and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/payments/${paymentId}`;
                form.style.display = 'none';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add DELETE method
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</x-app-layout> 