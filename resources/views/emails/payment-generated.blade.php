<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a3a5c; padding: 28px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 18px; letter-spacing: 0.4px; }
        .body { padding: 28px; color: #333333; line-height: 1.7; }
        .body h2 { color: #1a3a5c; margin: 0 0 10px; }
        .btn { display: inline-block; margin-top: 16px; padding: 12px 22px; background: #e8a020; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin: 14px 0; }
        .label { color: #64748b; font-size: 12px; }
        .value { font-weight: 700; }
        .footer { padding: 18px 28px; background: #f4f4f4; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Olsangola Corporation</h1>
    </div>
    <div class="body">
        <h2>Olá, {{ $user->first_name }}!</h2>
        <p>Um novo pagamento foi gerado e encontra-se disponível na sua conta.</p>

        <div class="box">
            <div class="label">Nº da Fatura</div>
            <div class="value">{{ $payment->invoice_number }}</div>
        </div>

        <div class="box">
            <div class="label">Descrição</div>
            <div class="value">{{ $payment->description }}</div>
        </div>

        <div class="box">
            <div class="label">Data de Vencimento</div>
            <div class="value">{{ $payment->due_date->format('d/m/Y') }}</div>
        </div>

        <div class="box">
            <div class="label">Valor a Pagar</div>
            <div class="value">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</div>
        </div>
        
        <p style="margin-top: 20px;">Por favor, aceda à plataforma para consultar os detalhes e enviar o comprovativo de pagamento até à data de vencimento.</p>
        
        <div style="text-align: center;">
            <a href="{{ rtrim(env('FRONTEND_URL', env('APP_URL', '')), '/') }}/login" class="btn">Aceder à Plataforma</a>
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Olsangola Corporation
    </div>
</div>
</body>
</html>