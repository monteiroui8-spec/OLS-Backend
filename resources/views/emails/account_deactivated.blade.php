<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $lang === 'en' ? 'Account Deactivated' : 'Conta Desactivada' }}</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .header { background: #64748b; padding: 32px 40px; text-align: center; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; }
    .body { padding: 36px 40px; }
    .badge { display: inline-block; background: #fee2e2; color: #b91c1c; font-size: 13px; font-weight: 700; padding: 4px 14px; border-radius: 999px; margin-bottom: 20px; }
    p { font-size: 15px; line-height: 1.6; color: #3f3f46; margin: 0 0 16px; }
    .info-box { background: #f8faff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #475569; }
    .footer { background: #f4f4f5; padding: 20px 40px; text-align: center; font-size: 12px; color: #a1a1aa; }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Olsangola Corporation</h1>
  </div>
  <div class="body">
    @if($lang === 'en')
      <div class="badge">⚠ Account Deactivated</div>
      <p>Hello, <strong>{{ $user->full_name }}</strong>.</p>
      <p>Your Olsangola account has been <strong>deactivated</strong>. You will no longer be able to log in until the account is reactivated by an administrator.</p>
      <div class="info-box">
        If you believe this was done in error or have any questions, please contact our support team directly.
      </div>
      <p>Thank you for your understanding.<br><strong>Olsangola Team</strong></p>
    @else
      <div class="badge">⚠ Conta Desactivada</div>
      <p>Olá, <strong>{{ $user->full_name }}</strong>.</p>
      <p>A sua conta na Olsangola foi <strong>desactivada</strong>. Não será possível iniciar sessão até que a conta seja reactivada por um administrador.</p>
      <div class="info-box">
        Se acredita que isto foi feito por engano ou tem alguma dúvida, contacte directamente a nossa equipa de suporte.
      </div>
      <p>Obrigado pela compreensão.<br><strong>Equipa Olsangola</strong></p>
    @endif
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Olsangola Corporation · Este email foi enviado automaticamente, por favor não responda.
  </div>
</div>
</body>
</html>