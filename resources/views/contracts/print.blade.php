<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>DELTA - To'lov Cheki</title>
    <!-- Using Inter for better readability on thermal printers -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Inter', sans-serif;
            width: 80mm;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
            -webkit-print-color-adjust: exact;
        }
        .receipt-container {
            border: 1px solid #000;
            padding: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 50mm;
            max-height: 20mm;
            margin-bottom: 5px;
            filter: grayscale(1) contrast(2);
        }
        .company-name {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-details {
            font-size: 11px;
            font-weight: 700;
            margin-top: 3px;
            line-height: 1.2;
        }
        .info-block {
            margin-bottom: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: 700;
        }
        .label { color: #000; text-transform: uppercase; }
        .value { text-align: right; }
        
        .divider {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        .total-row {
            margin-top: 10px;
            padding: 12px 0;
            border-top: 3px solid #000;
            border-bottom: 3px solid #000;
            font-size: 18px;
            font-weight: 900;
            display: flex;
            justify-content: space-between;
        }
        .qr-section {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
        }
        .qr-image { 
            width: 120px; 
            height: 120px; 
            border: 1px solid #000;
            padding: 2px;
        }
        .qr-link { font-size: 10px; margin-top: 5px; font-weight: 900; }
        .footer {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            margin-top: 15px;
            line-height: 1.4;
        }
        #print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #000;
            color: #fff;
            border: 2px solid #fff;
            padding: 12px 24px;
            cursor: pointer;
            z-index: 999;
            font-weight: 900;
            font-family: sans-serif;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        @media print {
            #print-btn { display: none; }
            body { padding: 0mm; margin: 0; }
            .receipt-container { border: none; }
        }
    </style>
</head>
<body>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 800);
        });

        window.onafterprint = function(event) {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
    
    <button id="print-btn" onclick="window.print()">CHOP ETISH (PRINT)</button>

    <div class="receipt-container">
        <div class="header">
            @if(isset($settings['company_logo']))
                <img src="{{ asset($settings['company_logo']) }}" class="logo" alt="Logo">
            @endif
            <div class="company-name text-bold">{{ $settings['company_name'] ?? 'DELTA XIZMAT' }}</div>
            <div class="company-details">
                <b>Elektron Davlat Xizmatlar Markazi</b><br>
                Urgut tumani, Urgut shahar<br>
                Aloqa: +998 (97) 391-11-14
            </div>
        </div>

        <div class="info-block">
            <div class="row">
                <span class="label">SANA/VAQT:</span>
                <span class="value">{{ now()->format('d.m.Y H:i:s') }}</span>
            </div>
            <div class="row">
                <span class="label">BUYURTMA ID:</span>
                <span class="value">#{{ $contract->contract_id }}</span>
            </div>
            <div class="row">
                <span class="label">TO'LOV USULI:</span>
                <span class="value">NAQD (CASH)</span>
            </div>
        </div>

        <div class="info-block">
            <div class="row">
                <span class="label">XIZMAT:</span>
                <span class="value" style="font-size: 14px;">{{ $contract->service->name ?? $contract->custom_type }}</span>
            </div>
            <div class="row">
                <span class="label">KATEGORIYA:</span>
                <span class="value">{{ $contract->custom_type }}</span>
            </div>
            <div class="divider"></div>
            <div class="row">
                <span class="label">MIJOZ:</span>
                <span class="value">{{ $contract->client_name }}</span>
            </div>
            <div class="row">
                <span class="label">TEL:</span>
                <span class="value">{{ $contract->client_phone }}</span>
            </div>
            <div class="row">
                <span class="label">MANZIL:</span>
                <span class="value" style="font-size: 10px;">{{ $contract->client_address }}</span>
            </div>
        </div>

        <div class="info-block" style="border-bottom: none; margin-bottom: 0;">
            <div class="row">
                <span class="label">OPERATOR:</span>
                <span class="value">{{ $contract->user->name }}</span>
            </div>
            <div class="row">
                <span class="label">KASSIR:</span>
                <span class="value">{{ $cashier->name }}</span>
            </div>
        </div>

        <div class="total-row">
            <span>JAMI:</span>
            <span>{{ number_format($contract->amount, 0, '.', ' ') }} UZS</span>
        </div>

        <div class="qr-section">
            @php
                $qrContent = "Delta Xizmat Urgut\nID: " . $contract->contract_id . "\nMijoz: " . $contract->client_name . "\nXizmat: " . ($contract->service->name ?? $contract->custom_type) . "\nSumma: " . number_format($contract->amount, 0, '.', ' ') . " UZS\nTashrifingiz uchun rahmat!";
            @endphp
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ rawurlencode($qrContent) }}&qzone=1" class="qr-image" alt="QR Code">
            <div class="qr-link">BUYURTMA HOLATI: TASDIQLANGAN</div>
        </div>

        <div class="footer">
            Xarid qilingan xizmatlar uchun to'lov qaytarilmaydi.<br>
            <b>Xaridingiz uchun Rahmat!</b>
        </div>
    </div>

</body>
</html>

