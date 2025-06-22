<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Renewal Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #C19A61;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1F6E38;
            margin-bottom: 10px;
        }
        .alert-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .alert-expired {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-critical {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-warning {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .membership-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-label {
            font-weight: bold;
            color: #1F6E38;
        }
        .cta-button {
            display: inline-block;
            background-color: #1F6E38;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            background-color: #165029;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        .benefits {
            margin: 20px 0;
        }
        .benefits ul {
            list-style-type: none;
            padding: 0;
        }
        .benefits li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .benefits li:before {
            content: "✓ ";
            color: #1F6E38;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <p>Membership Renewal Reminder</p>
        </div>

        <h2>Hello {{ $user->name }},</h2>

        @if($days_remaining <= 0)
            <div class="alert-box alert-expired">
                <h3>⚠️ Your Membership Has Expired</h3>
                <p>{{ $message }}</p>
            </div>
        @elseif($days_remaining <= 1)
            <div class="alert-box alert-critical">
                <h3>🚨 Urgent: Membership Expires Tomorrow</h3>
                <p>{{ $message }}</p>
            </div>
        @elseif($days_remaining <= 7)
            <div class="alert-box alert-critical">
                <h3>⏰ Membership Expires Soon</h3>
                <p>{{ $message }}</p>
            </div>
        @else
            <div class="alert-box alert-warning">
                <h3>📅 Membership Renewal Reminder</h3>
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="membership-details">
            <h3>Your Membership Details</h3>
            <div class="detail-row">
                <span class="detail-label">Membership Start:</span>
                <span>{{ $membership_start }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Membership End:</span>
                <span>{{ $membership_end }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Days Remaining:</span>
                <span>{{ $days_remaining > 0 ? $days_remaining : 'EXPIRED' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Original Payment:</span>
                <span>CHF {{ number_format($original_payment->amount / 100, 2) }} ({{ ucfirst($original_payment->payment_method) }})</span>
            </div>
        </div>

        <div class="benefits">
            <h3>Continue Enjoying These Benefits:</h3>
            <ul>
                <li>Full access to all membership features</li>
                <li>Priority customer support</li>
                <li>Exclusive member-only content</li>
                <li>Special discounts and offers</li>
                <li>Community access and networking</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="{{ $renewal_url }}" class="cta-button">
                {{ $days_remaining <= 0 ? 'Renew Now' : 'Renew Membership' }}
            </a>
        </div>

        @if($days_remaining > 0)
            <p><strong>Why renew now?</strong></p>
            <ul>
                <li>Avoid any interruption in service</li>
                <li>Maintain your membership benefits</li>
                <li>Continue your journey with us</li>
                <li>Secure your membership for another year</li>
            </ul>
        @else
            <p><strong>Renew immediately to:</strong></p>
            <ul>
                <li>Restore your membership access</li>
                <li>Regain all membership benefits</li>
                <li>Continue where you left off</li>
            </ul>
        @endif

        <p>If you have any questions about your membership or need assistance with renewal, please don't hesitate to contact our support team.</p>

        <div class="footer">
            <p>This is an automated reminder from {{ config('app.name') }}.</p>
            <p>If you believe this email was sent in error, please contact us immediately.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 