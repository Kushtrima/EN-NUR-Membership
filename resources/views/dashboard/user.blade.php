<x-app-layout>
    <div class="card">
        <h1 class="card-title">
            My Dashboard
        </h1>
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

    <!-- Membership Status -->
    <div class="card">
        <h2 class="card-title">Membership Status</h2>
        
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
                    $statusText = 'Membership Expired';
                    $statusIcon = '❌';
                    $bgColor = 'rgba(220, 53, 69, 0.1)';
                } elseif ($isExpiringSoon) {
                    $statusColor = '#ff6c37'; // Orange
                    $statusText = 'Membership Expiring Soon';
                    $statusIcon = '⚠️';
                    $bgColor = 'rgba(255, 108, 55, 0.1)';
                } else {
                    $statusColor = '#1F6E38'; // Green
                    $statusText = 'Active Member';
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
                            <p style="margin: 0; color: #666;">Your membership has expired. Please renew to continue accessing services.</p>
                        @elseif($isExpiringSoon)
                            <p style="margin: 0; color: #666;">Your membership expires soon. Consider renewing to avoid interruption.</p>
                        @else
                            <p style="margin: 0; color: #666;">Your membership is active and valid</p>
                        @endif
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Member Since:</strong><br>
                        <span style="color: {{ $statusColor }};">{{ $membershipStart->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <strong>Valid Until:</strong><br>
                        <span style="color: {{ $statusColor }};">{{ $membershipEnd->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <strong>Days Remaining:</strong><br>
                        <span style="color: {{ $statusColor }};">
                            {{ $daysLeft > 0 ? $daysLeft . ' days' : 'EXPIRED (' . abs($daysLeft) . ' days ago)' }}
                        </span>
                    </div>
                </div>
                
                @if($isExpired)
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8d7da; border-radius: 4px; border-left: 4px solid #dc3545;">
                        <strong>🚨 URGENT: Membership Expired</strong><br>
                        Your membership expired {{ abs($daysLeft) }} days ago. Please renew immediately to restore access to all services.
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('payment.create') }}" style="background: #dc3545; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                                🔄 Renew Membership Now
                            </a>
                        </div>
                    </div>
                @elseif($isExpiringSoon)
                    <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 4px; border-left: 4px solid #ffc107;">
                        <strong>⚠️ Renewal Reminder</strong><br>
                        Your membership expires in {{ $daysLeft }} days. Renew now to avoid any interruption in services.
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('payment.create') }}" style="background: #ff6c37; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                                🔄 Renew Early & Save Time
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
                    <h3 style="margin: 0; color: #dc3545;">No Active Membership</h3>
                    <p style="margin: 0.5rem 0; color: #666;">Join our community with an annual membership</p>
                </div>
                
                <div style="background: white; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">CHF 350.00</div>
                    <div style="color: #666; font-size: 0.9rem;">Annual Membership</div>
                </div>
                
                <a href="{{ route('payment.create') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                    Get Membership Now
                </a>
            </div>
        @endif
    </div>

    <!-- Personal Statistics -->
    <div class="card">
        <h2 class="card-title">My Statistics</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background-color: #1F6E38; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($userStats['total_paid'], 2) }}</div>
                <small style="color: white;">Total Paid</small>
            </div>
            <div style="text-align: center; padding: 1rem; background-color: #C19A61; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">CHF {{ number_format($userStats['total_donations'], 2) }}</div>
                <small style="color: white;">Donations</small>
            </div>
            <div style="text-align: center; padding: 1rem; background-color: #28a745; border-radius: 12px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ $userStats['completed_payments'] }}</div>
                <small style="color: white;">Completed</small>
            </div>
            @if($userStats['pending_payments'] > 0)
                <div style="text-align: center; padding: 1rem; background-color: #ffc107; border-radius: 12px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">{{ $userStats['pending_payments'] }}</div>
                    <small style="color: white;">Pending</small>
                </div>
            @endif
        </div>
    </div>

    <!-- My Payment History -->
    <div class="card">
        <h2 class="card-title">My Payment History</h2>
        @if(auth()->user()->payments->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->payments->sortByDesc('created_at')->take(10) as $payment)
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
                                          background-color: {{ $payment->status === 'completed' ? '#d4f4d4' : ($payment->status === 'pending' ? '#fff3cd' : '#f8d7da') }};
                                          color: {{ $payment->status === 'completed' ? '#1F6E38' : ($payment->status === 'pending' ? '#856404' : '#721c24') }};">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <a href="{{ route('payment.index') }}" 
                                           style="background: #1F6E38; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;"
                                           title="View All Payments & Export PDFs">
                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                            </svg>
                                            View
                                        </a>
                                    @elseif($payment->status === 'pending')
                                        <span style="color: #856404; font-size: 0.75rem;">Processing</span>
                                    @else
                                        <span style="color: #721c24; font-size: 0.75rem;">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(auth()->user()->payments->count() > 10)
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route('payment.index') }}" style="color: #1F6E38; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        📊 View All Payments & Export PDFs
                    </a>
                </div>
            @else
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route('payment.index') }}" style="color: #1F6E38; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        View All Payments & Export PDFs
                    </a>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 2rem; color: #666;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <p><strong>No payments yet</strong></p>
                <p>Start with your annual membership for CHF 350 or make a donation to support our community.</p>
                <a href="{{ route('payment.create') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 1rem; display: inline-block;">
                    Make Your First Payment
                </a>
            </div>
        @endif
    </div>


</x-app-layout> 