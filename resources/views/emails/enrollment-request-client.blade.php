<!DOCTYPE html>
<html lang="{{ $lang ?? 'pt' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ ($lang ?? 'pt') === 'en' ? 'Enrollment Request Received' : 'Pedido de Inscrição Recebido' }}</title>
  <style>
    body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%); padding: 40px 40px 32px; text-align: center; }
    .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.3px; }
    .header p { color: rgba(255,255,255,0.82); font-size: 14px; margin: 8px 0 0; }
    .body { padding: 40px; }
    .greeting { font-size: 18px; font-weight: 700; color: #1a3a5c; margin-bottom: 12px; }
    .text { font-size: 15px; line-height: 1.7; color: #3f3f46; margin-bottom: 16px; }
    .protocol-badge { text-align: center; background: #1a3a5c; color: #fff; border-radius: 8px; padding: 16px 24px; margin: 24px 0; }
    .protocol-badge .label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.75; }
    .protocol-badge .code { font-size: 22px; font-weight: 800; letter-spacing: 2px; margin-top: 4px; font-family: monospace; }
    .detail-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
    .detail-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
    .detail-table td:first-child { color: #71717a; width: 40%; }
    .detail-table td:last-child { font-weight: 600; color: #18181b; }
    .highlight-box { background: #eff6ff; border-left: 4px solid #2563eb; border-radius: 6px; padding: 18px 20px; margin: 24px 0; }
    .highlight-box p { margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6; }
    .divider { border: none; border-top: 1px solid #e4e4e7; margin: 32px 0; }
    .footer { padding: 0 40px 36px; text-align: center; }
    .footer p { font-size: 12px; color: #a1a1aa; margin: 4px 0; line-height: 1.6; }
    .footer a { color: #2563eb; text-decoration: none; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Olsangola Corporation</h1>
      <p>{{ ($lang ?? 'pt') === 'en' ? 'English Language School · Angola' : 'Escola de Língua Inglesa · Angola' }}</p>
    </div>

    <div class="body">
      @if(($lang ?? 'pt') === 'en')
        <p class="greeting">Hello, {{ $user->first_name }}!</p>
        <p class="text">
          We have received your enrollment request. Below you will find your request details for reference. Please keep your protocol number — it will be needed for any follow-up.
        </p>

        <div class="protocol-badge">
          <div class="label">Protocol Number</div>
          <div class="code">{{ $protocol }}</div>
        </div>

        <table class="detail-table">
          <tr><td>Full Name</td><td>{{ $user->full_name }}</td></tr>
          <tr><td>Email</td><td>{{ $user->email }}</td></tr>
          <tr><td>Course</td><td>{{ $course->title_en ?? $course->title_pt ?? '—' }}</td></tr>
          <tr><td>Class Group</td><td>{{ $classGroup->name }} ({{ $classGroup->year }})</td></tr>
          <tr><td>Status</td><td>🟡 Pending review</td></tr>
        </table>

        <div class="highlight-box">
          <p>
            ⏱ <strong>Expected response time: {{ $responseSla ?? '48h' }}</strong><br>
            Our team will review your request and send you confirmation with access credentials and next steps. If you need urgent assistance, please contact us directly.
          </p>
        </div>

        <p class="text">Thank you for choosing Olsangola Corporation. We look forward to welcoming you.</p>

      @else
        <p class="greeting">Olá, {{ $user->first_name }}!</p>
        <p class="text">
          Recebemos o seu pedido de inscrição. Abaixo encontrará os detalhes do seu pedido para consulta. Guarde o número de protocolo — será necessário para qualquer acompanhamento.
        </p>

        <div class="protocol-badge">
          <div class="label">Número de Protocolo</div>
          <div class="code">{{ $protocol }}</div>
        </div>

        <table class="detail-table">
          <tr><td>Nome Completo</td><td>{{ $user->full_name }}</td></tr>
          <tr><td>Email</td><td>{{ $user->email }}</td></tr>
          <tr><td>Curso</td><td>{{ $course->title_pt ?? $course->title_en ?? '—' }}</td></tr>
          <tr><td>Turma</td><td>{{ $classGroup->name }} ({{ $classGroup->year }})</td></tr>
          <tr><td>Estado</td><td>🟡 Aguarda análise</td></tr>
        </table>

        <div class="highlight-box">
          <p>
            ⏱ <strong>Tempo de resposta previsto: {{ $responseSla ?? '48h' }}</strong><br>
            A nossa equipa irá analisar o seu pedido e enviar-lhe a confirmação com as credenciais de acesso e os próximos passos. Se precisar de assistência urgente, contacte-nos directamente.
          </p>
        </div>

        <p class="text">Obrigado por escolher a Olsangola Corporation. Estamos ansiosos por recebê-lo(a).</p>
      @endif

      <hr class="divider">

      <div class="footer">
        <p><strong>Olsangola Corporation</strong></p>
        <p>{{ ($lang ?? 'pt') === 'en' ? 'Luanda, Angola · English Language School' : 'Luanda, Angola · Escola de Língua Inglesa' }}</p>
        <p style="margin-top: 12px;"><a href="mailto:info@olsangola.com">info@olsangola.com</a></p>
        <p style="margin-top: 12px; font-size: 11px; color: #d4d4d8;">
          {{ ($lang ?? 'pt') === 'en'
            ? 'This email was sent automatically. Please do not reply directly to this message.'
            : 'Este email foi enviado automaticamente. Por favor, não responda directamente a esta mensagem.' }}
        </p>
      </div>
    </div>
  </div>
</body>
</html>
