<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $lang === 'en' ? 'Your Account Credentials' : 'As Suas Credenciais' }}</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .header { background: #1d4ed8; padding: 32px 40px; text-align: center; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; }
    .body { padding: 36px 40px; }
    p { font-size: 15px; line-height: 1.6; color: #3f3f46; margin: 0 0 16px; }
    .credentials { background: #f8faff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 20px 24px; margin: 24px 0; }
    .credentials p { margin: 0 0 10px; font-size: 14px; }
    .credentials p:last-child { margin: 0; }
    .credentials .label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; font-weight: 700; margin-bottom: 2px; }
    .credentials .value { font-family: 'Courier New', monospace; font-size: 16px; font-weight: 700; color: #1d4ed8; background: #eff6ff; padding: 6px 10px; border-radius: 6px; display: inline-block; }
    .warning { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400e; margin: 20px 0; }
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
      <p>Hello, <strong>{{ $user->full_name }}</strong>!</p>
      <p>Your Olsangola account is ready. Below are your login credentials:</p>
      <div class="credentials">
        <p><span class="label">Email</span><br><span class="value">{{ $user->email }}</span></p>
        <p><span class="label">Password</span><br><span class="value">{{ $plainPassword }}</span></p>
      </div>
      <div class="warning">
        ⚠️ For security reasons, please <strong>change your password</strong> after your first login.
      </div>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/login">Log in Now</a>
      </div>
      <p>If you did not expect this email, please contact us immediately.</p>
      <p>Warm regards,<br><strong>Olsangola Team</strong></p>
    @else
      <p>Olá, <strong>{{ $user->full_name }}</strong>!</p>
      <p>A sua conta na Olsangola está pronta. Abaixo encontra as suas credenciais de acesso:</p>
      <div class="credentials">
        <p><span class="label">Email</span><br><span class="value">{{ $user->email }}</span></p>
        <p><span class="label">Password</span><br><span class="value">{{ $plainPassword }}</span></p>
      </div>
      <div class="warning">
        ⚠️ Por razões de segurança, <strong>altere a sua password</strong> após o primeiro acesso.
      </div>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/login">Entrar Agora</a>
      </div>
      <p>Se não esperava este email, contacte-nos imediatamente.</p>
      <p>Com os melhores cumprimentos,<br><strong>Equipa Olsangola</strong></p>
    @endif
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Olsangola Corporation · Este email foi enviado automaticamente, por favor não responda.
  </div>
</div>
</body>
</html>
