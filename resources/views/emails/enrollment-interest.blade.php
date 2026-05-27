<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Novo Pedido de Inscrição</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%); padding: 36px 40px; text-align: center; }
    .header h1 { color: #fff; font-size: 20px; font-weight: 700; margin: 0; }
    .header p { color: rgba(255,255,255,0.8); font-size: 13px; margin: 8px 0 0; }
    .body { padding: 36px 40px; }
    .title { font-size: 17px; font-weight: 700; color: #1a3a5c; margin-bottom: 20px; }
    .detail-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 24px; }
    .detail-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
    .detail-table td:first-child { color: #71717a; width: 38%; }
    .detail-table td:last-child { font-weight: 600; color: #18181b; }
    .badge { display: inline-block; background: #fef9c3; color: #854d0e; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
    .footer { padding: 0 40px 32px; text-align: center; }
    .footer p { font-size: 12px; color: #a1a1aa; margin: 4px 0; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Olsangola Corporation</h1>
      <p>Novo pedido de inscrição recebido</p>
    </div>
    <div class="body">
      <p class="title">📋 Detalhes do Pedido</p>
      <table class="detail-table">
        <tr><td>Nome</td><td>{{ $data['first_name'] }} {{ $data['last_name'] }}</td></tr>
        <tr><td>Email</td><td>{{ $data['email'] }}</td></tr>
        <tr><td>Telefone</td><td>{{ $data['phone'] }}</td></tr>
        @if(!empty($data['bi_number']))
        <tr><td>Nº do BI</td><td>{{ $data['bi_number'] }}</td></tr>
        @endif
        @if(!empty($data['birth_date']))
        <tr><td>Data de Nascimento</td><td>{{ \Carbon\Carbon::parse($data['birth_date'])->format('d/m/Y') }}</td></tr>
        @endif
        @if(!empty($data['gender']))
        @php $genderMap = ['M' => 'Masculino', 'F' => 'Feminino', 'outro' => 'Outro']; @endphp
        <tr><td>Sexo</td><td>{{ $genderMap[$data['gender']] ?? $data['gender'] }}</td></tr>
        @endif
        @if(!empty($data['nationality']))
        <tr><td>Nacionalidade</td><td>{{ $data['nationality'] }}</td></tr>
        @endif
        @if(!empty($data['marital_status']))
        @php $maritalMap = ['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','outro'=>'Outro']; @endphp
        <tr><td>Estado Civil</td><td>{{ $maritalMap[$data['marital_status']] ?? $data['marital_status'] }}</td></tr>
        @endif
        @if(!empty($data['address']))
        <tr><td>Morada</td><td>{{ $data['address'] }}{{ !empty($data['province']) ? ', '.$data['province'] : '' }}</td></tr>
        @endif
        @if(!empty($data['guardian_name']))
        <tr><td>Encarregado</td><td>{{ $data['guardian_name'] }}{{ !empty($data['guardian_phone']) ? ' · '.$data['guardian_phone'] : '' }}</td></tr>
        @endif
        @if(!empty($data['course_name']))
        <tr><td>Curso de Interesse</td><td>{{ $data['course_name'] }}</td></tr>
        @endif
        @if(!empty($data['service_type']))
        <tr><td>Tipo de Serviço</td><td>{{ $data['service_type'] }}</td></tr>
        @endif
        @if(!empty($data['country']))
        <tr><td>País</td><td>{{ $data['country'] }}</td></tr>
        @endif
        <tr><td>Data de Submissão</td><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Estado</td><td><span class="badge">Aguarda Revisão</span></td></tr>
      </table>
      <p style="font-size:14px; color:#3f3f46;">Aceda ao painel de administração para aprovar ou rejeitar este pedido.</p>
    </div>
    <div class="footer">
      <p><strong>Olsangola Corporation</strong> · Luanda, Angola</p>
      <p><a href="mailto:info@olsangola.com" style="color:#2563eb;">info@olsangola.com</a></p>
    </div>
  </div>
</body>
</html>
