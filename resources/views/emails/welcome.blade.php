<!DOCTYPE html>
<html lang="{{ $lang ?? 'pt' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ ($lang ?? 'pt') === 'en' ? 'Welcome to Olsangola Corporation' : 'Bem-vindo(a) à Olsangola Corporation' }}</title>
  <style>
    body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%); padding: 40px 40px 32px; text-align: center; }
    .header img { height: 48px; margin-bottom: 16px; }
    .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.3px; }
    .header p { color: rgba(255,255,255,0.82); font-size: 14px; margin: 8px 0 0; }
    .body { padding: 40px; }
    .greeting { font-size: 18px; font-weight: 700; color: #1a3a5c; margin-bottom: 12px; }
    .text { font-size: 15px; line-height: 1.7; color: #3f3f46; margin-bottom: 16px; }
    .highlight-box { background: #eff6ff; border-left: 4px solid #2563eb; border-radius: 6px; padding: 18px 20px; margin: 24px 0; }
    .highlight-box p { margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6; }
    .cta { text-align: center; margin: 32px 0 24px; }
    .cta a { background: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-size: 15px; font-weight: 700; display: inline-block; letter-spacing: 0.2px; }
    .divider { border: none; border-top: 1px solid #e4e4e7; margin: 32px 0; }
    .footer { padding: 0 40px 36px; text-align: center; }
    .footer p { font-size: 12px; color: #a1a1aa; margin: 4px 0; line-height: 1.6; }
    .footer a { color: #2563eb; text-decoration: none; }
    .steps { display: table; width: 100%; margin: 24px 0; }
    .step { display: table-row; }
    .step-num { display: table-cell; width: 36px; height: 36px; background: #2563eb; color: #fff; border-radius: 50%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 14px; padding-bottom: 12px; }
    .step-text { display: table-cell; padding: 0 0 12px 14px; font-size: 14px; color: #3f3f46; vertical-align: middle; }
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
        <p class="greeting">Congratulations, {{ $user->first_name }}! 🎉</p>
        <p class="text">
          You have taken an excellent step towards your professional and personal growth. Your enrollment request at <strong>Olsangola Corporation</strong> has been successfully received.
        </p>

        <div class="highlight-box">
          <p>
            📋 <strong>Your request is being processed.</strong><br>
            Our team is reviewing your registration details. Within <strong>up to 48 business hours</strong> you will receive a confirmation email with all the information needed to start your journey.
          </p>
        </div>

        <p class="text">Here is what happens next:</p>
        <table class="steps" cellpadding="0" cellspacing="0">
          <tr class="step">
            <td class="step-num">1</td>
            <td class="step-text"><strong>Review</strong> — Our team analyzes your registration and assigns you to the appropriate class.</td>
          </tr>
          <tr class="step">
            <td class="step-num">2</td>
            <td class="step-text"><strong>Confirmation</strong> — You receive your access credentials and payment details by email.</td>
          </tr>
          <tr class="step">
            <td class="step-num">3</td>
            <td class="step-text"><strong>Start</strong> — Welcome to the Olsangola Corporation family. Your English journey begins!</td>
          </tr>
        </table>

        <p class="text">If you have any questions in the meantime, do not hesitate to contact us. We are here to help.</p>

        <div class="cta">
          <a href="mailto:info@olsangola.com">Contact Support</a>
        </div>

      @else
        <p class="greeting">Parabéns, {{ $user->first_name }}! 🎉</p>
        <p class="text">
          Deste um passo excelente rumo ao seu crescimento profissional e pessoal. O seu pedido de inscrição na <strong>Olsangola Corporation</strong> foi recebido com sucesso.
        </p>

        <div class="highlight-box">
          <p>
            📋 <strong>O seu pedido está a ser processado.</strong><br>
            A nossa equipa está a rever os seus dados de registo. Nas próximas <strong>48 horas úteis</strong> receberá um email de confirmação com todas as informações necessárias para começar a sua jornada.
          </p>
        </div>

        <p class="text">O que acontece a seguir:</p>
        <table class="steps" cellpadding="0" cellspacing="0">
          <tr class="step">
            <td class="step-num">1</td>
            <td class="step-text"><strong>Análise</strong> — A nossa equipa analisa o seu registo e atribui-lhe a turma mais adequada.</td>
          </tr>
          <tr class="step">
            <td class="step-num">2</td>
            <td class="step-text"><strong>Confirmação</strong> — Receberá as suas credenciais de acesso e os detalhes de pagamento por email.</td>
          </tr>
          <tr class="step">
            <td class="step-num">3</td>
            <td class="step-text"><strong>Início</strong> — Bem-vindo(a) à família Olsangola Corporation. A sua jornada de inglês começa!</td>
          </tr>
        </table>

        <p class="text">Se tiver alguma dúvida entretanto, não hesite em contactar-nos. Estamos aqui para ajudar.</p>

        <div class="cta">
          <a href="mailto:info@olsangola.com">Contactar Suporte</a>
        </div>
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
