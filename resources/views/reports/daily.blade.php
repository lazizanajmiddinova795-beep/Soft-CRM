<!DOCTYPE html>
<html>
<head>
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary-box { background: #f4f4f4; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; }
        .summary-item { margin-bottom: 5px; }
        .summary-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .income { color: green; }
        .expense { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KUNLIK HISOBOT</h1>
        <p>Sana: {{ $date }} | Obsidian OS Academy</p>
    </div>

    <div class="summary-box">
        <div class="summary-item"><span class="summary-label">Jami Kirim:</span> {{ number_format($summary['total_income'], 2) }} UZS</div>
        <div class="summary-item"><span class="summary-label">Jami Chiqim:</span> {{ number_format($summary['total_expense'], 2) }} UZS</div>
        <hr>
        <div class="summary-item"><span class="summary-label">Naqd Pul Qoldig'i:</span> {{ number_format($summary['cash_on_hand'], 2) }} UZS</div>
        <div class="summary-item"><span class="summary-label">Karta/Click Qoldig'i:</span> {{ number_format($summary['card_on_hand'], 2) }} UZS</div>
    </div>

    <h3>O'QUV TO'LOVLARI</h3>
    <table>
        <thead>
            <tr>
                <th>Vaqt</th>
                <th>O'quvchi</th>
                <th>Guruh</th>
                <th>Uslub</th>
                <th>Summa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($academyPayments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('H:i') }}</td>
                <td>{{ $payment->student->name }}</td>
                <td>{{ $payment->group->name }}</td>
                <td>{{ strtoupper($payment->payment_method) }}</td>
                <td>{{ number_format($payment->amount, 0) }} UZS</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>KIRIM/XARAJATLAR (TRANZAKSIYALAR)</h3>
    <table>
        <thead>
            <tr>
                <th>Turi</th>
                <th>Tavsif</th>
                <th>Uslub</th>
                <th>Summa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
            <tr>
                <td class="{{ $tx->type }}">{{ strtoupper($tx->type) }}</td>
                <td>{{ $tx->description }}</td>
                <td>{{ strtoupper($tx->payment_method) }}</td>
                <td>{{ number_format($tx->amount, 0) }} UZS</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
