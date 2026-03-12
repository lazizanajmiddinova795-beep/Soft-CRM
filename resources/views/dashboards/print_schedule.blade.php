<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>To'lov Grafigi - {{ $debt->client->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
        }

        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th {
            background: #f4f4f4;
            text-align: left;
            padding: 12px;
            font-size: 11px;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }

        td {
            padding: 12px;
            font-size: 12px;
            border: 1px solid #ddd;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 200px;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">CHOP ETISH (PRINT)</button>

    <div class="header">
        <h1>TO'LOV GRAFIGI SHARTNOMASI</h1>
        <div style="font-size: 12px; margin-top: 5px;">Hujjat raqami: #SCH-{{ $debt->id }}-{{ date('Y') }}</div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Mijoz ma'lumotlari</div>
            <div class="info-value">{{ $debt->client->name }}</div>
            <div class="info-value" style="font-weight: normal;">{{ $debt->client->phone }}</div>
            <div class="info-value" style="font-weight: normal; font-size: 11px;">{{ $debt->client->address }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Qarz ma'lumotlari</div>
            <div class="info-value">Umumiy: {{ number_format($debt->total_amount, 0, '.', ' ') }} UZS</div>
            <div class="info-value">Muddati: {{ $debt->deadline ? date('d.m.Y', strtotime($debt->deadline)) : $debt->installments->count().' oy' }}</div>
            <div class="info-value" style="font-weight: normal; font-size: 10px; color: #666;">Rasmiylashtirilgan sana: {{ $debt->created_at->format('d.m.Y') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">№</th>
                <th>To'lov sanasi</th>
                <th class="amount">To'lov miqdori</th>
                <th style="text-align: center;">Holati</th>
                <th style="width: 150px;">Imzo (Mijoz)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debt->installments->sortBy('due_date') as $index => $inst)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ date('d.m.Y', strtotime($inst->due_date)) }}</td>
                    <td class="amount">{{ number_format($inst->amount, 0, '.', ' ') }} UZS</td>
                    <td style="text-align: center;">{{ $inst->status === 'paid' ? 'TO\'LANGAN' : 'KUTILMOQDA' }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f9f9f9; font-weight: bold;">
                <td colspan="2" style="text-align: right; text-transform: uppercase;">Jami summa:</td>
                <td class="amount">{{ number_format($debt->total_amount, 0, '.', ' ') }} UZS</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 11px; margin-bottom: 40px;">
        <strong>ESLATMA:</strong> To'lovlar belgilangan muddatdan kechikmasligi shart. Kechikkan har bir kun uchun shartnomada belgilangan tartibda penya hisoblanishi mumkin.
    </div>

    <div class="footer">
        <div class="signature">
            Mijoz imzo
        </div>
        <div class="signature">
            Mas'ul xodim imzo
        </div>
    </div>

    <div style="text-align: center; margin-top: 100px; font-size: 9px; color: #888;">
        Hujjat "Obsidian OS" tizimi orqali avtomatik shakllantirildi. <br>
        {{ date('d.m.Y H:i:s') }}
    </div>
</body>
</html>
