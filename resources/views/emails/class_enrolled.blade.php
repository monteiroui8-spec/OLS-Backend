<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $lang === 'en' ? 'Enrolled in Class' : 'Inscrito na Turma' }}</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .header { background: #1d4ed8; padding: 32px 40px; text-align: center; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; }
    .body { padding: 36px 40px; }
    .badge { display: inline-block; background: #dbeafe; color: #1d4ed8; font-size: 13px; font-weight: 700; padding: 4px 14px; border-radius: 999px; margin-bottom: 20px; }
    p { font-size: 15px; line-height: 1.6; color: #3f3f46; margin: 0 0 16px; }
    .class-card { border: 1.5px solid #e4e4e7; border-radius: 10px; padding: 20px 24px; margin: 20px 0; }
    .class-card h3 { margin: 0 0 4px; font-size: 17px; color: #18181b; }
    .class-card .course { font-size: 13px; color: #6b7280; margin: 0 0 14px; }
    .detail-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; font-size: 14px; color: #3f3f46; }
    .detail-row .icon { font-size: 16px; flex-shrink: 0; }
    .schedules { margin-top: 12px; padding-top: 12px; border-top: 1px solid #e4e4e7; }
    .schedules h4 { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin: 0 0 8px; }
    .schedule-item { font-size: 13px; color: #3f3f46; padding: 5px 0; border-bottom: 1px solid #f4f4f5; }
    .schedule-item:last-child { border: none; }
    .cta { display: block; text-align: center; margin: 28px 0; }
    .cta a { background: #1d4ed8; color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 36px; border-radius: 8px; display: inline-block; }
    .footer { background: #f4f4f5; padding: 20px 40px; text-align: center; font-size: 12px; color: #a1a1aa; }
    @php
      $days_pt = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
      $days_en = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    @endphp
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Olsangola Corporation</h1>
  </div>
  <div class="body">
    @php
      $days_pt = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
      $days_en = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
      $courseName = $lang === 'en'
        ? ($class->course?->title_en ?? $class->course?->title_pt)
        : ($class->course?->title_pt ?? $class->course?->title_en);
    @endphp

    @if($lang === 'en')
      <div class="badge">🎓 Class Enrollment Confirmed</div>
      <p>Hello, <strong>{{ $user->full_name }}</strong>!</p>
      <p>You have been successfully enrolled in the following class:</p>
      <div class="class-card">
        <h3>{{ $class->name }}</h3>
        @if($courseName)
          <p class="course">{{ $courseName }}{{ $class->course?->level ? ' · ' . $class->course->level : '' }}</p>
        @endif
        @if($class->teacher?->user)
          <div class="detail-row">
            <span class="icon">👨‍🏫</span>
            <span><strong>Teacher:</strong> {{ $class->teacher->user->full_name }}</span>
          </div>
        @endif
        @if($class->schedules->isNotEmpty())
          <div class="schedules">
            <h4>Schedule</h4>
            @foreach($class->schedules as $s)
              <div class="schedule-item">
                📅 {{ $days_en[$s->day_of_week] ?? '-' }} · {{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}
                @if($s->room) · Room {{ $s->room }}@endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/dashboard">View My Dashboard</a>
      </div>
      <p>Warm regards,<br><strong>Olsangola Team</strong></p>
    @else
      <div class="badge">🎓 Inscrição na Turma Confirmada</div>
      <p>Olá, <strong>{{ $user->full_name }}</strong>!</p>
      <p>Foi inscrito com sucesso na seguinte turma:</p>
      <div class="class-card">
        <h3>{{ $class->name }}</h3>
        @if($courseName)
          <p class="course">{{ $courseName }}{{ $class->course?->level ? ' · ' . $class->course->level : '' }}</p>
        @endif
        @if($class->teacher?->user)
          <div class="detail-row">
            <span class="icon">👨‍🏫</span>
            <span><strong>Professor:</strong> {{ $class->teacher->user->full_name }}</span>
          </div>
        @endif
        @if($class->schedules->isNotEmpty())
          <div class="schedules">
            <h4>Horário</h4>
            @foreach($class->schedules as $s)
              <div class="schedule-item">
                📅 {{ $days_pt[$s->day_of_week] ?? '-' }} · {{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}
                @if($s->room) · Sala {{ $s->room }}@endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
      <div class="cta">
        <a href="{{ config('app.frontend_url', 'https://app.olsangola.com') }}/dashboard">Ver o Meu Painel</a>
      </div>
      <p>Com os melhores cumprimentos,<br><strong>Equipa Olsangola</strong></p>
    @endif
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Olsangola Corporation · Este email foi enviado automaticamente, por favor não responda.
  </div>
</div>
</body>
</html>
