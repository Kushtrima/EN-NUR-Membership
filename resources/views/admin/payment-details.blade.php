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