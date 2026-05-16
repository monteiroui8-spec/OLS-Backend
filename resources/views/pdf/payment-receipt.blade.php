<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>{{ $payment->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; }
        .header { margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .section { margin-bottom: 10px; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">Recibo de Pagamento</div>
            <div>Fatura: {{ $payment->invoice_number }}</div>
        </div>

        <div class="section">
            <div class="label">Aluno</div>
            <div>{{ $payment->student->user->full_name }}</div>
            <div>{{ $payment->student->user->email }}</div>
        </div>

        <div class="section">
            <div class="label">Detalhes do Pagamento</div>
            <table>
                <tr>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Moeda</th>
                    <th>Estado</th>
                    <th>Vencimento</th>
                    <th>Pago em</th>
                </tr>
                <tr>
                    <td>{{ $payment->description }}</td>
                    <td>{{ number_format($payment->amount, 2, ',', '.') }}</td>
                    <td>{{ $payment->currency }}</td>
                    <td>{{ $payment->status }}</td>
                    <td>{{ $payment->due_date?->format('d/m/Y') }}</td>
                    <td>{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

