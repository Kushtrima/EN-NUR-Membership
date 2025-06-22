<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Payments Report - {{ $exportDate->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #1F6E38;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1F6E38;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #1F6E38;
        }
        
        .user-info h3 {
            margin: 0 0 10px 0;
            color: #1F6E38;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 10px 3px 0;
            width: 30%;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        
        .filters {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filters h4 {
            margin: 0 0 8px 0;
            color: #495057;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            background: #1F6E38;
            color: white;
            padding: 15px;
            text-align: center;
            margin: 0 5px;
            border-radius: 5px;
            width: 33.33%;
        }
        
        .summary-card:first-child {
            margin-left: 0;
        }
        
        .summary-card:last-child {
            margin-right: 0;
        }
        
        .summary-amount {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 11px;
            opacity: 0.9;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .payments-table th {
            background: #1F6E38;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #165028;
        }
        
        .payments-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .payments-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .type-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .type-membership {
            background: #C19A61;
            color: white;
        }
        
        .type-donation {
            background: #1F6E38;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        
        .no-payments {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">{{ config('app.name', 'EN NUR - MEMBERSHIP') }}</div>
        <div class="subtitle">All Payments Report</div>
        <div style="font-size: 12px; color: #666;">
            Generated on {{ $exportDate->format('F d, Y \a\t H:i') }}
        </div>
    </div>

    <!-- System Information -->
    <div class="user-info">
        <h3>System Overview</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Total Payments:</div>
                <div class="info-value">{{ number_format($totalPayments) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Completed Payments:</div>
                <div class="info-value">{{ number_format($completedPayments) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Users:</div>
                <div class="info-value">{{ number_format($totalUsers) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Revenue:</div>
                <div class="info-value">CHF {{ number_format($totalAmount, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters Applied -->
    @if($filters['start_date'] || $filters['end_date'] || $filters['status'] || $filters['payment_type'] || $filters['payment_method'])
    <div class="filters">
        <h4>Filters Applied:</h4>
        @if($filters['start_date'])
            <strong>From:</strong> {{ Carbon\Carbon::parse($filters['start_date'])->format('F d, Y') }}<br>
        @endif
        @if($filters['end_date'])
            <strong>To:</strong> {{ Carbon\Carbon::parse($filters['end_date'])->format('F d, Y') }}<br>
        @endif
        @if($filters['status'])
            <strong>Status:</strong> {{ ucfirst($filters['status']) }}<br>
        @endif
        @if($filters['payment_type'])
            <strong>Type:</strong> {{ ucfirst($filters['payment_type']) }}<br>
        @endif
        @if($filters['payment_method'])
            <strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $filters['payment_method'])) }}<br>
        @endif
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-amount">CHF {{ number_format($totalAmount, 2) }}</div>
            <div class="summary-label">Total Revenue</div>
        </div>
        <div class="summary-card">
            <div class="summary-amount">{{ number_format($completedPayments) }}</div>
            <div class="summary-label">Completed Payments</div>
        </div>
        <div class="summary-card">
            <div class="summary-amount">{{ number_format($totalUsers) }}</div>
            <div class="summary-label">Total Users</div>
        </div>
    </div>

    <!-- Payments Table -->
    @if($payments->count() > 0)
    <table class="payments-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Method</th>
                <th>Transaction ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                <td style="font-size: 10px;">{{ $payment->user->name }}</td>
                <td>
                    <span class="type-badge type-{{ $payment->payment_type }}">
                        {{ ucfirst($payment->payment_type) }}
                    </span>
                </td>
                <td>CHF {{ number_format($payment->amount / 100, 2) }}</td>
                <td>
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td style="font-size: 10px;">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                <td style="font-family: monospace; font-size: 9px;">
                    {{ $payment->transaction_id ?? 'N/A' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-payments">
        <p>No payments found for the selected criteria.</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated by {{ $exportedBy->name }} ({{ $exportedBy->email }}) on {{ $exportDate->format('F d, Y \a\t H:i') }}</p>
        <p>{{ config('app.name') }} - Confidential All Payments Report</p>
        <p>For questions about this report, please contact our support team.</p>
    </div>
</body>
</html> 