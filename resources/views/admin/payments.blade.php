<x-app-layout>
@include('components.confirmation-modal')
    <!-- Page Header -->
    <div class="card" style="background: linear-gradient(135deg, #1F6E38 0%, #2d8a4a 100%); color: white; border: none;">
        <div style="display: flex; align-items: center;">
            <svg style="width: 64px; height: 64px; margin-right: 1.5rem; opacity: 0.9;" fill="white" viewBox="0 0 24 24">
                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
            </svg>
            <div>
                <h1 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;">Payment Management</h1>
                <p style="margin: 0; font-size: 1.1rem; opacity: 0.9; color: white;">
                    Monitor all payments, verify bank transfers, and manage payment statuses
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">{{ $stats['total'] ?? 0 }}</div>
            <div style="color: #6c757d;">Total Payments</div>
        </div>
        <div class="card" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #28a745;">{{ $stats['completed'] ?? 0 }}</div>
            <div style="color: #6c757d;">Completed</div>
        </div>
        <div class="card" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #ffc107;">{{ $stats['pending'] ?? 0 }}</div>
            <div style="color: #6c757d;">Pending Verification</div>
        </div>
        <div class="card" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #dc3545;">{{ $stats['failed'] ?? 0 }}</div>
            <div style="color: #6c757d;">Failed</div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card">
        <form method="GET" action="{{ route('admin.payments') }}">
            <!-- Search Section (Top Row) -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1F6E38;">
                    <svg style="width: 16px; height: 16px; margin-right: 0.5rem; vertical-align: middle;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                    </svg>
                    Search Payments
                </label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by user name, email, or transaction ID..." 
                       value="{{ request('search') }}"
                       style="font-size: 1rem; padding: 0.75rem 1rem; border: 2px solid #e9ecef; border-radius: 8px; transition: border-color 0.3s ease;"
                       onfocus="this.style.borderColor='#1F6E38'" 
                       onblur="this.style.borderColor='#e9ecef'">
            </div>

                         <!-- Filters Section (Bottom Row) -->
             <div style="border-top: 1px solid #e9ecef; padding-top: 1.5rem;">
                 <div style="display: grid; grid-template-columns: 140px 140px 1fr auto; gap: 1rem; align-items: end;">
                     <!-- Status Filter -->
                     <div>
                         <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Status</label>
                         <select name="status" class="form-control" style="border-radius: 6px;">
                             <option value="">All</option>
                             <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                             <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                             <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                             <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                         </select>
                     </div>

                     <!-- Payment Method Filter -->
                     <div>
                         <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Method</label>
                         <select name="method" class="form-control" style="border-radius: 6px;">
                             <option value="">All</option>
                             <option value="stripe" {{ request('method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                             <option value="paypal" {{ request('method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                             <option value="twint" {{ request('method') === 'twint' ? 'selected' : '' }}>TWINT</option>
                             <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                         </select>
                     </div>

                     <!-- Date Range Filter -->
                     <div>
                         <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Date Range</label>
                         <div style="display: flex; gap: 0.5rem;">
                             <input type="date" name="date_from" class="form-control" 
                                    value="{{ request('date_from') }}" 
                                    style="border-radius: 6px; font-size: 0.9rem;"
                                    title="From Date">
                             <input type="date" name="date_to" class="form-control" 
                                    value="{{ request('date_to') }}" 
                                    style="border-radius: 6px; font-size: 0.9rem;"
                                    title="To Date">
                         </div>
                     </div>

                     <!-- Action Buttons -->
                     <div style="display: flex; gap: 0.5rem;">
                         <button type="submit" class="btn" 
                                 style="background: #1F6E38; color: white; padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 400; display: flex; align-items: center; gap: 0.5rem;">
                             <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                                 <path d="M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                             </svg>
                             Apply Filters
                         </button>
                         @if(request()->hasAny(['search', 'status', 'method', 'date_from', 'date_to']))
                             <a href="{{ route('admin.payments') }}" class="btn btn-secondary" 
                                style="padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 400; display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                                 <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                                     <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                                 </svg>
                                 Clear
                             </a>
                         @endif
                     </div>
                 </div>
             </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
            <h2 class="card-title">All Payments ({{ $payments->total() }})</h2>
            @if(auth()->user()->isSuperAdmin() && $payments->where('status', 'pending')->where('payment_method', 'bank_transfer')->count() > 0)
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 0.75rem; color: #856404;">
                    <strong>{{ $payments->where('status', 'pending')->where('payment_method', 'bank_transfer')->count() }}</strong> bank transfers awaiting verification
                </div>
            @endif
        </div>

        @if($payments->count() > 0)
            <!-- Bulk Actions Bar (Hidden by default) -->
            <div id="bulk-actions-bar" style="display: none; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="background: #1F6E38; color: white; padding: 0.5rem; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #1F6E38; font-size: 1rem;">
                                <span id="selected-count">0</span> payments selected
                            </div>
                            <div style="color: #6c757d; font-size: 0.875rem;">
                                Choose an action to apply to selected payments
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button onclick="bulkUpdateStatus('completed')" class="btn" 
                                style="background: #28a745; color: white; font-size: 0.9rem; padding: 0.75rem 1rem; border-radius: 6px; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#218838'; this.style.transform='translateY(-1px)';"
                                onmouseout="this.style.background='#28a745'; this.style.transform='translateY(0)';">
                            <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                                <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"/>
                            </svg>
                            Mark as Completed
                        </button>
                        
                        <button onclick="bulkUpdateStatus('failed')" class="btn" 
                                style="background: #dc3545; color: white; font-size: 0.9rem; padding: 0.75rem 1rem; border-radius: 6px; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-1px)';"
                                onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)';">
                            <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                                <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                            </svg>
                            Mark as Failed
                        </button>
                        
                        <button onclick="bulkSendNotifications()" class="btn" 
                                style="background: #17a2b8; color: white; font-size: 0.9rem; padding: 0.75rem 1rem; border-radius: 6px; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#138496'; this.style.transform='translateY(-1px)';"
                                onmouseout="this.style.background='#17a2b8'; this.style.transform='translateY(0)';">
                            <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                                <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/>
                            </svg>
                            Send Notifications
                        </button>
                        
                        <button onclick="clearBulkSelection()" class="btn" 
                                style="background: #6c757d; color: white; font-size: 0.9rem; padding: 0.75rem 1rem; border-radius: 6px; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)';"
                                onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                            <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                                <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                            </svg>
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            @if(auth()->user()->isSuperAdmin())
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                </th>
                            @endif
                            <th>ID</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr id="payment-row-{{ $payment->id }}" class="payment-row">
                                @if(auth()->user()->isSuperAdmin())
                                    <td>
                                        <input type="checkbox" class="payment-checkbox" value="{{ $payment->id }}" onchange="updateBulkSelection()">
                                    </td>
                                @endif
                                <td>{{ $payment->id }}</td>
                                <td>
                                    <div style="font-size: 0.8rem; line-height: 1.2;">
                                        {{ $payment->created_at->format('M d, Y') }} <span style="color: #6c757d;">{{ $payment->created_at->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="line-height: 1.3;">
                                        <strong style="font-size: 0.9rem;">{{ $payment->user->name }}</strong>
                                        <br>
                                        <small style="color: #6c757d; font-size: 0.75rem;">{{ $payment->user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong style="font-size: 0.9rem;">{{ $payment->formatted_amount }}</strong>
                                </td>
                                <td>
                                    <span style="padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600;
                                          background-color: {{ $payment->payment_type === 'membership' ? '#d4edda' : '#fff3cd' }};
                                          color: {{ $payment->payment_type === 'membership' ? '#155724' : '#856404' }};">
                                        {{ ucfirst($payment->payment_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.7rem;
                                          background-color: #e9ecef; color: #495057;">
                                        {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $payment->status }}" style="padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600;
                                          background-color: {{ $payment->status === 'completed' ? '#d4edda' : ($payment->status === 'pending' ? '#fff3cd' : '#f8d7da') }};
                                          color: {{ $payment->status === 'completed' ? '#155724' : ($payment->status === 'pending' ? '#856404' : '#721c24') }};">
                                        {{ ucfirst($payment->status) }}
                                        @if($payment->status === 'pending' && $payment->payment_method === 'bank_transfer')
                                            <span style="margin-left: 0.25rem;">⏳</span>
                                        @elseif($payment->status === 'pending' && $payment->payment_method === 'cash')
                                            <span style="margin-left: 0.25rem;">💰</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($payment->transaction_id)
                                        <code style="background: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 3px; font-size: 0.7rem;">
                                            {{ $payment->transaction_id }}
                                        </code>
                                    @else
                                        <span style="color: #6c757d; font-size: 0.8rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.2rem;">
                                        @if(auth()->user()->isSuperAdmin())
                                            @if($payment->status === 'pending')
                                                <button onclick="updatePaymentStatus({{ $payment->id }}, 'completed')" 
                                                        class="btn" style="background: #28a745; color: white; padding: 0.3rem; border-radius: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                                        title="Mark as Completed">
                                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"/>
                                                    </svg>
                                                </button>
                                                <button onclick="updatePaymentStatus({{ $payment->id }}, 'failed')" 
                                                        class="btn" style="background: #dc3545; color: white; padding: 0.3rem; border-radius: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                                        title="Mark as Failed">
                                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            <button onclick="showPaymentDetails({{ $payment->id }})" 
                                                    class="btn btn-secondary" style="padding: 0.3rem; border-radius: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                                    title="View Details">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                                                </svg>
                                            </button>
                                            <button onclick="sendPaymentNotification({{ $payment->id }})" 
                                                    class="btn" style="background: #17a2b8; color: white; padding: 0.3rem; border-radius: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                                    title="Send Notification">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('payments.delete', $payment) }}" 
                                                  style="display: inline-block; margin: 0;"
                                                  onsubmit="return handleDeleteSubmit(event, {{ $payment->id }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn" style="background: #dc3545; color: white; padding: 0.3rem; border-radius: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                                        onmouseover="this.style.background='#c82333'" 
                                                        onmouseout="this.style.background='#dc3545'"
                                                        title="Delete Payment">
                                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
                <div style="color: #6c757d; font-size: 0.9rem;">
                    Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} results
                </div>
                
                @if ($payments->hasPages())
                    <div style="display: flex; gap: 0.25rem; align-items: center;">
                        {{-- Previous Page Link --}}
                        @if ($payments->onFirstPage())
                            <span style="padding: 0.5rem 0.75rem; color: #6c757d; border: 1px solid #e9ecef; border-radius: 6px; background: #f8f9fa; cursor: not-allowed;">
                                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                </svg>
                                Previous
                            </span>
                        @else
                            <a href="{{ $payments->previousPageUrl() }}" 
                               style="padding: 0.5rem 0.75rem; color: #1F6E38; border: 1px solid #1F6E38; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 0.25rem; transition: all 0.2s;"
                               onmouseover="this.style.background='#1F6E38'; this.style.color='white';"
                               onmouseout="this.style.background='transparent'; this.style.color='#1F6E38';">
                                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                </svg>
                                Previous
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                            @if ($page == $payments->currentPage())
                                <span style="padding: 0.5rem 0.75rem; background: #1F6E38; color: white; border: 1px solid #1F6E38; border-radius: 6px; font-weight: 600;">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" 
                                   style="padding: 0.5rem 0.75rem; color: #495057; border: 1px solid #e9ecef; border-radius: 6px; text-decoration: none; transition: all 0.2s;"
                                   onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#1F6E38';"
                                   onmouseout="this.style.background='transparent'; this.style.borderColor='#e9ecef';">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($payments->hasMorePages())
                            <a href="{{ $payments->nextPageUrl() }}" 
                               style="padding: 0.5rem 0.75rem; color: #1F6E38; border: 1px solid #1F6E38; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 0.25rem; transition: all 0.2s;"
                               onmouseover="this.style.background='#1F6E38'; this.style.color='white';"
                               onmouseout="this.style.background='transparent'; this.style.color='#1F6E38';">
                                Next
                                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                </svg>
                            </a>
                        @else
                            <span style="padding: 0.5rem 0.75rem; color: #6c757d; border: 1px solid #e9ecef; border-radius: 6px; background: #f8f9fa; cursor: not-allowed;">
                                Next
                                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                <svg style="width: 64px; height: 64px; margin-bottom: 1rem;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                </svg>
                <h3>No payments found</h3>
                <p>No payments match your current filters.</p>
            </div>
        @endif
    </div>

    <!-- Payment Details Modal -->
    <div id="payment-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;" onclick="closePaymentDetails()">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; padding: 0; width: 90%; max-width: 800px; max-height: 90%; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; background: linear-gradient(135deg, #1F6E38 0%, #2d8a4a 100%); color: white;">
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
                    <svg style="width: 24px; height: 24px;" fill="white" viewBox="0 0 24 24">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                    </svg>
                    Payment Details
                </h3>
                <button onclick="closePaymentDetails()" 
                        style="background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; backdrop-filter: blur(10px);"
                        onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.1)';"
                        onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';"
                        title="Close">
                    <svg style="width: 20px; height: 20px;" fill="white" viewBox="0 0 24 24">
                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Content -->
            <div style="padding: 2rem; max-height: calc(90vh - 100px); overflow-y: auto;">
                <div id="payment-details-content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedPayments = new Set();

        function showBulkActions() {
            document.getElementById('bulk-actions-bar').style.display = 'block';
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
                if (selectAll.checked) {
                    selectedPayments.add(checkbox.value);
                } else {
                    selectedPayments.delete(checkbox.value);
                }
            });
            
            updateBulkSelection();
        }

        function updateBulkSelection() {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            selectedPayments.clear();
            
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedPayments.add(checkbox.value);
                }
            });
            
            document.getElementById('selected-count').textContent = selectedPayments.size;
            
            if (selectedPayments.size > 0) {
                document.getElementById('bulk-actions-bar').style.display = 'block';
            } else {
                document.getElementById('bulk-actions-bar').style.display = 'none';
            }
        }

        function clearBulkSelection() {
            selectedPayments.clear();
            document.querySelectorAll('.payment-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all').checked = false;
            document.getElementById('bulk-actions-bar').style.display = 'none';
        }

        async function updatePaymentStatus(paymentId, status) {
            const confirmed = await confirmPaymentAction(status, 1);
            if (!confirmed) return;
            
            try {
                const response = await fetch(`/admin/payments/${paymentId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to update payment status');
                }
            } catch (error) {
                alert('Error updating payment status');
            }
        }

        async function bulkUpdateStatus(status) {
            if (selectedPayments.size === 0) return;
            
            const confirmed = await confirmPaymentAction(status, selectedPayments.size);
            if (!confirmed) return;
            
            try {
                const response = await fetch('/admin/payments/bulk-status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        payment_ids: Array.from(selectedPayments),
                        status 
                    })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to update payment statuses');
                }
            } catch (error) {
                alert('Error updating payment statuses');
            }
        }

        async function showPaymentDetails(paymentId) {
            try {
                const response = await fetch(`/admin/payments/${paymentId}/details`);
                const data = await response.text();
                
                document.getElementById('payment-details-content').innerHTML = data;
                document.getElementById('payment-details-modal').style.display = 'block';
            } catch (error) {
                alert('Error loading payment details');
            }
        }

        function closePaymentDetails() {
            document.getElementById('payment-details-modal').style.display = 'none';
        }

        async function sendPaymentNotification(paymentId) {
            const confirmed = await confirmPaymentAction('notify', 1);
            if (!confirmed) return;
            
            try {
                const response = await fetch(`/admin/payments/${paymentId}/notify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    alert('Notification sent successfully');
                } else {
                    alert('Failed to send notification');
                }
            } catch (error) {
                alert('Error sending notification');
            }
        }

        async function bulkSendNotifications() {
            if (selectedPayments.size === 0) return;
            
            const confirmed = await confirmPaymentAction('notify', selectedPayments.size);
            if (!confirmed) return;
            
            try {
                const response = await fetch('/admin/payments/bulk-notify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        payment_ids: Array.from(selectedPayments)
                    })
                });
                
                if (response.ok) {
                    alert('Notifications sent successfully');
                    clearBulkSelection();
                } else {
                    alert('Failed to send notifications');
                }
            } catch (error) {
                alert('Error sending notifications');
            }
        }

        function refreshPayments() {
            location.reload();
        }

        async function deletePayment(paymentId) {
            const confirmed = await confirmPaymentAction('delete', 1);
            if (!confirmed) return;
            
            try {
                const response = await fetch(`/payments/${paymentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to delete payment');
                }
            } catch (error) {
                alert('Error deleting payment');
            }
        }

        async function handleDeleteSubmit(event, paymentId) {
            event.preventDefault();
            
            const confirmed = await confirmPaymentAction('delete', 1);
            if (confirmed) {
                event.target.submit();
            }
            
            return false;
        }

        // Auto-refresh every 30 seconds for pending payments
        setInterval(() => {
            const pendingCount = document.querySelectorAll('.status-pending').length;
            if (pendingCount > 0) {
                // Subtle refresh without full page reload
            }
        }, 30000);

        // Cash Payment Functions
        async function confirmCashPayment(paymentId) {
            const confirmed = confirm('Are you sure you want to confirm this cash payment as received?');
            if (!confirmed) return;
            
            try {
                const response = await fetch(`/admin/payments/cash/confirm/${paymentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    alert('Cash payment confirmed successfully!');
                    location.reload();
                } else {
                    alert('Failed to confirm cash payment');
                }
            } catch (error) {
                alert('Error confirming cash payment');
            }
        }

        function showCashPaymentForm(paymentId) {
            const notes = prompt('Add notes about the cash payment (optional):');
            if (notes === null) return; // User cancelled
            
            confirmCashPaymentWithNotes(paymentId, notes);
        }

        async function confirmCashPaymentWithNotes(paymentId, notes) {
            try {
                const response = await fetch(`/admin/payments/cash/confirm/${paymentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ notes })
                });
                
                if (response.ok) {
                    alert('Cash payment confirmed successfully with notes!');
                    location.reload();
                } else {
                    alert('Failed to confirm cash payment');
                }
            } catch (error) {
                alert('Error confirming cash payment');
            }
        }
    </script>
</x-app-layout> 