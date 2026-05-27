<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Pré-inscrição Recebida</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%); padding: 40px 40px 32px; text-align: center; }
    .header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
    .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin: 8px 0 0; }
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
    .steps { margin: 24px 0; }
    .step { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .step-num { background: #2563eb; color: #fff; border-radius: 50%; width: 24px; height: 24px; min-width: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
    .step-text { font-size: 14px; color: #3f3f46; line-height: 1.5; padding-top: 2px; }
    .footer { padding: 0 40px 36px; text-align: center; }
    .footer p { font-size: 12px; color: #a1a1aa; margin: 4px 0; }
    .footer a { color: #2563eb; text-decoration: none; }
    hr { border: none; border-top: 1px solid #e4e4e7; margin: 32px 0; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Olsangola Corporation</h1>
      <p>Escola de Língua Inglesa · Angola</p>
    </div>

    <div class="body">
      <p class="greeting">Olá, {{ $data['first_name'] }}!</p>

      <p class="text">
        Recebemos o seu pedido de pré-inscrição na <strong>Olsangola Corporation</strong>.
        O nosso departamento de admissões irá analisar os seus dados e entrar em contacto consigo em breve.
      </p>

      <div class="protocol-badge">
        <div class="label">Número de Protocolo</div>
        <div class="code">{{ $data['protocol'] }}</div>
      </div>

      <p class="text" style="font-size:13px; color:#71717a; text-align:center; margin-top:-16px; margin-bottom:24px;">
        Guarde este número — será necessário para acompanhar o seu pedido.
      </p>

      <table class="detail-table">
        <tr><td>Nome</td><td>{{ $data['first_name'] }} {{ $data['last_name'] }}</td></tr>
        <tr><td>Email</td><td>{{ $data['email'] }}</td></tr>
        <tr><td>Telefone</td><td>{{ $data['phone'] }}</td></tr>
        @if(!empty($data['bi_number']))
        <tr><td>Nº do BI</td><td>{{ $data['bi_number'] }}</td></tr>
        @endif
        @if(!empty($data['course_name']))
        <tr><td>Curso de Interesse</td><td>{{ $data['course_name'] }}</td></tr>
        @endif
        <tr><td>Estado</td><td>🟡 Aguarda análise</td></tr>
      </table>

      <p class="text" style="font-weight:700; margin-bottom:10px;">Próximos passos:</p>
      <div class="steps">
        <div class="step">
          <div class="step-num">1</div>
          <div class="step-text">A nossa equipa analisa o seu pedido (até 48h úteis).</div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-text">Receberá um email de confirmação com as suas credenciais de acesso e os próximos passos.</div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-text">Efectua o pagamento da inscrição e inicia as suas aulas.</div>
        </div>
      </div>

      <div class="highlight-box">
        <p>
          📞 Precisa de ajuda? Contacte-nos:<br>
          <strong>Email:</strong> <a href="mailto:info@olsangola.com" style="color:#1e40af;">info@olsangola.com</a><br>
          <strong>Telefone/WhatsApp:</strong> +244 9XX XXX XXX
        </p>
      </div>

      <p class="text">Obrigado por escolher a Olsangola Corporation. Estamos ansiosos por recebê-lo(a)!</p>

      <hr>
    </div>

    <div class="footer">
      <p><strong>Olsangola Corporation</strong> · Luanda, Angola</p>
      <p><a href="mailto:info@olsangola.com">info@olsangola.com</a></p>
      <p style="margin-top:10px; font-size:11px; color:#d4d4d8;">
        Este email foi enviado automaticamente. Por favor, não responda directamente a esta mensagem.
      </p>
    </div>
  </div>
</body>
</html>
