<x-app-layout>
    <div class="card">
        <h1 class="card-title">My Dashboard</h1>
        <p>Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
        
        @if(!auth()->user()->hasVerifiedEmail())
            <div class="alert alert-info">
                Your email address is not verified. Please check your email for a verification link.
                <form method="POST" action="{{ route('verification.send') }}" style="display: inline; margin-left: 1rem;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Resend Verification Email</button>
                </form>
            </div>
        @endif
    </div>

    <!-- Community Statistics -->
    <div class="card">
        <h2 class="card-title">Community Overview</h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem;">
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Total Users</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['total_users'] }}
                </div>
                <small style="color: #666;">Registered members</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Active Members</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    {{ $stats['membership_payments'] }}
                </div>
                <small style="color: #666;">CHF 350 memberships</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Total Revenue</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    CHF {{ number_format($stats['total_revenue'], 2) }}
                </div>
                <small style="color: #666;">All completed payments</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Total Donations</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    CHF {{ number_format($stats['total_donations'], 2) }}
                </div>
                <small style="color: #666;">Donation contributions</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">New Users</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['recent_registrations'] }}
                </div>
                <small style="color: #666;">Last 30 days</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Pending</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #dc3545;">
                    {{ $stats['pending_payments'] }}
                </div>
                <small style="color: #666;">Awaiting payment</small>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 class="card-title">My Payment History</h2>
        @if(auth()->user()->payments->count() > 0)
            @php
                $totalPaid = auth()->user()->payments->where('status', 'completed')->sum('amount') / 100;
                $totalDonations = auth()->user()->payments->where('payment_type', 'donation')->where('status', 'completed')->sum('amount') / 100;
            @endphp
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($totalPaid, 2) }}</div>
                    <small style="color: white;">Total Paid</small>
                </div>
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($totalDonations, 2) }}</div>
                    <small style="color: white;">Donations</small>
                </div>
                <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 4px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ auth()->user()->payments->where('status', 'completed')->count() }}</div>
                    <small style="color: white;">Payments</small>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->payments->sortByDesc('created_at')->take(5) as $payment)
                            <tr>
                                <td>
                                    @if($payment->payment_type === 'membership')
                                        <span style="color: #C19A61; font-weight: bold;">Membership</span>
                                    @else
                                        <span style="color: #1F6E38;">Donation</span>
                                    @endif
                                </td>
                                <td style="font-weight: bold;">{{ $payment->formatted_amount }}</td>
                                <td>
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; 
                                          background-color: {{ $payment->status === 'completed' ? '#d4f4d4' : '#fff3cd' }};
                                          color: {{ $payment->status === 'completed' ? '#1F6E38' : '#856404' }};">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span>{{ $payment->created_at->format('M d, Y') }}</span>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('user.payments.receipt', $payment) }}" 
                                               style="background: #1F6E38; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;"
                                               title="Download Receipt">
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
                    Showing 5 most recent payments of {{ auth()->user()->payments->count() }} total
                </p>
            @endif
        @else
            <div style="text-align: center; padding: 2rem; color: #666;">
                <p>You haven't made any payments yet.</p>
                <p>Start with your annual membership for CHF 350</p>
            </div>
        @endif
    </div>
</x-app-layout> 