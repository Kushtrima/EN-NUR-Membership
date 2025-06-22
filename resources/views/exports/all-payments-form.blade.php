<x-app-layout>
    <!-- DEBUG INFO - Remove this after testing -->
    <div style="background: #ffeb3b; padding: 1rem; margin-bottom: 1rem; border: 2px solid #ff9800;">
        <strong>DEBUG - All Payments Export Form</strong><br>
        Total Payments: {{ $allPaymentStats['total_payments'] ?? 'NOT SET' }}<br>
        Total Users: {{ $allPaymentStats['total_users'] ?? 'NOT SET' }}<br>
        Total Amount: CHF {{ number_format($allPaymentStats['total_amount'] ?? 0, 2) }}<br>
        Current User: {{ auth()->user()->name }} ({{ auth()->user()->role }})<br>
        Is Super Admin: {{ auth()->user()->isSuperAdmin() ? 'YES' : 'NO' }}
    </div>
    
    <div class="card">
        <h1 class="card-title">Export All Payments</h1>
        <p>Download a comprehensive PDF report of all payments from all users with optional filters.</p>
        
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #1F6E38; margin-top: 1rem;">
            <strong>Super Admin Export:</strong><br>
            This export includes payment data from all users in the system. Use filters to narrow down the results as needed.
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="card">
        <h2 class="card-title">System-Wide Payment Summary</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">{{ number_format($allPaymentStats['total_payments']) }}</div>
                <div style="color: #666; font-size: 0.9rem;">Total Payments</div>
            </div>
            
            <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">{{ number_format($allPaymentStats['completed_payments']) }}</div>
                <div style="color: #666; font-size: 0.9rem;">Completed</div>
            </div>
            
            <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #856404;">{{ number_format($allPaymentStats['pending_payments']) }}</div>
                <div style="color: #666; font-size: 0.9rem;">Pending</div>
            </div>
            
            <div style="background: #f8d7da; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #721c24;">{{ number_format($allPaymentStats['failed_payments'] + $allPaymentStats['cancelled_payments']) }}</div>
                <div style="color: #666; font-size: 0.9rem;">Failed/Cancelled</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Revenue Summary</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                    <div><strong>Total Revenue:</strong> CHF {{ number_format($allPaymentStats['total_amount'], 2) }}</div>
                    <div><strong>Total Users:</strong> {{ number_format($allPaymentStats['total_users']) }}</div>
                    <div><strong>Avg per User:</strong> CHF {{ $allPaymentStats['total_users'] > 0 ? number_format($allPaymentStats['total_amount'] / $allPaymentStats['total_users'], 2) : '0.00' }}</div>
                </div>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Payment Types</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                    <div><strong>Memberships:</strong> {{ number_format($allPaymentStats['membership_payments']) }}</div>
                    <div><strong>Donations:</strong> {{ number_format($allPaymentStats['donation_payments']) }}</div>
                </div>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Payment Methods</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                    <div><strong>Stripe:</strong> {{ number_format($allPaymentStats['payments_by_method']['stripe']) }}</div>
                    <div><strong>PayPal:</strong> {{ number_format($allPaymentStats['payments_by_method']['paypal']) }}</div>
                    <div><strong>TWINT:</strong> {{ number_format($allPaymentStats['payments_by_method']['twint']) }}</div>
                    <div><strong>Bank Transfer:</strong> {{ number_format($allPaymentStats['payments_by_method']['bank_transfer']) }}</div>
                </div>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Date Range</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                    @if($allPaymentStats['first_payment'])
                        <div><strong>First Payment:</strong> {{ $allPaymentStats['first_payment']->created_at->format('M d, Y') }}</div>
                        <div><strong>Latest Payment:</strong> {{ $allPaymentStats['latest_payment']->created_at->format('M d, Y') }}</div>
                    @else
                        <div>No payments found</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Export Form -->
    <div class="card">
        <h2 class="card-title">Export Options</h2>
        <form method="POST" action="{{ route('admin.exports.all') }}">
            @csrf
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <!-- Date Range -->
                <div class="form-group">
                    <label class="form-label">From Date (Optional)</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">To Date (Optional)</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>
                
                <!-- Status Filter -->
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <!-- Type Filter -->
                <div class="form-group">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="membership" {{ old('payment_type') === 'membership' ? 'selected' : '' }}>Membership</option>
                        <option value="donation" {{ old('payment_type') === 'donation' ? 'selected' : '' }}>Donation</option>
                    </select>
                </div>
                
                <!-- Method Filter -->
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="">All Methods</option>
                        <option value="stripe" {{ old('payment_method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="paypal" {{ old('payment_method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="twint" {{ old('payment_method') === 'twint' ? 'selected' : '' }}>TWINT</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
            </div>
            
            <!-- Export Button -->
            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-success" style="font-size: 1.1rem; padding: 1rem 2rem;">
                    <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    Export All Payments to PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Navigation -->
    <div class="card">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3,3H21A2,2 0 0,1 23,5V19A2,2 0 0,1 21,21H3A2,2 0 0,1 1,19V5A2,2 0 0,1 3,3M13,9V15H18V10.5L16.5,9H13M6,9V15H11V9H6Z"/>
                </svg>
                Back to Dashboard
            </a>
            
            <a href="{{ route('admin.payments') }}" class="btn btn-secondary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,18H4V8H20V18Z"/>
                </svg>
                View All Payments
            </a>
            
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16,17V19H2V17S2,13 9,13 16,17 16,17M12.5,7.5A3.5,3.5 0 0,1 9,11A3.5,3.5 0 0,1 5.5,7.5A3.5,3.5 0 0,1 9,4A3.5,3.5 0 0,1 12.5,7.5M15.94,13A5.32,5.32 0 0,1 18,17V19H22V17S22,13.37 15.94,13Z"/>
                </svg>
                Manage Users
            </a>
        </div>
    </div>

    <!-- Admin Information -->
    <div class="card">
        <h2 class="card-title">Super Admin Export Information</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Export Contents</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>All payments from all users in the system</li>
                    <li>Complete payment history and summaries</li>
                    <li>User information for each payment</li>
                    <li>Transaction details and IDs</li>
                    <li>Payment methods and statuses</li>
                    <li>System-wide statistics and totals</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Privacy & Compliance</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Export is logged with your super admin credentials</li>
                    <li>PDF includes export timestamp and admin info</li>
                    <li>All user data remains confidential</li>
                    <li>Suitable for financial reporting and audits</li>
                    <li>Professional format for official records</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Filtering Options</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Filter by date range for specific periods</li>
                    <li>Filter by payment status (completed, pending, etc.)</li>
                    <li>Filter by payment type (membership, donation)</li>
                    <li>Filter by payment method (Stripe, PayPal, etc.)</li>
                    <li>Combine multiple filters for precise results</li>
                </ul>
            </div>
        </div>
    </div>

    @if($allPaymentStats['total_payments'] === 0)
    <div class="card" style="text-align: center; padding: 3rem;">
        <h3 style="color: #6c757d; margin-bottom: 1rem;">No Payment History</h3>
        <p style="color: #6c757d; margin-bottom: 2rem;">{{ $user->name }} hasn't made any payments yet.</p>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back to Users</a>
    </div>
    @endif
</x-app-layout> 