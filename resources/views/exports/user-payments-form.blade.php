<x-app-layout>
    <!-- Navigation -->
    <div class="card">
        <div style="display: flex; justify-content: between; align-items: center; gap: 1rem;">
            <div>
                <h1 class="card-title" style="margin: 0;">Export My Payment History</h1>
                <p style="margin: 0.5rem 0 0 0;">Download a PDF report of your payment history with optional filters.</p>
            </div>
            <div>
                <a href="{{ route('payment.index') }}" style="background: #6c757d; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem;"
                   onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                    <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                        <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
                    </svg>
                    Back to Payments
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="card">
        <h2 class="card-title">Your Payment Summary</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number text-primary">{{ $paymentStats['total_payments'] }}</div>
                <div class="stat-label">Total Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-primary">{{ $paymentStats['completed_payments'] }}</div>
                <div class="stat-label">Completed Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-primary">CHF {{ number_format($paymentStats['total_amount'], 2) }}</div>
                <div class="stat-label">Total Amount Paid</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-gold">{{ $paymentStats['membership_payments'] }}</div>
                <div class="stat-label">Membership Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-gold">{{ $paymentStats['donation_payments'] }}</div>
                <div class="stat-label">Donations</div>
            </div>
            @if($paymentStats['first_payment'])
            <div class="stat-card">
                <div class="stat-number" style="font-size: 1.2rem;">{{ $paymentStats['first_payment']->created_at->format('M Y') }}</div>
                <div class="stat-label">First Payment</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Export Form -->
    <div class="card">
        <h2 class="card-title">Export Options</h2>
        <form method="POST" action="{{ route('exports.user') }}">
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
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="membership" {{ old('type') === 'membership' ? 'selected' : '' }}>Membership</option>
                        <option value="donation" {{ old('type') === 'donation' ? 'selected' : '' }}>Donation</option>
                    </select>
                </div>
            </div>
            
            <!-- Export Button -->
            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-success" style="font-size: 1.1rem; padding: 1rem 2rem;">
                    <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    Download PDF Report
                </button>
            </div>
        </form>
    </div>

    <!-- Help Information -->
    <div class="card">
        <h2 class="card-title">About PDF Export</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">What's Included</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Complete payment history</li>
                    <li>Payment summaries and totals</li>
                    <li>Transaction details and IDs</li>
                    <li>Payment methods used</li>
                    <li>Status of each payment</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Filter Options</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li><strong>Date Range:</strong> Export payments from specific periods</li>
                    <li><strong>Status:</strong> Filter by payment status (completed, pending, etc.)</li>
                    <li><strong>Type:</strong> Show only memberships or donations</li>
                    <li><strong>All Fields Optional:</strong> Leave blank to export everything</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Privacy & Security</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>PDF contains only your payment data</li>
                    <li>Secure download directly to your device</li>
                    <li>No data stored on servers during export</li>
                    <li>Professional format suitable for records</li>
                </ul>
            </div>
        </div>
    </div>

    @if($paymentStats['total_payments'] === 0)
    <div class="card" style="text-align: center; padding: 3rem;">
        <h3 style="color: #6c757d; margin-bottom: 1rem;">No Payment History</h3>
        <p style="color: #6c757d; margin-bottom: 2rem;">You haven't made any payments yet. Once you make payments, you'll be able to export your payment history here.</p>
        <a href="{{ route('payment.create') }}" class="btn btn-success">Make Your First Payment</a>
    </div>
    @endif
</x-app-layout> 