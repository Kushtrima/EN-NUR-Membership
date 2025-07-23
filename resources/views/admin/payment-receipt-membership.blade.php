<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Membership Receipt #{{ $payment->id }}</title>
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
        
        .membership-badge {
            background: linear-gradient(135deg, #1F6E38 0%, #2d8a4a 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 5px;
            display: inline-block;
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
        
        .membership-details {
            background-color: #e8f5e8;
            border: 1px solid #1F6E38;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .membership-details h4 {
            color: #1F6E38;
            margin: 0 0 10px 0;
            font-size: 13px;
        }
        
        .validity-period {
            background: linear-gradient(135deg, #1F6E38 0%, #2d8a4a 100%);
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: bold;
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
        
        .benefits-list {
            background-color: #f8f9fa;
            border-left: 4px solid #1F6E38;
            padding: 12px;
            margin: 15px 0;
        }
        
        .benefits-list h4 {
            color: #1F6E38;
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        
        .benefits-list ul {
            margin: 0;
            padding-left: 18px;
            font-size: 10px;
        }
        
        .benefits-list li {
            margin-bottom: 3px;
        }
        
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .reference-code {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 10px;
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('app.name', 'EN NUR - MEMBERSHIP') }}</div>
        <div style="font-size: 11px;">Mosque Community Center</div>
        <div class="receipt-title">Annual Membership Receipt</div>
        <div class="membership-badge">MEMBERSHIP CONFIRMATION</div>
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
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                </div>
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
                <div class="info-row">
                    <span class="info-label">Member ID:</span>
                    <span class="reference-code">MBR-{{ str_pad($payment->user->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Validity Period -->
    <div class="validity-period">
        <div style="font-size: 11px; margin-bottom: 3px;">MEMBERSHIP VALID FROM</div>
        <div style="font-size: 14px;">{{ $payment->created_at->format('M d, Y') }} - {{ $payment->created_at->addYear()->format('M d, Y') }}</div>
        <div style="font-size: 10px; margin-top: 3px; opacity: 0.9;">Full 365 Days of Access</div>
    </div>

    <!-- Amount Section -->
    <div class="amount-section">
        <div class="label">Annual Membership Fee</div>
        <div class="amount">{{ $payment->formatted_amount }}</div>
        <div class="currency">Swiss Francs (CHF)</div>
    </div>

    <!-- Membership Benefits -->
    <div class="benefits-list">
        <h4>🕌 Your Membership Includes:</h4>
        <ul>
            <li><strong>Prayer Facilities:</strong> 24/7 access to all prayer rooms and ablution facilities</li>
            <li><strong>Religious Services:</strong> Friday prayers, Eid celebrations, and special religious events</li>
            <li><strong>Educational Programs:</strong> Quran classes, Islamic studies, and Arabic language courses</li>
            <li><strong>Community Events:</strong> Social gatherings, cultural celebrations, and family activities</li>
            <li><strong>Counseling Services:</strong> Religious guidance and community support</li>
            <li><strong>Youth Programs:</strong> Activities and education for children and teenagers</li>
            <li><strong>Library Access:</strong> Islamic books, resources, and study materials</li>
            <li><strong>Event Discounts:</strong> Reduced rates for weddings, conferences, and special occasions</li>
            <li><strong>Voting Rights:</strong> Participation in community decisions and board elections</li>
        </ul>
    </div>

    <!-- Membership Details -->
    <div class="membership-details">
        <h4>Important Membership Information:</h4>
        <div style="font-size: 10px; line-height: 1.5;">
            • <strong>Renewal:</strong> Your membership will expire on {{ $payment->created_at->addYear()->format('M d, Y') }}. Renewal notices will be sent 30 days prior.<br>
            • <strong>Transfer:</strong> Membership is personal and non-transferable.<br>
            • <strong>Contact:</strong> For any membership inquiries, contact us at info@mosque.ch or +41 XX XXX XX XX<br>
            • <strong>Card:</strong> Your membership card will be available for pickup at the mosque reception within 7 days.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for joining our community!</strong> This receipt confirms your annual membership.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i T') }}</p>
        <div style="margin-top: 10px; font-size: 11px; line-height: 1.5; color: #000;">
            <strong>Islamische Verein EN-NUR</strong><br>
            Ziegeleistrasse 2, 8253 Diessenhofen<br>
            <a href="mailto:info@xhamia-en-nur.ch" style="color: #1A53F2; text-decoration: underline;">info@xhamia-en-nur.ch</a><br>
            Tel: <a href="tel:0526541460" style="color: #1A53F2; text-decoration: underline;">052 654 14 60</a>
        </div>
    </div>
</body>
</html> 