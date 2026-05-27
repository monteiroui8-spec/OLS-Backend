<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo pedido de inscrição</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a3a5c; padding: 28px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 18px; letter-spacing: 0.4px; }
        .body { padding: 28px; color: #333333; line-height: 1.6; }
        .body h2 { color: #1a3a5c; margin: 0 0 10px; }
        .item { margin: 10px 0; }
        .label { color: #666666; font-size: 12px; margin-bottom: 2px; }
        .value { font-weight: 600; }
        .footer { padding: 18px 28px; background: #f4f4f4; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>OLS Admin</h1>
    </div>
    <div class="body">
        <h2>Novo pedido de inscrição</h2>
        <div class="item">
            <div class="label">Protocolo</div>
            <div class="value">{{ $protocol }}</div>
        </div>
        <div class="item">
            <div class="label">Aluno</div>
            <div class="value">{{ $user->full_name }} ({{ $user->email }})</div>
        </div>
        <div class="item">
            <div class="label">Telefone</div>
            <div class="value">{{ $user->phone }}</div>
        </div>
        @if($user->bi_number)
        <div class="item">
            <div class="label">Nº do BI</div>
            <div class="value">{{ $user->bi_number }}</div>
        </div>
        @endif
        @if($user->birth_date)
        <div class="item">
            <div class="label">Data de Nascimento</div>
            <div class="value">{{ \Carbon\Carbon::parse($user->birth_date)->format('d/m/Y') }}</div>
        </div>
        @endif
        @if($user->gender)
        @php $genderMap = ['M' => 'Masculino', 'F' => 'Feminino', 'outro' => 'Outro']; @endphp
        <div class="item">
            <div class="label">Sexo</div>
            <div class="value">{{ $genderMap[$user->gender] ?? $user->gender }}</div>
        </div>
        @endif
        @if($user->nationality)
        <div class="item">
            <div class="label">Nacionalidade</div>
            <div class="value">{{ $user->nationality }}</div>
        </div>
        @endif
        @if($user->address)
        <div class="item">
            <div class="label">Morada</div>
            <div class="value">{{ $user->address }}{{ $user->province ? ', '.$user->province : '' }}</div>
        </div>
        @endif
        @if($user->guardian_name)
        <div class="item">
            <div class="label">Encarregado de Educação</div>
            <div class="value">{{ $user->guardian_name }}{{ $user->guardian_phone ? ' · '.$user->guardian_phone : '' }}</div>
        </div>
        @endif
        <div class="item">
            <div class="label">Curso</div>
            <div class="value">{{ $course->title_pt }}</div>
        </div>
        <div class="item">
            <div class="label">Turma</div>
            <div class="value">{{ $classGroup->name }} ({{ $classGroup->year }})</div>
        </div>
        <p style="margin-top: 18px; font-size: 13px; color: #666666;">
            A aprovação/rejeição é feita no painel administrativo.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Olsangola Corporation
    </div>
</div>
</body>
</html>

