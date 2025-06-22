<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1F6E38;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1F6E38;
            margin-bottom: 3px;
        }
        
        .receipt-title {
            font-size: 16px;
            color: #666;
            margin-top: 8px;
        }
        
        .receipt-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .receipt-info-left, .receipt-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }
        
        .receipt-info-right {
            text-align: left;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-section h3 {
            color: #1F6E38;
            font-size: 14px;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #1F6E38;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 100px;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .payment-table th {
            background-color: #1F6E38;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .payment-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 11px;
        }
        
        .amount-section {
            background-color: #1F6E38;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .amount-section .label {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .amount-section .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .amount-section .currency {
            font-size: 11px;
            opacity: 0.9;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            display: inline-block;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .reference-code {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 10px;
        }
        
        .pending-notice {
            background-color: #fff3cd;
            border-left: 4px solid #856404;
            padding: 10px;
            margin: 15px 0;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .type-badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .type-membership {
            background-color: #d4edda;
            color: #155724;
        }
        
        .type-donation {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('app.name', 'EN NUR - MEMBERSHIP') }}</div>
        <div style="font-size: 11px;">Mosque Community Center</div>
        <div class="receipt-title">Payment Receipt</div>
    </div>

    <!-- Receipt Information -->
    <div class="receipt-info">
        <div class="receipt-info-left">
            <div class="info-section">
                <h3>Receipt Information</h3>
                <div class="info-row">
                    <span class="info-label">Receipt #:</span>
                    <span class="reference-code">{{ $payment->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Issued:</span>
                    {{ now()->format('M d, Y') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Date:</span>
                    {{ $payment->created_at->format('M d, Y H:i') }}
                </div>
                @if($payment->transaction_id)
                <div class="info-row">
                    <span class="info-label">Transaction ID:</span>
                    <span class="reference-code">{{ $payment->transaction_id }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="receipt-info-right">
            <div class="info-section">
                <h3>Member Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    {{ $payment->user->name }}
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    {{ $payment->user->email }}
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since:</span>
                    {{ $payment->user->created_at->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Table -->
    <table class="payment-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($payment->payment_type === 'membership')
                        Annual Membership Fee<br>
                        <small style="color: #666;">Access to all mosque facilities and programs</small>
                    @else
                        Community Donation<br>
                        <small style="color: #666;">Supporting mosque operations and programs</small>
                    @endif
                </td>
                <td>
                    <span class="type-badge {{ $payment->payment_type === 'membership' ? 'type-membership' : 'type-donation' }}">
                        {{ ucfirst($payment->payment_type) }}
                    </span>
                </td>
                <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td>
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td style="font-weight: bold; color: #1F6E38; font-size: 12px;">
                    {{ $payment->formatted_amount }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Amount Section -->
    <div class="amount-section">
        <div class="label">Total Amount Paid</div>
        <div class="amount">{{ $payment->formatted_amount }}</div>
        <div class="currency">Swiss Francs (CHF)</div>
    </div>

    <!-- Payment Description -->
    <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 15px 0; text-align: center; font-size: 11px; color: #495057;">
        @if($payment->payment_type === 'membership')
            <strong>This payment covers your annual membership for one full year.</strong><br>
            Your membership includes access to all mosque services, facilities, prayer times, community events, 
            Islamic education programs, and full participation in our religious community activities.
        @else
            <strong>Thank you for your generous donation to our mosque community.</strong><br>
            Your contribution helps us maintain our facilities, support community programs, 
            and continue serving the Muslim community with quality religious services.
        @endif
    </div>

    @if($payment->status === 'pending')
        <div class="pending-notice">
            <strong>Payment Pending</strong><br>
            @if($payment->payment_method === 'bank_transfer')
                Your bank transfer is being processed. Please allow 1-3 business days for verification.
            @else
                Your payment is being processed and will be confirmed shortly.
            @endif
        </div>
    @endif

    <!-- Footer -->
    <div class="footer" style="position: absolute; bottom: 15mm; left: 15mm; right: 15mm;">
        <p>This is an official receipt for your payment. Please keep this for your records.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i T') }}</p>
        <p style="margin-top: 10px;">
            {{ config('app.name') }} - Serving the Muslim Community | Tax-exempt Religious Organization
        </p>
    </div>
</body>
</html> 