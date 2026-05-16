<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Rejeitado</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a3a5c; padding: 28px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 18px; }
        .body { padding: 28px; color: #333333; line-height: 1.7; }
        .body h2 { color: #e11d48; margin: 0 0 10px; }
        .box { background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; padding: 14px; margin: 14px 0; }
        .label { color: #9f1239; font-size: 12px; font-weight: bold; }
        .value { color: #333; }
        .footer { padding: 18px 28px; background: #f4f4f4; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Olsangola Corporation</h1>
    </div>
    <div class="body">
        <h2>Pagamento Rejeitado</h2>
        <p>Olá, <strong>{{ $user->first_name }}</strong>.</p>
        <p>Informamos que o comprovativo enviado para o pagamento <strong>{{ $payment->invoice_number }}</strong> foi analisado e rejeitado.</p>

        <div class="box">
            <div class="label">Motivo da Rejeição:</div>
            <div class="value">{{ $reason }}</div>
        </div>

        <p>Por favor, aceda à plataforma para verificar os detalhes e submeter um novo comprovativo válido.</p>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ rtrim(env('FRONTEND_URL', env('APP_URL', '')), '/') }}/login" style="display: inline-block; padding: 12px 24px; background: #1a3a5c; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Ir para Pagamentos</a>
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Olsangola Corporation
    </div>
</div>
</body>
</html>
