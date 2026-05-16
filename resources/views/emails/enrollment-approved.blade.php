<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrição aprovada</title>
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
        .pre { white-space: pre-line; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; color: #334155; }
        .footer { padding: 18px 28px; background: #f4f4f4; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Olsangola Corporation</h1>
    </div>
    <div class="body">
        <h2>Bem-vindo(a), {{ $user->first_name }}!</h2>
        <p>A sua inscrição foi aprovada com sucesso. Seguem os seus dados e informações da turma.</p>

        <div style="background-color: #fef9c3; border: 1px solid #fef08a; padding: 12px; border-radius: 6px; margin: 16px 0;">
            <p style="margin: 0; color: #854d0e; font-size: 14px;">
                <strong>Atenção:</strong> A sua conta foi activada, mas neste momento tem acesso apenas ao módulo de Pagamentos. Por favor, aceda à plataforma e envie o comprovativo do seu primeiro pagamento. Assim que for aprovado, todos os módulos ficarão disponíveis.
            </p>
        </div>

        <div class="box">
            <div class="label">Protocolo</div>
            <div class="value">{{ $protocol }}</div>
        </div>

        <div class="box">
            <div class="label">Acesso ao sistema</div>
            <div class="value">Username: {{ $user->username }}</div>
            @if(isset($plainPassword))
                <div class="value">Password: {{ $plainPassword }}</div>
            @endif
            @if($loginUrl)
                <div style="margin-top: 10px;">
                    <a href="{{ $loginUrl }}" class="btn">Aceder à plataforma</a>
                </div>
            @endif
        </div>

        <div class="box">
            <div class="label">Curso</div>
            <div class="value">{{ $course->title_pt }}</div>
        </div>

        <div class="box">
            <div class="label">Turma</div>
            <div class="value">{{ $classGroup->name }} ({{ $classGroup->year }})</div>
            @if($classGroup->teacher && $classGroup->teacher->user)
                <div class="label" style="margin-top: 8px;">Professor</div>
                <div class="value">{{ $classGroup->teacher->user->full_name }}</div>
            @endif
        </div>

        <div class="box">
            <div class="label">Horários</div>
            <div class="pre">{{ $scheduleText ?: '—' }}</div>
        </div>

        <p style="font-size: 13px; color: #666666;">
            Em anexo segue um ficheiro de calendário (.ics) para adicionar ao seu calendário.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Olsangola Corporation
    </div>
</div>
</body>
</html>

