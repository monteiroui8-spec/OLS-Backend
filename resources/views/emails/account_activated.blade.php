<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $lang === 'en' ? 'Account Activated' : 'Conta Activada' }}</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .header { background: #1d4ed8; padding: 32px 40px; text-align: center; }
    .header img { height: 40px; }
    .header h1 { color: #fff; font-size: 22px; margin: 16px 0 0; font-weight: 700; letter-spacing: -.3px; }
    .body { padding: 36px 40px; }
    .badge { display: inline-block; background: #dcfce7; color: #15803d; font-size: 13px; font-weight: 700; padding: 4px 14px; border-radius: 999px; margin-bottom: 20px; }
    p { font-size: 15px; line-height: 1.6; color: #3f3f46; margin: 0 0 16px; }
    .cta { display: block; text-align: center; margin: 28px 0; }
    .cta a { background: #1d4ed8; color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 36px; border-radius: 8px; display: inline-block; }
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
      <div class="badge">✓ Account Activated</div>
      <p>Hello, <strong>{{ $user->full_name }}</strong>!</p>
      <p>Great news — your Olsangola account has been <strong>activated</strong> and you can now access the platform with full privileges.</p>
      <p>Log in now to explore your dashboard, check your schedule and connect with your classmates.</p>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/login">Log in to My Account</a>
      </div>
      <p>If you have any questions, feel free to reach out to our support team.</p>
      <p>Warm regards,<br><strong>Olsangola Team</strong></p>
    @else
      <div class="badge">✓ Conta Activada</div>
      <p>Olá, <strong>{{ $user->full_name }}</strong>!</p>
      <p>Boas notícias — a sua conta na Olsangola foi <strong>activada</strong> e já pode aceder à plataforma com todas as funcionalidades.</p>
      <p>Entre agora para explorar o seu painel, verificar os seus horários e ligar-se aos seus colegas.</p>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/login">Entrar na Minha Conta</a>
      </div>
      <p>Se tiver alguma dúvida, não hesite em contactar a nossa equipa de suporte.</p>
      <p>Com os melhores cumprimentos,<br><strong>Equipa Olsangola</strong></p>
    @endif
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Olsangola Corporation · Este email foi enviado automaticamente, por favor não responda.
  </div>
</div>
</body>
</html>