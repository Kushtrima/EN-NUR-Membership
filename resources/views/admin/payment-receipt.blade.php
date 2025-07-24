<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturë Pagese</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #1F6E38;
            font-size: 28px;
            margin: 0;
            font-weight: normal;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .payment-table th,
        .payment-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-weight: normal;
        }
        
        .payment-table th {
            background-color: #f8f9fa;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
        }
        
        .amount-cell {
            text-align: right;
            font-weight: normal;
        }
        
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
            font-weight: normal;
        }
        
        .totals-table .total-row {
            border-bottom: 2px solid #1F6E38;
            font-size: 14px;
        }
        
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header" style="text-align: left; margin-bottom: 30px; border-bottom: 1px solid #1F6E38; padding-bottom: 10px; position: relative;">
            <h1 style="color: #1F6E38; font-size: 28px; margin: 0; font-weight: normal;">XHAMIA EN NUR</h1>
            <div style="position: absolute; top: 0; right: 0; background: #000; color: white; padding: 5px 15px; font-size: 14px;">FATURË</div>
        </div>

        <!-- Three Column Section -->
        <table style="width: 100%; margin: 40px 0; border-collapse: collapse;">
            <tr>
                <td style="width: 33%; vertical-align: top; padding-right: 20px; font-size: 12px; color: #000;">
                    <div style="margin-bottom: 4px;">Numri i faturës: {{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</div>
                    <div style="margin-bottom: 4px;">Data e lëshimit: {{ now()->format('F j, Y') }}</div>
                    <div style="margin-bottom: 4px;">Data e duhur: {{ now()->format('F j, Y') }}</div>
                    @if($payment->transaction_id)
                    <div style="margin-bottom: 4px;">ID e transaksionit: {{ $payment->transaction_id }}</div>
                    @endif
                </td>
                <td style="width: 33%; vertical-align: top; padding-right: 20px; font-size: 12px; color: #000;">
                    <div style="margin-bottom: 4px;">Islamische Verein EN-NUR</div>
                    <div style="margin-bottom: 4px;">Ziegeleistrasse 2</div>
                    <div style="margin-bottom: 4px;">8253 Diessenhofen</div>
                    <div style="margin-bottom: 4px;">Zvicër</div>
                    <div style="margin-bottom: 4px;">Tel: 052 654 14 60</div>
                    <div style="margin-bottom: 4px;">info@xhamia-en-nur.ch</div>
                </td>
                <td style="width: 33%; vertical-align: top; font-size: 12px; color: #000;">
                    <div style="margin-bottom: 4px;">{{ $payment->user->name }}</div>
                    <div style="margin-bottom: 4px;">{{ $payment->user->email }}</div>
                    <div style="margin-bottom: 4px;">Anëtar që nga {{ $payment->user->created_at->format('F j, Y') }}</div>
                    @if($payment->payment_type === 'membership')
                    <div style="margin-bottom: 4px;">ID e anëtarit: MBR-{{ str_pad($payment->user->id, 6, '0', STR_PAD_LEFT) }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Amount Due Section -->
        <div style="border-top: 2px solid #1F6E38; padding-top: 15px; margin: 30px 0; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 3px; color: #000;">CHF {{ (int)($payment->amount / 100) }}</div>
            <div style="font-size: 12px; color: #666;">për të paguar më {{ now()->format('F j, Y') }}</div>
        </div>

        <!-- Payment Table -->
        <div style="border-top: 1px solid #1F6E38; padding-top: 20px; margin: 30px 0;">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Përshkrimi</th>
                        <th style="text-align: center;">Sasia</th>
                        <th style="text-align: right;">Çmimi për njësi</th>
                        <th style="text-align: right;">Shuma</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            @if($payment->payment_type === 'membership')
                                Tarifë Vjetore Anëtarësie<br>
                                <small style="color: #666;">{{ now()->format('M j, Y') }} – {{ now()->addYear()->format('M j, Y') }}</small>
                            @else
                                Donacion për Komunitetin<br>
                                <small style="color: #666;">{{ $payment->created_at->format('M j, Y') }}</small>
                            @endif
                        </td>
                        <td style="text-align: center;">1</td>
                        <td class="amount-cell" style="padding-left: 46px;">CHF {{ (int)($payment->amount / 100) }}</td>
                        <td class="amount-cell" style="padding-left: 46px;">CHF {{ (int)($payment->amount / 100) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Nëntotali:</td>
                <td class="amount-cell">CHF {{ (int)($payment->amount / 100) }}</td>
            </tr>
            <tr class="total-row">
                <td>Totali:</td>
                <td class="amount-cell">CHF {{ (int)($payment->amount / 100) }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; font-size: 11px; color: #666; vertical-align: top;">
                        <p style="margin: 0;">Faleminderit për kontributin tuaj! Kjo faturë konfirmon pagesën tuaj.</p>
                        <p style="margin: 0;">E gjeneruar më {{ now()->format('F j, Y \n\ë g:i A T') }}</p>
                    </td>
                    <td style="text-align: right; font-size: 11px; color: #000; vertical-align: top;">
                        <div style="line-height: 1.3;">
                            <span style="color: #1F6E38;">Islamische Verein EN-NUR</span><br>
                            Ziegeleistrasse 2, 8253 Diessenhofen<br>
                            <a href="mailto:info@xhamia-en-nur.ch" style="color: #1F6E38; text-decoration: none;">info@xhamia-en-nur.ch</a> | 
                            <a href="tel:0526541460" style="color: #1F6E38; text-decoration: none;">052 654 14 60</a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html> 