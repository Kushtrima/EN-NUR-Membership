<x-app-layout>
    <div class="card">
        <h1 class="card-title">Export Payment History - {{ $user->name }}</h1>
        <p>Download a PDF report of {{ $user->name }}'s payment history with optional filters.</p>
        
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #1F6E38; margin-top: 1rem;">
            <strong>User Information:</strong><br>
            <strong>Name:</strong> {{ $user->name }}<br>
            <strong>Email:</strong> {{ $user->email }}<br>
            <strong>Role:</strong> {{ \App\Models\User::getRoles()[$user->role] }}<br>
            <strong>Member Since:</strong> {{ $user->created_at->format('F d, Y') }}
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="card">
        <h2 class="card-title">{{ $user->name }}'s Payment Summary</h2>
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
        <form method="POST" action="{{ route('admin.exports.admin', $user) }}">
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
                    Download PDF Report for {{ $user->name }}
                </button>
            </div>
        </form>
    </div>

    <!-- User's Payment History Table -->
    @if($paymentStats['total_payments'] > 0)
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">{{ $user->name }}'s Payment History ({{ $paymentStats['total_payments'] }} payments)</h2>
            <div style="display: flex; gap: 1rem;">
                <!-- Quick Export All Button -->
                <form method="POST" action="{{ route('admin.exports.admin', $user) }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: #C19A61; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                            onmouseover="this.style.background='#a67c52'" onmouseout="this.style.background='#C19A61'">
                        <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Export All {{ $paymentStats['total_payments'] }} Payments
                    </button>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Date</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Type</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Amount</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Method</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Status</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Transaction ID</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->payments()->orderBy('created_at', 'desc')->get() as $payment)
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 1rem;">
                                <div style="font-weight: bold;">{{ $payment->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.8rem; color: #666;">{{ $payment->created_at->format('H:i') }}</div>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: {{ $payment->payment_type === 'membership' ? '#1F6E38' : '#C19A61' }}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                    {{ ucfirst($payment->payment_type) }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: bold; font-size: 1.1rem;">CHF {{ number_format($payment->amount / 100, 2) }}</div>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</div>
                            </td>
                            <td style="padding: 1rem;">
                                @php
                                    $statusColors = [
                                        'completed' => '#28a745',
                                        'pending' => '#ffc107',
                                        'failed' => '#dc3545',
                                        'cancelled' => '#6c757d'
                                    ];
                                    $statusColor = $statusColors[$payment->status] ?? '#6c757d';
                                @endphp
                                <span style="background: {{ $statusColor }}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-family: monospace; font-size: 0.9rem;">
                                    {{ $payment->transaction_id ?? 'N/A' }}
                                </div>
                            </td>
                                                         <td style="padding: 1rem;">
                                 <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end;">
                                     @if($payment->status === 'completed')
                                         <!-- Export PDF Button (Green) -->
                                         <form method="POST" action="{{ route('admin.exports.admin', $user) }}" style="display: inline; margin: 0;">
                                             @csrf
                                             <!-- Export only this specific payment by date -->
                                             <input type="hidden" name="start_date" value="{{ $payment->created_at->format('Y-m-d') }}">
                                             <input type="hidden" name="end_date" value="{{ $payment->created_at->format('Y-m-d') }}">
                                             <input type="hidden" name="type" value="{{ $payment->payment_type }}">
                                             <input type="hidden" name="status" value="{{ $payment->status }}">
                                             <button type="submit" 
                                                     style="background: #28a745; color: white; padding: 0.5rem 0.75rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.4rem; height: 32px; box-sizing: border-box; white-space: nowrap;"
                                                     onmouseover="this.style.background='#218838'; this.style.transform='translateY(-1px)'" 
                                                     onmouseout="this.style.background='#28a745'; this.style.transform='translateY(0)'"
                                                     title="Export this payment to PDF report">
                                                 <svg style="width: 14px; height: 14px;" fill="white" viewBox="0 0 24 24">
                                                     <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                                 </svg>
                                                 Export PDF
                                             </button>
                                         </form>
                                     @else
                                         <span style="color: #6c757d; font-size: 0.8rem; font-style: italic;">
                                             @if($payment->status === 'pending')
                                                 Processing...
                                             @else
                                                 {{ ucfirst($payment->status) }}
                                             @endif
                                         </span>
                                     @endif
                                     
                                     <!-- Delete Button (Red) -->
                                     <form method="POST" action="{{ route('admin.payments.delete', $payment) }}" 
                                           style="display: inline; margin: 0;"
                                           onsubmit="return handleDeletePayment(event, this, {{ $payment->id }})">
                                         @csrf
                                         @method('DELETE')
                                         <button type="submit" 
                                                 style="background: #dc3545; color: white; padding: 0.5rem 0.75rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.4rem; height: 32px; box-sizing: border-box;"
                                                 onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-1px)'" 
                                                 onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)'"
                                                 title="Delete this payment">
                                             <svg style="width: 14px; height: 14px;" fill="white" viewBox="0 0 24 24">
                                                 <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                                             </svg>
                                             Delete
                                         </button>
                                     </form>
                                 </div>
                             </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Navigation -->
    <div class="card">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16,17V19H2V17S2,13 9,13 16,17 16,17M12.5,7.5A3.5,3.5 0 0,1 9,11A3.5,3.5 0 0,1 5.5,7.5A3.5,3.5 0 0,1 9,4A3.5,3.5 0 0,1 12.5,7.5M15.94,13A5.32,5.32 0 0,1 18,17V19H22V17S22,13.37 15.94,13Z"/>
                </svg>
                Back to Users
            </a>
            
            <a href="{{ route('admin.payments') }}" class="btn btn-secondary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,18H4V8H20V18Z"/>
                </svg>
                View All Payments
            </a>
            
            <a href="{{ route('admin.users.export', $user) }}" class="btn btn-outline-primary">
                <svg style="width: 1em; height: 1em; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                </svg>
                Export User Data (GDPR)
            </a>
        </div>
    </div>

    <!-- Admin Information -->
    <div class="card">
        <h2 class="card-title">Admin Export Information</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Export Contents</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Complete payment history for {{ $user->name }}</li>
                    <li>Payment summaries and totals</li>
                    <li>Transaction details and IDs</li>
                    <li>Payment methods used</li>
                    <li>Status of each payment</li>
                    <li>Admin export metadata</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Privacy & Compliance</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Export is logged with your admin credentials</li>
                    <li>PDF includes export timestamp and admin info</li>
                    <li>User data remains confidential</li>
                    <li>Suitable for compliance and audit purposes</li>
                    <li>Professional format for official records</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Super Admin Privileges</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Access to all user payment data</li>
                    <li>Full filtering and export capabilities</li>
                    <li>Export tracking and audit trail</li>
                    <li>Compliance with data protection laws</li>
                    <li>Secure PDF generation and download</li>
                </ul>
            </div>
        </div>
    </div>

    @if($paymentStats['total_payments'] === 0)
    <div class="card" style="text-align: center; padding: 3rem;">
        <h3 style="color: #6c757d; margin-bottom: 1rem;">No Payment History</h3>
        <p style="color: #6c757d; margin-bottom: 2rem;">{{ $user->name }} hasn't made any payments yet.</p>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back to Users</a>
    </div>
    @endif

    <!-- Custom Confirmation Modal -->
    <div id="deleteConfirmationModal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    ">
        <div id="deleteModalContent" style="
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 28rem;
            width: 100%;
            margin: 1rem;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        ">
            <!-- Header -->
            <div style="
                display: flex;
                align-items: center;
                justify-content: center;
                padding-top: 2rem;
                padding-bottom: 1rem;
            ">
                <div style="
                    width: 4rem;
                    height: 4rem;
                    background: #fecaca;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 1rem;
                ">
                    <svg style="
                        width: 2rem;
                        height: 2rem;
                        color: #dc2626;
                    " fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Content -->
            <div style="padding: 0 2rem 2rem 2rem;">
                <h3 style="
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #111827;
                    text-align: center;
                    margin-bottom: 0.75rem;
                    margin-top: 0;
                ">
                    Delete Payment
                </h3>
                
                <p style="
                    color: #6b7280;
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 0.875rem;
                    margin-top: 0;
                ">
                    Permanent deletion warning
                </p>
                
                <p style="
                    color: #374151;
                    text-align: center;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    margin-top: 0;
                ">
                    Are you sure you want to delete this payment?
                </p>
                
                <!-- Action Buttons -->
                <div style="
                    display: flex;
                    gap: 0.75rem;
                    flex-direction: column;
                " id="deleteButtonContainer">
                    <button type="button" onclick="closeDeleteModal()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: white;
                        border: 2px solid #d1d5db;
                        color: #374151;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#f9fafb';
                        this.style.borderColor='#9ca3af';
                        this.style.transform='translateY(-1px)';
                    " onmouseout="
                        this.style.background='white';
                        this.style.borderColor='#d1d5db';
                        this.style.transform='translateY(0)';
                    ">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmDeletePayment()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: #dc2626;
                        color: white;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        border: none;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#b91c1c';
                        this.style.transform='translateY(-1px)';
                        this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                    " onmouseout="
                        this.style.background='#dc2626';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                    ">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Modal Animation */
    #deleteConfirmationModal.show {
        display: flex !important;
    }

    #deleteConfirmationModal.show #deleteModalContent {
        transform: scale(1) !important;
    }

    /* Responsive adjustments */
    @media (min-width: 640px) {
        #deleteButtonContainer {
            flex-direction: row !important;
        }
    }

    /* Focus states for accessibility */
    #deleteConfirmationModal button:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }
    </style>

    <script>
    let currentDeleteForm = null;
    
    function handleDeletePayment(event, form, paymentId) {
        event.preventDefault();
        
        // Store the form reference
        currentDeleteForm = form;
        
        // Show custom confirmation modal
        showDeleteModal();
        
        return false;
    }
    
         function showDeleteModal() {
         const modal = document.getElementById('deleteConfirmationModal');
         modal.classList.remove('hidden');
         modal.classList.add('show');
         document.body.style.overflow = 'hidden';
         
         // Add keyboard event listener
         document.addEventListener('keydown', handleModalKeydown);
         
         // Focus the cancel button for accessibility
         setTimeout(() => {
             const cancelButton = modal.querySelector('button[onclick="closeDeleteModal()"]');
             if (cancelButton) cancelButton.focus();
         }, 100);
     }
    
         function closeDeleteModal() {
         const modal = document.getElementById('deleteConfirmationModal');
         modal.classList.remove('show');
         document.body.style.overflow = 'auto';
         currentDeleteForm = null;
         
         setTimeout(() => {
             modal.style.display = 'none';
         }, 300);
         
         // Remove keyboard event listener
         document.removeEventListener('keydown', handleModalKeydown);
     }
     
     function handleModalKeydown(event) {
         if (event.key === 'Escape') {
             closeDeleteModal();
         }
     }
    
    function confirmDeletePayment() {
        console.log('=== confirmDeletePayment called ===');
        
        if (!currentDeleteForm) {
            console.error('ERROR: No currentDeleteForm found');
            showNotification('Error: No form reference found!', 'error');
            return;
        }
        
        console.log('currentDeleteForm found:', currentDeleteForm);
        console.log('Form action URL:', currentDeleteForm.action);
        
        // Get the button BEFORE closing modal (so currentDeleteForm is still available)
        const button = currentDeleteForm.querySelector('button[type="submit"]');
        const formAction = currentDeleteForm.action;
        const formData = new FormData(currentDeleteForm);
        const tableRow = currentDeleteForm.closest('tr');
        
        // Close modal
        closeDeleteModal();
        const originalText = button.innerHTML;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = `
            <svg style="width: 14px; height: 14px; animation: spin 1s linear infinite;" fill="white" viewBox="0 0 24 24">
                <path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/>
            </svg>
            Deleting...
        `;
        
        // Add spinning animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        
                 // Submit the form via AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('=== Starting AJAX request ===');
        console.log('CSRF Token found:', csrfToken ? 'Yes' : 'No');
        console.log('Form action URL:', formAction);
        console.log('Form data entries:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }
        
        fetch(formAction, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Show success message
                showNotification('Payment deleted successfully!', 'success');
                
                                 // Remove the payment row from the table
                 const row = tableRow;
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    row.remove();
                    
                    // Check if there are no more payments
                    const tbody = document.querySelector('tbody');
                    if (tbody && tbody.children.length === 0) {
                        // Refresh the page to show "No payments" message
                        window.location.reload();
                    }
                }, 500);
            } else {
                // Show error message
                showNotification(data.error || 'Failed to delete payment', 'error');
                
                // Reset button
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            console.error('Error message:', error.message);
            showNotification('An error occurred while deleting the payment: ' + error.message, 'error');
            
            // Reset button
            button.disabled = false;
            button.innerHTML = originalText;
        });
        
        return false;
    }
    
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            font-weight: bold;
            max-width: 400px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
    </script>
</x-app-layout> 