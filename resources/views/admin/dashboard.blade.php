<x-app-layout>
    <div class="card">
        <h1 class="card-title">Users Dashboard</h1>
        <p>Welcome to the admin panel. Here you can manage users, payments, and view statistics.</p>
    </div>

    <div class="card">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('admin.users') }}" class="btn">Manage Users</a>
            <a href="{{ route('admin.payments') }}" class="btn btn-success">View Payments</a>
        </div>
    </div>

    <!-- Revenue Statistics -->
    <div class="card" style="background: linear-gradient(135deg, #1F6E38 0%, #28a745 100%); color: white; margin-bottom: 1.5rem;">
        <h2 style="color: white; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            💰 Revenue Overview
        </h2>
        
        @php
            $membershipRevenue = $dashboardStats['membership_revenue'];
            $donationRevenue = $dashboardStats['donation_revenue'];
            $totalRevenue = $membershipRevenue + $donationRevenue;
            $membershipCount = $dashboardStats['membership_count'];
            $donationCount = $dashboardStats['donation_count'];
        @endphp
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center; padding: 1.5rem; background: rgba(255,255,255,0.1); border-radius: 12px; border: 2px solid rgba(255,255,255,0.2);">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem;">CHF {{ number_format($membershipRevenue, 2) }}</div>
                <div style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.25rem;">💳 Annual Memberships</div>
                <div style="font-size: 0.8rem; opacity: 0.7;">{{ $membershipCount }} members @ CHF 350 each</div>
            </div>
            
            <div style="text-align: center; padding: 1.5rem; background: rgba(255,255,255,0.1); border-radius: 12px; border: 2px solid rgba(255,255,255,0.2);">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem;">CHF {{ number_format($donationRevenue, 2) }}</div>
                <div style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.25rem;">❤️ Community Donations</div>
                <div style="font-size: 0.8rem; opacity: 0.7;">{{ $donationCount }} donations (CHF 50-500)</div>
            </div>
            
            <div style="text-align: center; padding: 1.5rem; background: rgba(255,255,255,0.15); border-radius: 12px; border: 2px solid rgba(255,255,255,0.3);">
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem;">CHF {{ number_format($totalRevenue, 2) }}</div>
                <div style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.25rem;">💎 Total Revenue</div>
                <div style="font-size: 0.8rem; opacity: 0.7;">{{ $membershipCount + $donationCount }} completed payments</div>
            </div>
        </div>
        
        @if($totalRevenue > 0)
        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.1); border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 0.9rem;">Revenue Split:</span>
                <span style="font-size: 0.9rem;">{{ round(($membershipRevenue / $totalRevenue) * 100) }}% Memberships | {{ round(($donationRevenue / $totalRevenue) * 100) }}% Donations</span>
            </div>
            <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.2); border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; background: #C19A61; width: {{ ($membershipRevenue / $totalRevenue) * 100 }}%; float: left;"></div>
                <div style="height: 100%; background: #fff; width: {{ ($donationRevenue / $totalRevenue) * 100 }}%;"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- MEMBERSHIP RENEWAL NOTIFICATIONS Section -->
    <div class="card" style="background: linear-gradient(135deg, #C19A61 0%, #D4AF37 100%); color: white; margin-bottom: 1.5rem;">
        <h2 style="color: white; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            🔔 Membership Renewal Notifications
        </h2>
        
        @if($membershipRenewals->count() > 0)
            <p style="margin-bottom: 1rem; opacity: 0.9;">
                {{ $membershipRenewals->count() }} member(s) require attention for membership renewal.
            </p>
            
            <div style="overflow-x: auto; border-radius: 8px; background: rgba(255,255,255,0.1);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(0,0,0,0.2);">
                            <th style="padding: 1rem; text-align: left; color: white; font-weight: bold;">Member</th>
                            <th style="padding: 1rem; text-align: center; color: white; font-weight: bold;">Days Until Expiry</th>
                            <th style="padding: 1rem; text-align: center; color: white; font-weight: bold;">Expiry Date</th>
                            <th style="padding: 1rem; text-align: center; color: white; font-weight: bold;">Status</th>
                            <th style="padding: 1rem; text-align: center; color: white; font-weight: bold;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($membershipRenewals as $renewal)
                            @php
                                $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                                $isExpired = $daysUntilExpiry <= 0;
                                $isUrgent = $daysUntilExpiry <= 7 && $daysUntilExpiry > 0;
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <td style="padding: 0.75rem;">
                                    <div style="font-weight: bold; margin-bottom: 0.25rem;">{{ $renewal->user->name }}</div>
                                    <div style="font-size: 0.875rem; opacity: 0.8;">{{ $renewal->user->email }}</div>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <span style="
                                        padding: 0.5rem 1rem; 
                                        border-radius: 20px; 
                                        font-weight: bold; 
                                        background: {{ $isExpired ? '#dc3545' : ($isUrgent ? '#fd7e14' : '#28a745') }}; 
                                        color: white;
                                        font-size: 0.875rem;
                                    ">
                                        {{ $isExpired ? 'EXPIRED' : ($daysUntilExpiry . ' days') }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center; font-size: 0.875rem;">
                                    {{ $renewal->membership_end_date ? $renewal->membership_end_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    @if($isExpired)
                                        <span style="color: #ffcccb; font-weight: bold;">⚠️ OVERDUE</span>
                                    @elseif($isUrgent)
                                        <span style="color: #ffd700; font-weight: bold;">🚨 URGENT</span>
                                    @else
                                        <span style="color: #90EE90; font-weight: bold;">📅 REMINDER</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <form method="POST" action="{{ route('admin.renewals.notify', $renewal) }}" style="display: inline-block; margin-right: 0.5rem;">
                                        @csrf
                                        <button type="submit" 
                                                style="
                                                    background: #fff; 
                                                    color: #C19A61; 
                                                    border: none; 
                                                    padding: 0.5rem 1rem; 
                                                    border-radius: 4px; 
                                                    font-weight: bold; 
                                                    cursor: pointer;
                                                    font-size: 0.875rem;
                                                "
                                                onmouseover="this.style.background='#f8f9fa'"
                                                onmouseout="this.style.background='#fff'">
                                            📧 Send Reminder
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1rem; text-align: center;">
                <form method="POST" action="{{ route('admin.notifications.bulk-send') }}" style="display: inline-block;">
                    @csrf
                    <input type="hidden" name="renewal_ids" value="{{ $membershipRenewals->pluck('id')->implode(',') }}">
                    <button type="submit" 
                            style="
                                background: #dc3545; 
                                color: white; 
                                border: none; 
                                padding: 1rem 2rem; 
                                border-radius: 8px; 
                                font-weight: bold; 
                                cursor: pointer;
                                font-size: 1.1rem;
                                box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
                                transition: all 0.3s ease;
                            "
                            onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(220, 53, 69, 0.4)'"
                            onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(220, 53, 69, 0.3)'">
                        🚨 NOTIFY ALL EXPIRED USERS ({{ $membershipRenewals->where('days_until_expiry', '<=', 0)->count() }})
                    </button>
                </form>
                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: rgba(255,255,255,0.8);">
                    Sends personalized emails to all expired members with their specific membership details
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 2rem; opacity: 0.8;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <div style="font-size: 1.1rem; font-weight: bold; margin-bottom: 0.5rem;">All Memberships Current!</div>
                <div style="font-size: 0.9rem;">No renewal notifications needed at this time.</div>
            </div>
        @endif
    </div>

    <!-- USERS Section -->
    <div class="card">
        <h2 class="card-title">USERS</h2>
        <p style="margin-bottom: 1rem;">Manage all registered users and their membership status.</p>
        
        <!-- Search Box for Users -->
        <div style="margin-bottom: 1.5rem;">
            <input type="text" id="userSearch" placeholder="Search users by name or email..." 
                   style="width: 100%; max-width: 400px; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        
        <!-- Users Table -->
        <div style="overflow-x: auto; max-height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 4px;">
            <table class="table" style="margin-bottom: 0;">
                <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                    <tr>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Name</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Email</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Member Status</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Total Paid</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Registered</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @foreach($recentUsers->take(10) as $user)
                        <tr class="user-row">
                            <td style="padding: 0.75rem;">{{ $user->name }}</td>
                            <td style="padding: 0.75rem;">{{ $user->email }}</td>
                            <td style="padding: 0.75rem;">
                                @if($user->payments->where('payment_type', 'membership')->where('status', 'completed')->count() > 0)
                                    <span style="color: #1F6E38; font-weight: bold; padding: 0.25rem 0.5rem; background: #d4f4d4; border-radius: 4px;">✓ Active Member</span>
                                @else
                                    <span style="color: #dc3545; font-weight: bold; padding: 0.25rem 0.5rem; background: #f8d7da; border-radius: 4px;">✗ Not Member</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; font-weight: bold;">CHF {{ number_format($user->payments->where('status', 'completed')->sum('amount') / 100, 2) }}</td>
                            <td style="padding: 0.75rem;">{{ $user->created_at->format('M d, Y') }}</td>
                            <td style="padding: 0.75rem;">
                                <a href="{{ route('admin.users') }}" style="color: #1F6E38; text-decoration: none; font-size: 0.875rem;">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; text-align: center;">
            <a href="{{ route('admin.users') }}" class="btn" style="background-color: #C19A61;">View All Users ({{ $recentUsers->count() }})</a>
        </div>
    </div>

    <!-- PAYMENTS Section -->
    <div class="card">
        <h2 class="card-title">PAYMENTS</h2>
        <p style="margin-bottom: 1rem;">Monitor all payment transactions and their status.</p>
        
        <!-- Search Box for Payments -->
        <div style="margin-bottom: 1.5rem;">
            <input type="text" id="paymentSearch" placeholder="Search payments by user or amount..." 
                   style="width: 100%; max-width: 400px; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        
        <!-- Payments Table -->
        <div style="overflow-x: auto; max-height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 4px;">
            <table class="table" style="margin-bottom: 0;">
                <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                    <tr>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">User</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Type</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Amount</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Status</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Method</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Date</th>
                        <th style="padding: 1rem; border-bottom: 2px solid #1F6E38;">Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    @foreach($recentPayments->take(10) as $payment)
                        <tr class="payment-row">
                            <td style="padding: 0.75rem; font-weight: 500;">{{ $payment->user->name }}</td>
                            <td style="padding: 0.75rem;">
                                @if($payment->payment_type === 'membership')
                                    <span style="color: #C19A61; font-weight: bold; padding: 0.25rem 0.5rem; background: #fdf9f0; border-radius: 4px;">Membership</span>
                                @else
                                    <span style="color: #1F6E38; font-weight: bold; padding: 0.25rem 0.5rem; background: #d4f4d4; border-radius: 4px;">Donation</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; font-weight: bold; font-size: 1.1rem;">{{ $payment->formatted_amount }}</td>
                            <td style="padding: 0.75rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; font-weight: bold;
                                      background-color: {{ $payment->status === 'completed' ? '#d4f4d4' : '#fff3cd' }};
                                      color: {{ $payment->status === 'completed' ? '#1F6E38' : '#856404' }};">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-transform: capitalize;">{{ $payment->payment_method ?? 'N/A' }}</td>
                            <td style="padding: 0.75rem;">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding: 0.75rem;">
                                <a href="{{ route('admin.payments') }}" style="color: #1F6E38; text-decoration: none; font-size: 0.875rem;">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; text-align: center;">
            <a href="{{ route('admin.payments') }}" class="btn btn-success">View All Payments ({{ $recentPayments->count() }})</a>
        </div>
    </div>

    <script>
        // Search functionality for Users
        document.getElementById('userSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Search functionality for Payments
        document.getElementById('paymentSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.payment-row');
            
            rows.forEach(row => {
                const user = row.cells[0].textContent.toLowerCase();
                const amount = row.cells[2].textContent.toLowerCase();
                const type = row.cells[1].textContent.toLowerCase();
                
                if (user.includes(searchTerm) || amount.includes(searchTerm) || type.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</x-app-layout> 