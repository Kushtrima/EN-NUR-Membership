<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donation Receipt #{{ $payment->id }}</title>
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
            border-bottom: 2px solid #C19A61;
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
        
        .donation-badge {
            background: linear-gradient(135deg, #C19A61 0%, #d4b574 100%);
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
            border-bottom: 1px solid #C19A61;
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
        
        .amount-section {
            background: linear-gradient(135deg, #C19A61 0%, #d4b574 100%);
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
        
        .thank-you-section {
            background-color: #fff8e1;
            border: 1px solid #C19A61;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        
        .thank-you-section h4 {
            color: #C19A61;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .impact-section {
            background-color: #f0f8f0;
            border-left: 4px solid #1F6E38;
            padding: 12px;
            margin: 15px 0;
        }
        
        .impact-section h4 {
            color: #1F6E38;
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        
        .impact-list {
            font-size: 10px;
            margin: 0;
            padding-left: 18px;
        }
        
        .impact-list li {
            margin-bottom: 3px;
        }
        
        .tax-info {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 12px;
            margin: 15px 0;
        }
        
        .tax-info h4 {
            color: #0c5460;
            margin: 0 0 8px 0;
            font-size: 12px;
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
        <div class="receipt-title">Donation Receipt</div>
        <div class="donation-badge">CHARITABLE CONTRIBUTION</div>
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
                    <span class="info-label">Donation Date:</span>
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
                <h3>Donor Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    {{ $payment->user->name }}
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    {{ $payment->user->email }}
                </div>
                <div class="info-row">
                    <span class="info-label">Donor Since:</span>
                    {{ $payment->user->created_at->format('M d, Y') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Donor ID:</span>
                    <span class="reference-code">DNR-{{ str_pad($payment->user->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Amount Section -->
    <div class="amount-section">
        <div class="label">Total Donation Amount</div>
        <div class="amount">{{ $payment->formatted_amount }}</div>
        <div class="currency">Swiss Francs (CHF)</div>
    </div>

    <!-- Thank You Section -->
    <div class="thank-you-section">
        <h4>🤲 Thank You for Your Generous Support!</h4>
        <div style="font-size: 11px; line-height: 1.5;">
            Your donation helps us serve the Muslim community and maintain our religious and cultural programs. 
            May Allah (SWT) reward you for your generosity and bless you abundantly.
        </div>
    </div>

    <!-- Community Impact -->
    <div class="impact-section">
        <h4>🌟 How Your Donation Helps Our Community:</h4>
        <ul class="impact-list">
            <li><strong>Facility Maintenance:</strong> Keeping our mosque clean, safe, and welcoming for all</li>
            <li><strong>Utility Costs:</strong> Electricity, heating, water, and internet services</li>
            <li><strong>Religious Programs:</strong> Friday prayers, Eid celebrations, and special events</li>
            <li><strong>Educational Services:</strong> Quran classes, Islamic studies, and youth programs</li>
            <li><strong>Community Support:</strong> Helping families in need and emergency assistance</li>
            <li><strong>Interfaith Dialogue:</strong> Building bridges with other communities</li>
            <li><strong>Charitable Activities:</strong> Food banks, clothing drives, and community outreach</li>
            <li><strong>Imam Services:</strong> Religious guidance, counseling, and spiritual support</li>
        </ul>
    </div>

    <!-- Tax Information -->
    <div class="tax-info">
        <h4>📋 Tax Deduction Information:</h4>
        <div style="font-size: 10px; line-height: 1.5;">
            <strong>Tax-Exempt Status:</strong> {{ config('app.name') }} is a registered religious organization in Switzerland.<br>
            <strong>Tax Registration:</strong> CHE-XXX.XXX.XXX<br>
            <strong>Deduction Eligibility:</strong> This donation may be tax-deductible according to Swiss tax laws.<br>
            <strong>Important:</strong> Please consult your tax advisor for specific deduction rules in your canton.<br>
            <strong>Receipt Validity:</strong> Keep this receipt for your tax records.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Barakallahu feeki!</strong> May Allah bless you for your contribution to our community.</p>
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