<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Activated - EN NUR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #1F6E38;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .footer {
            background: #f1f1f1;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
            color: #666;
        }
        .user-info {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #1F6E38;
        }
        .info-row {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #1F6E38;
            display: inline-block;
            width: 120px;
        }
        .button {
            background: #1F6E38;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 New User Activated</h1>
        <p>EN NUR Membership Platform</p>
    </div>

    <div class="content">
        <div class="success-icon">✅</div>
        
        <p><strong>Great news!</strong> A new user has successfully completed their registration and accepted the terms and conditions on the EN NUR membership platform.</p>

        <div class="user-info">
            <h3>📋 User Details</h3>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span>{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span>{{ $user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">User ID:</span>
                <span>#{{ $user->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Role:</span>
                <span>{{ ucfirst($user->role) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Registered:</span>
                <span>{{ $user->created_at->format('d M Y, H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Terms Accepted:</span>
                <span>{{ $activatedAt->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <div class="user-info">
            <h3>🔍 Technical Details</h3>
            <div class="info-row">
                <span class="info-label">IP Address:</span>
                <span>{{ $ipAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">User Agent:</span>
                <span style="font-size: 11px; color: #666;">{{ $userAgent }}</span>
            </div>
        </div>

        <p><strong>What happens next?</strong></p>
        <ul>
            <li>✅ User can now access their dashboard</li>
            <li>✅ User can make membership payments</li>
            <li>✅ User can participate in community activities</li>
            <li>📧 User will receive membership renewal notifications</li>
        </ul>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/users" class="button">
                👥 View All Users
            </a>
            <a href="{{ config('app.url') }}/dashboard" class="button">
                📊 Admin Dashboard
            </a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from the EN NUR Membership Platform.</p>
        <p>📧 Sent to: info@xhamia-en-nur.ch | 🕒 {{ now()->format('d M Y, H:i T') }}</p>
        <p>
            <a href="{{ config('app.url') }}">Visit Platform</a> | 
            <a href="mailto:info@en-nur.org">Contact Support</a>
        </p>
    </div>
</body>
</html> 