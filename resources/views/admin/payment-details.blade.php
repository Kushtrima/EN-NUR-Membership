<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Payment Information -->
    <div>
        <h4 style="color: #1F6E38; margin-bottom: 1rem; border-bottom: 2px solid #1F6E38; padding-bottom: 0.5rem;">
            Payment Information
        </h4>
        
        <div style="space-y: 0.75rem;">
            <div style="margin-bottom: 0.75rem;">
                <strong>Payment ID:</strong>
                <span style="font-family: monospace; background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 3px;">
                    {{ $payment->id }}
                </span>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Amount:</strong>
                <span style="font-size: 1.1rem; font-weight: 600; color: #1F6E38;">
                    {{ $payment->formatted_amount }}
                </span>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Type:</strong>
                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;
                      background-color: {{ $payment->payment_type === 'membership' ? '#d4edda' : '#fff3cd' }};
                      color: {{ $payment->payment_type === 'membership' ? '#155724' : '#856404' }};">
                    {{ ucfirst($payment->payment_type) }}
                </span>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Payment Method:</strong>
                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;
                      background-color: #e9ecef; color: #495057;">
                    {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}
                </span>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Status:</strong>
                <span style="padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600;
                      background-color: {{ $payment->status === 'completed' ? '#d4edda' : ($payment->status === 'pending' ? '#fff3cd' : '#f8d7da') }};
                      color: {{ $payment->status === 'completed' ? '#155724' : ($payment->status === 'pending' ? '#856404' : '#721c24') }};">
                    {{ ucfirst($payment->status) }}
                    @if($payment->status === 'pending' && $payment->payment_method === 'bank_transfer')
                        <span style="margin-left: 0.25rem;">⏳</span>
                    @endif
                </span>
            </div>
            
            @if($payment->transaction_id)
                <div style="margin-bottom: 0.75rem;">
                    <strong>Transaction ID:</strong>
                    <code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem; word-break: break-all;">
                        {{ $payment->transaction_id }}
                    </code>
                </div>
            @endif
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Created:</strong>
                <div>
                    {{ $payment->created_at->format('M d, Y \a\t H:i') }}
                    <br>
                    <small style="color: #6c757d;">{{ $payment->created_at->diffForHumans() }}</small>
                </div>
            </div>
            
            @if($payment->updated_at != $payment->created_at)
                <div style="margin-bottom: 0.75rem;">
                    <strong>Last Updated:</strong>
                    <div>
                        {{ $payment->updated_at->format('M d, Y \a\t H:i') }}
                        <br>
                        <small style="color: #6c757d;">{{ $payment->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- User Information -->
    <div>
        <h4 style="color: #1F6E38; margin-bottom: 1rem; border-bottom: 2px solid #1F6E38; padding-bottom: 0.5rem;">
            User Information
        </h4>
        
        <div style="space-y: 0.75rem;">
            <div style="margin-bottom: 0.75rem;">
                <strong>Name:</strong>
                <div>{{ $payment->user->name }}</div>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Email:</strong>
                <div>
                    <a href="mailto:{{ $payment->user->email }}" style="color: #1F6E38; text-decoration: none;">
                        {{ $payment->user->email }}
                    </a>
                </div>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Role:</strong>
                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;
                      background-color: {{ $payment->user->isSuperAdmin() ? '#dc3545' : ($payment->user->isAdmin() ? '#d4edda' : '#e9ecef') }};
                      color: {{ $payment->user->isSuperAdmin() ? 'white' : ($payment->user->isAdmin() ? '#155724' : '#495057') }};">
                    {{ \App\Models\User::getRoles()[$payment->user->role] }}
                </span>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Email Verified:</strong>
                @if($payment->user->hasVerifiedEmail())
                    <span style="color: #28a745;">✓ Verified</span>
                @else
                    <span style="color: #dc3545;">✗ Not verified</span>
                @endif
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Member Since:</strong>
                <div>
                    {{ $payment->user->created_at->format('M d, Y') }}
                    <br>
                    <small style="color: #6c757d;">{{ $payment->user->created_at->diffForHumans() }}</small>
                </div>
            </div>
            
            <div style="margin-bottom: 0.75rem;">
                <strong>Total Payments:</strong>
                <div>
                    {{ $payment->user->payments->count() }} payments
                    @if($payment->user->payments->where('status', 'completed')->count() > 0)
                        <br>
                        <small style="color: #6c757d;">
                            CHF {{ number_format($payment->user->payments->where('status', 'completed')->sum('amount') / 100, 2) }} total
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Specific Information -->
@if($payment->payment_method === 'bank_transfer' && $payment->status === 'pending')
    <div style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px;">
        <h5 style="color: #856404; margin-bottom: 0.5rem;">Bank Transfer Instructions</h5>
        <p style="margin: 0; color: #856404; font-size: 0.9rem;">
            This payment is awaiting bank transfer verification. The user should transfer 
            <strong>{{ $payment->formatted_amount }}</strong> with reference 
            <code>PAY-{{ $payment->id }}-{{ strtoupper(substr($payment->payment_type, 0, 3)) }}</code>
        </p>
    </div>
@endif

@if($payment->payment_method === 'cash' && $payment->status === 'pending')
    <div style="margin-top: 2rem; padding: 1rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px;">
        <h5 style="color: #0c5460; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M21,4H3A2,2 0 0,0 1,6V18A2,2 0 0,0 3,20H21A2,2 0 0,0 23,18V6A2,2 0 0,0 21,4M21,18H3V6H21V18Z"/>
            </svg>
            Cash Payment Pending
        </h5>
        <p style="margin: 0 0 1rem 0; color: #0c5460; font-size: 0.9rem;">
            This payment is awaiting cash collection. User should pay 
            <strong>{{ $payment->formatted_amount }}</strong> with reference 
            <code>CASH-{{ $payment->id }}</code>
        </p>
        
        @if(auth()->user()->isSuperAdmin())
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button onclick="confirmCashPayment({{ $payment->id }})" 
                        style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem;">
                    <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"/>
                    </svg>
                    Confirm Cash Received
                </button>
                
                <button onclick="showCashPaymentForm({{ $payment->id }})" 
                        style="background: #17a2b8; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem;">
                    <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                    </svg>
                    Add Notes & Confirm
                </button>
            </div>
        @endif
    </div>
@endif

<!-- Admin Actions -->
@if(auth()->user()->isSuperAdmin())
    <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
        <h5 style="margin-bottom: 1rem;">Admin Actions</h5>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @if($payment->status === 'pending')
                <button onclick="updatePaymentStatus({{ $payment->id }}, 'completed'); closePaymentDetails();" 
                        class="btn" style="background: #28a745; color: white; font-size: 0.875rem;">
                    Mark as Completed
                </button>
                <button onclick="updatePaymentStatus({{ $payment->id }}, 'failed'); closePaymentDetails();" 
                        class="btn" style="background: #dc3545; color: white; font-size: 0.875rem;">
                    Mark as Failed
                </button>
            @endif
            <button onclick="sendPaymentNotification({{ $payment->id }})" 
                    class="btn" style="background: #17a2b8; color: white; font-size: 0.875rem;">
                Send Notification
            </button>
            <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank"
               class="btn" style="background: #6f42c1; color: white; font-size: 0.875rem; text-decoration: none;">
                Generate Receipt
            </a>
        </div>
    </div>
@endif 