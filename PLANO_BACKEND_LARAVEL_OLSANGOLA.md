# Plano de Implementação Backend — Laravel
## Olsangola Corporation · Plataforma de Gestão de Ensino de Inglês

**Versão:** 2.0 · Stack Laravel  
**Data:** Abril 2026  
**Frontend:** React 18 + TypeScript + Vite (existente)  
**Backend:** Laravel 11 + PHP 8.3 + PostgreSQL 16

---

## Índice

1. [Visão Geral da Arquitectura](#1-visão-geral-da-arquitectura)
2. [Stack Tecnológico Laravel](#2-stack-tecnológico-laravel)
3. [Estrutura de Pastas do Projecto](#3-estrutura-de-pastas-do-projecto)
4. [Modelo de Base de Dados — Migrations Eloquent](#4-modelo-de-base-de-dados--migrations-eloquent)
5. [Fase 1 — Fundação e Autenticação (Laravel Sanctum)](#fase-1--fundação-e-autenticação-laravel-sanctum)
6. [Fase 2 — Gestão de Utilizadores e Roles (Spatie Permissions)](#fase-2--gestão-de-utilizadores-e-roles-spatie-permissions)
7. [Fase 3 — Cursos, Turmas e Horários](#fase-3--cursos-turmas-e-horários)
8. [Fase 4 — Sistema de Avaliações e Notas](#fase-4--sistema-de-avaliações-e-notas)
9. [Fase 5 — Pagamentos e Finanças](#fase-5--pagamentos-e-finanças)
10. [Fase 6 — Documentos, Ficheiros e Conteúdo](#fase-6--documentos-ficheiros-e-conteúdo)
11. [Fase 7 — Notificações e Comunicação](#fase-7--notificações-e-comunicação)
12. [Fase 8 — Dashboard Analytics e Relatórios](#fase-8--dashboard-analytics-e-relatórios)
13. [Fase 9 — API Pública e Integrações](#fase-9--api-pública-e-integrações)
14. [Fase 10 — Infraestrutura, Segurança e Deploy](#fase-10--infraestrutura-segurança-e-deploy)
15. [Integração Frontend → Backend por Página](#integração-frontend--backend-por-página)
16. [Cronograma Estimado](#cronograma-estimado)

---

## 1. Visão Geral da Arquitectura

```
┌──────────────────────────────────────────────────────────────────┐
│                     FRONTEND (React — Existente)                  │
│   Public Site  |  Student Dashboard  |  Teacher  |  Admin Panel  │
│             Deployed: Vercel / Netlify                            │
└───────────────────────────┬──────────────────────────────────────┘
                            │ HTTPS · REST API · WebSocket
┌───────────────────────────▼──────────────────────────────────────┐
│                   NGINX (Reverse Proxy + SSL)                     │
│              Let's Encrypt · Gzip · Rate Limiting                 │
└───────────────────────────┬──────────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────────┐
│                  LARAVEL 11 API (PHP 8.3)                         │
│                                                                    │
│  ┌───────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │ Sanctum Auth  │  │  REST Controllers │  │  File Storage    │  │
│  │ SPA + Token   │  │  API Resources   │  │  S3 / R2 Driver  │  │
│  └───────────────┘  └──────────────────┘  └──────────────────┘  │
│  ┌───────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │ Spatie Roles  │  │  Laravel Queue   │  │  Laravel Echo    │  │
│  │ & Permissions │  │  Jobs + Schedule │  │  Broadcasting    │  │
│  └───────────────┘  └──────────────────┘  └──────────────────┘  │
└───────────────┬────────────────────────────┬─────────────────────┘
                │                            │
┌───────────────▼────────┐     ┌─────────────▼───────────────────┐
│   PostgreSQL 16         │     │  Redis 7                         │
│   Base de Dados         │     │  Cache · Sessions · Queue        │
│   Principal             │     │  Broadcasting (Pusher-compat)    │
└────────────────────────┘     └─────────────────────────────────┘
                                            │
                             ┌──────────────▼──────────────────────┐
                             │  Cloudflare R2 / AWS S3              │
                             │  Ficheiros, Vídeos, Certificados,    │
                             │  Recibos PDF gerados                 │
                             └─────────────────────────────────────┘
```

### Porquê Laravel para este projecto

- **Eloquent ORM** — relações expressivas (hasMany, belongsToMany, morphMany) que mapeiam directamente o modelo de dados da escola
- **Laravel Sanctum** — autenticação SPA nativa, ideal para o frontend React existente com suporte a tokens de API e cookie-based auth
- **Spatie Laravel Permission** — sistema de roles (Admin/Teacher/Student) e permissões granulares já testado em produção
- **Laravel Queue** — geração assíncrona de PDFs, envio de emails, relatórios financeiros, sem bloquear as respostas da API
- **Laravel Schedule** — cron jobs nativos para geração de mensalidades mensais, lembretes de pagamento, relatórios automáticos
- **API Resources** — transformação consistente dos dados Eloquent em JSON alinhado com as interfaces TypeScript do frontend
- **Laravel Broadcasting** — WebSocket via Laravel Echo para notificações em tempo real no dashboard
- **Pest PHP** — testes elegantes e rápidos para todos os endpoints

---

## 2. Stack Tecnológico Laravel

### Core

```
PHP 8.3
Laravel 11
Composer 2
```

### Base de Dados e Cache

```
PostgreSQL 16          — base de dados principal
Redis 7                — cache, filas de jobs, broadcasting, sessões
Laravel Eloquent ORM   — incluído no Laravel (migrations, seeders, factories)
```

### Autenticação e Autorização

```
Laravel Sanctum 4      — autenticação SPA e API tokens (incluído no Laravel 11)
spatie/laravel-permission 6  — roles e permissões granulares
```

### Packages Adicionais

```
barryvdh/laravel-dompdf         — geração de recibos e certificados em PDF
spatie/laravel-media-library    — gestão de uploads e media (S3/R2 ready)
spatie/laravel-query-builder    — filtros, sorts e includes via query string
spatie/laravel-activitylog      — audit trail automático de acções
league/flysystem-aws-s3-v3      — driver S3 para Cloudflare R2 / AWS S3
beyondcode/laravel-websockets   — servidor WebSocket self-hosted
  (alternativa: Pusher ou Ably como serviço gerido)
maatwebsite/excel               — exportação de relatórios em Excel/CSV
intervention/image-laravel      — resize e processamento de imagens
```

### Testes

```
Pest PHP 3             — framework de testes (wrapper elegante do PHPUnit)
Faker (incluído)       — dados falsos para factories e seeders
```

### Variáveis de Ambiente (`.env`)

```env
# Aplicação
APP_NAME="Olsangola Corporation"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://api.olsangola.ao

# Base de Dados
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=olsangola
DB_USERNAME=ols_user
DB_PASSWORD=senha_segura

# Cache e Filas
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
BROADCAST_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Email (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=re_...
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@olsangola.ao
MAIL_FROM_NAME="Olsangola Corporation"

# Storage S3 / Cloudflare R2
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=olsangola-files
AWS_ENDPOINT=https://...r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true

# Sanctum — domínios do frontend autorizados
SANCTUM_STATEFUL_DOMAINS=olsangola.ao,www.olsangola.ao,localhost:5173

# Frontend URL (para CORS e emails)
FRONTEND_URL=https://olsangola.ao

# Pusher / WebSocket
PUSHER_APP_ID=ols_app
PUSHER_APP_KEY=ols_key
PUSHER_APP_SECRET=ols_secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http

# Taxa de câmbio (opcional — API externa)
EXCHANGE_RATE_API_KEY=...
```

---

## 3. Estrutura de Pastas do Projecto

```
laravel-backend/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── GenerateMonthlyPayments.php   ← cron job dia 1 de cada mês
│   │       └── SendPaymentReminders.php      ← cron job dia 3 de cada mês
│   │
│   ├── Events/
│   │   ├── PaymentConfirmed.php
│   │   ├── ExamPublished.php
│   │   └── GradeReleased.php
│   │
│   ├── Exceptions/
│   │   └── Handler.php                       ← respostas de erro padronizadas
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   │   └── EmailVerificationController.php
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── AdminDashboardController.php
│   │   │   │   │   ├── AdminUserController.php
│   │   │   │   │   ├── AdminClassController.php
│   │   │   │   │   ├── AdminCourseController.php
│   │   │   │   │   ├── AdminPaymentController.php
│   │   │   │   │   ├── AdminExamController.php
│   │   │   │   │   ├── AdminGradeController.php
│   │   │   │   │   ├── AdminReportController.php
│   │   │   │   │   └── AdminSettingController.php
│   │   │   │   ├── Teacher/
│   │   │   │   │   ├── TeacherClassController.php
│   │   │   │   │   ├── TeacherStudentController.php
│   │   │   │   │   ├── TeacherExamController.php
│   │   │   │   │   ├── TeacherGradeController.php
│   │   │   │   │   ├── TeacherAttendanceController.php
│   │   │   │   │   └── TeacherFoulController.php
│   │   │   │   ├── Student/
│   │   │   │   │   ├── StudentDashboardController.php
│   │   │   │   │   ├── StudentCourseController.php
│   │   │   │   │   ├── StudentExamController.php
│   │   │   │   │   ├── StudentGradeController.php
│   │   │   │   │   ├── StudentPaymentController.php
│   │   │   │   │   ├── StudentFoulController.php
│   │   │   │   │   ├── StudentScheduleController.php
│   │   │   │   │   └── StudentAttendanceController.php
│   │   │   │   ├── Public/
│   │   │   │   │   ├── PublicCourseController.php
│   │   │   │   │   ├── ContactController.php
│   │   │   │   │   └── NewsletterController.php
│   │   │   │   ├── DocumentController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   └── ProfileController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureEmailIsVerified.php
│   │   │   ├── SetLocaleFromHeader.php       ← lê Accept-Language: pt | en
│   │   │   ├── SetCurrencyFromHeader.php     ← lê X-Currency: AOA | EUR | USD
│   │   │   └── CheckAccountStatus.php        ← bloqueia se status != ACTIVE
│   │   │
│   │   └── Requests/                         ← Form Requests (validação)
│   │       ├── Auth/
│   │       │   ├── LoginRequest.php
│   │       │   └── RegisterRequest.php
│   │       ├── Admin/
│   │       │   ├── StoreUserRequest.php
│   │       │   ├── StoreClassRequest.php
│   │       │   └── StorePaymentRequest.php
│   │       ├── Teacher/
│   │       │   ├── StoreExamRequest.php
│   │       │   └── StoreGradeRequest.php
│   │       └── Student/
│   │           └── SubmitExamRequest.php
│   │
│   ├── Jobs/
│   │   ├── SendWelcomeEmail.php
│   │   ├── SendPaymentReceiptEmail.php
│   │   ├── SendPaymentReminderEmail.php
│   │   ├── GeneratePaymentReceiptPdf.php
│   │   ├── GenerateCertificatePdf.php
│   │   └── SendExamResultEmail.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── StudentProfile.php
│   │   ├── TeacherProfile.php
│   │   ├── AdminProfile.php
│   │   ├── Course.php
│   │   ├── CourseModule.php
│   │   ├── ModuleContent.php
│   │   ├── StudentContentProgress.php
│   │   ├── ClassGroup.php
│   │   ├── ClassSchedule.php
│   │   ├── Enrollment.php
│   │   ├── Attendance.php
│   │   ├── Exam.php
│   │   ├── Question.php
│   │   ├── ExamAttempt.php
│   │   ├── ExamAnswer.php
│   │   ├── Grade.php
│   │   ├── Payment.php
│   │   ├── Foul.php
│   │   ├── Document.php
│   │   ├── Notification.php
│   │   ├── SystemSetting.php
│   │   ├── NewsletterSubscriber.php
│   │   └── ContactFormSubmission.php
│   │
│   ├── Notifications/
│   │   ├── PaymentDueNotification.php
│   │   ├── PaymentConfirmedNotification.php
│   │   ├── ExamPublishedNotification.php
│   │   └── GradeReleasedNotification.php
│   │
│   ├── Observers/
│   │   ├── PaymentObserver.php               ← ao salvar Payment, dispara jobs
│   │   └── ExamAttemptObserver.php           ← ao submeter, calcula score
│   │
│   ├── Policies/
│   │   ├── UserPolicy.php
│   │   ├── CoursePolicy.php
│   │   ├── ExamPolicy.php
│   │   ├── PaymentPolicy.php
│   │   └── DocumentPolicy.php
│   │
│   └── Http/Resources/                       ← API Resources (transformadores)
│       ├── UserResource.php
│       ├── StudentProfileResource.php
│       ├── CourseResource.php
│       ├── ClassGroupResource.php
│       ├── ExamResource.php
│       ├── GradeResource.php
│       ├── PaymentResource.php
│       └── NotificationResource.php
│
├── database/
│   ├── migrations/                           ← todas as migrations (ver Fase 4)
│   ├── factories/                            ← dados falsos para testes
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php          ← roles e permissões iniciais
│       ├── SystemSettingSeeder.php           ← configurações por defeito
│       ├── CourseSeeder.php                  ← cursos e preços iniciais
│       └── AdminUserSeeder.php              ← utilizador admin inicial
│
├── resources/
│   └── views/
│       ├── emails/                           ← templates Blade para emails
│       │   ├── welcome.blade.php
│       │   ├── payment-due.blade.php
│       │   ├── payment-receipt.blade.php
│       │   └── exam-published.blade.php
│       └── pdf/                              ← templates Blade para PDFs
│           ├── payment-receipt.blade.php
│           └── certificate.blade.php
│
├── routes/
│   └── api.php                               ← todas as rotas da API
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   ├── Teacher/
│   │   └── Student/
│   └── Unit/
│
└── composer.json
```

---

## 4. Modelo de Base de Dados — Migrations Eloquent

Cada migration corresponde a uma tabela. As relações são definidas nos Models Eloquent.

### 4.1 Lista de Migrations (por ordem de criação)

```
2026_01_01_000001_create_users_table.php
2026_01_01_000002_create_password_reset_tokens_table.php  ← incluída no Laravel
2026_01_01_000003_create_personal_access_tokens_table.php ← Sanctum
2026_01_01_000004_create_roles_and_permissions_tables.php ← Spatie
2026_01_01_000005_create_student_profiles_table.php
2026_01_01_000006_create_teacher_profiles_table.php
2026_01_01_000007_create_admin_profiles_table.php
2026_01_01_000008_create_courses_table.php
2026_01_01_000009_create_course_modules_table.php
2026_01_01_000010_create_module_contents_table.php
2026_01_01_000011_create_student_content_progress_table.php
2026_01_01_000012_create_class_groups_table.php
2026_01_01_000013_create_class_schedules_table.php
2026_01_01_000014_create_enrollments_table.php
2026_01_01_000015_create_attendances_table.php
2026_01_01_000016_create_exams_table.php
2026_01_01_000017_create_questions_table.php
2026_01_01_000018_create_exam_attempts_table.php
2026_01_01_000019_create_exam_answers_table.php
2026_01_01_000020_create_grades_table.php
2026_01_01_000021_create_payments_table.php
2026_01_01_000022_create_fouls_table.php
2026_01_01_000023_create_documents_table.php
2026_01_01_000024_create_document_accesses_table.php
2026_01_01_000025_create_notifications_table.php ← já incluída no Laravel
2026_01_01_000026_create_system_settings_table.php
2026_01_01_000027_create_audit_logs_table.php    ← Spatie Activity Log
2026_01_01_000028_create_newsletter_subscribers_table.php
2026_01_01_000029_create_contact_form_submissions_table.php
```

### 4.2 Código das Migrations Principais

```php
// create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('password');
    $table->enum('role', ['admin', 'teacher', 'student']);
    $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
    $table->string('avatar_url')->nullable();
    $table->string('country', 2)->default('AO');
    $table->enum('preferred_language', ['pt', 'en'])->default('pt');
    $table->enum('preferred_currency', ['AOA', 'EUR', 'USD'])->default('AOA');
    $table->boolean('two_factor_enabled')->default(false);
    $table->string('two_factor_secret')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('last_login_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['role', 'status']);
});

// create_student_profiles_table.php
Schema::create('student_profiles', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
    $table->string('student_code')->unique(); // OLS-2026-001
    $table->enum('current_level', ['A1', 'A2', 'B1', 'B2', 'C1'])->nullable();
    $table->date('enrollment_date')->useCurrent();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique('user_id');
});

// create_teacher_profiles_table.php
Schema::create('teacher_profiles', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
    $table->string('teacher_code')->unique(); // TCH-001
    $table->text('bio')->nullable();
    $table->jsonb('certifications')->default('[]');
    $table->jsonb('specializations')->default('[]');
    $table->date('hire_date')->useCurrent();
    $table->decimal('salary', 12, 2)->nullable();
    $table->enum('salary_currency', ['AOA', 'EUR', 'USD'])->default('AOA');
    $table->timestamps();

    $table->unique('user_id');
});

// create_courses_table.php
Schema::create('courses', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('slug')->unique();
    $table->string('title_pt');
    $table->string('title_en');
    $table->text('description_pt');
    $table->text('description_en');
    $table->string('level');                    // Beginner, Intermediate, Advanced
    $table->enum('service_type', [
        'group_daily_communication',
        'individual',
        'kanuca',
        'oil_gas',
        'banking_finance',
        'corporate',
    ]);
    $table->string('duration')->nullable();     // "8 Weeks"
    $table->string('image_url')->nullable();
    $table->string('flyer_url')->nullable();    // flyer OLS_BANNER
    $table->decimal('price_aoa', 12, 2);        // preço base em Kwanzas
    $table->decimal('price_eur', 10, 2);
    $table->decimal('price_usd', 10, 2);
    $table->boolean('is_active')->default(true);
    $table->jsonb('tags')->default('[]');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['service_type', 'is_active']);
});

// create_class_groups_table.php
Schema::create('class_groups', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name');                     // "Turma A - Beginner"
    $table->foreignUlid('course_id')->constrained()->restrictOnDelete();
    $table->foreignUlid('teacher_id')
          ->references('id')->on('teacher_profiles')
          ->restrictOnDelete();
    $table->year('year');
    $table->unsignedSmallInteger('capacity')->default(6); // regra OLS: máx 6
    $table->string('room')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['course_id', 'year']);
    $table->index('teacher_id');
});

// create_enrollments_table.php
Schema::create('enrollments', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('student_id')
          ->references('id')->on('student_profiles')
          ->restrictOnDelete();
    $table->foreignUlid('course_id')->constrained()->restrictOnDelete();
    $table->foreignUlid('class_group_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->enum('status', ['active', 'completed', 'suspended', 'cancelled'])
          ->default('active');
    $table->unsignedTinyInteger('progress_pct')->default(0); // 0-100
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['student_id', 'class_group_id']);
    $table->index('student_id');
    $table->index('class_group_id');
});

// create_exams_table.php
Schema::create('exams', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('title');
    $table->foreignUlid('class_group_id')
          ->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('course_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('teacher_id')
          ->references('id')->on('teacher_profiles')
          ->restrictOnDelete();
    $table->unsignedSmallInteger('duration');   // minutos
    $table->unsignedTinyInteger('max_attempts')->default(1);
    $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
    $table->timestamp('due_date')->nullable();
    $table->unsignedSmallInteger('total_points')->default(100);
    $table->unsignedTinyInteger('pass_score')->default(50);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['class_group_id', 'status']);
});

// create_questions_table.php
Schema::create('questions', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('exam_id')->constrained()->cascadeOnDelete();
    $table->text('text');
    $table->enum('type', ['multiple_choice', 'true_false', 'open_text']);
    $table->jsonb('options')->default('[]');    // ["Went", "Gone", "Goed"]
    $table->unsignedTinyInteger('correct');     // índice da opção correcta
    $table->unsignedTinyInteger('points')->default(10);
    $table->unsignedSmallInteger('order');
    $table->timestamps();

    $table->index('exam_id');
});

// create_exam_attempts_table.php
Schema::create('exam_attempts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('exam_id')->constrained()->restrictOnDelete();
    $table->foreignUlid('student_id')
          ->references('id')->on('student_profiles')
          ->restrictOnDelete();
    $table->enum('status', ['in_progress', 'completed', 'expired'])
          ->default('in_progress');
    $table->timestamp('started_at')->useCurrent();
    $table->timestamp('submitted_at')->nullable();
    $table->decimal('score', 5, 2)->nullable();   // 0.00 – 100.00
    $table->boolean('passed')->nullable();
    $table->timestamps();

    $table->index('student_id');
    $table->index('exam_id');
});

// create_payments_table.php
Schema::create('payments', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('student_id')
          ->references('id')->on('student_profiles')
          ->restrictOnDelete();
    $table->foreignUlid('course_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('enrollment_id')->nullable()->constrained()->nullOnDelete();
    $table->string('description');             // "March 2026 Tuition"
    $table->decimal('amount', 12, 2);
    $table->enum('currency', ['AOA', 'EUR', 'USD'])->default('AOA');
    $table->decimal('amount_aoa', 12, 2)->nullable(); // valor convertido para AOA
    $table->date('due_date');
    $table->timestamp('paid_at')->nullable();
    $table->enum('status', ['paid', 'pending', 'overdue', 'cancelled', 'refunded'])
          ->default('pending');
    $table->enum('method', [
        'credit_card', 'bank_transfer', 'mpesa', 'multicaixa', 'cash', 'other'
    ])->nullable();
    $table->string('transaction_ref')->nullable();
    $table->string('receipt_url')->nullable();
    $table->string('invoice_number')->unique(); // INV-2026-001
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['student_id', 'status']);
    $table->index(['due_date', 'status']);
});

// create_fouls_table.php
Schema::create('fouls', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('student_id')
          ->references('id')->on('student_profiles')
          ->restrictOnDelete();
    $table->foreignUlid('course_id')->nullable()->constrained()->nullOnDelete();
    $table->string('description');
    $table->decimal('amount', 10, 2);
    $table->enum('currency', ['AOA', 'EUR', 'USD'])->default('AOA');
    $table->enum('status', ['pending', 'overdue', 'paid'])->default('pending');
    $table->timestamp('paid_at')->nullable();
    $table->date('due_date')->nullable();
    $table->foreignUlid('created_by')->references('id')->on('users');
    $table->timestamps();

    $table->index(['student_id', 'status']);
});
```

### 4.3 Models Eloquent com Relações

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, SoftDeletes, HasRoles; // HasRoles = Spatie

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password',
        'role', 'status', 'avatar_url', 'country',
        'preferred_language', 'preferred_currency',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relações
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function notifications()
    {
        return $this->hasMany(DatabaseNotification::class, 'notifiable_id');
    }
}

// app/Models/ClassGroup.php
class ClassGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'course_id', 'teacher_id', 'year', 'capacity', 'room', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'enrollments')
                    ->withPivot('status', 'progress_pct', 'start_date')
                    ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Verifica se a turma tem capacidade (regra OLS: máx 6)
    public function hasCapacity(): bool
    {
        return $this->students()->wherePivot('status', 'active')->count() < $this->capacity;
    }

    // Progresso médio da turma
    public function getAverageProgressAttribute(): float
    {
        return $this->students()->avg('enrollments.progress_pct') ?? 0;
    }
}

// app/Models/Payment.php
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'course_id', 'enrollment_id', 'description',
        'amount', 'currency', 'amount_aoa', 'due_date', 'paid_at',
        'status', 'method', 'transaction_ref', 'receipt_url',
        'invoice_number', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Gerar invoice_number automático antes de criar
        static::creating(function (Payment $payment) {
            $payment->invoice_number = self::generateInvoiceNumber();
        });
    }

    private static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $last = self::whereYear('created_at', $year)->count() + 1;
        return "INV-{$year}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
}

// app/Models/Exam.php
class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'class_group_id', 'course_id', 'teacher_id',
        'duration', 'max_attempts', 'status', 'due_date',
        'total_points', 'pass_score',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function getAverageScoreAttribute(): ?float
    {
        return $this->attempts()
                    ->where('status', 'completed')
                    ->avg('score');
    }
}
```

---

## Fase 1 — Fundação e Autenticação (Laravel Sanctum)

**Duração estimada:** 1 semana  
**Prioridade:** Crítica

### 1.1 Instalação e Setup

```bash
# Criar projecto
composer create-project laravel/laravel olsangola-api

# Packages essenciais
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require spatie/laravel-query-builder
composer require spatie/laravel-media-library
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
composer require intervention/image-laravel
composer require beyondcode/laravel-websockets

# Dev packages
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev laravel/pint  # code style fixer

# Publicar configs
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# Configurar Sanctum para SPA (adicionar ao Kernel)
# config/sanctum.php → stateful domains
```

### 1.2 Configuração CORS para o Frontend React

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'https://olsangola.ao',
        'https://www.olsangola.ao',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // necessário para Sanctum SPA
];
```

### 1.3 Rotas de Autenticação

```php
// routes/api.php

Route::prefix('v1')->group(function () {

    // ── Públicas (sem autenticação) ──────────────────
    Route::prefix('auth')->group(function () {
        Route::post('register',         [AuthController::class, 'register']);
        Route::post('login',            [AuthController::class, 'login']);
        Route::post('forgot-password',  [ForgotPasswordController::class, 'send']);
        Route::post('reset-password',   [ForgotPasswordController::class, 'reset']);
        Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
             ->name('verification.verify');
    });

    Route::prefix('public')->group(function () {
        Route::get('courses',                  [PublicCourseController::class, 'index']);
        Route::get('courses/{slug}',           [PublicCourseController::class, 'show']);
        Route::get('prices',                   [PublicCourseController::class, 'prices']);
        Route::get('stats',                    [PublicCourseController::class, 'stats']);
        Route::get('exchange-rates',           [PublicCourseController::class, 'rates']);
        Route::post('enrollment-request',      [PublicCourseController::class, 'enrollmentRequest']);
        Route::post('contact',                 [ContactController::class, 'store']);
        Route::post('newsletter',              [NewsletterController::class, 'subscribe']);
    });

    // ── Autenticadas ─────────────────────────────────
    Route::middleware(['auth:sanctum', 'account.active'])->group(function () {

        Route::post('auth/logout',    [AuthController::class, 'logout']);
        Route::post('auth/refresh',   [AuthController::class, 'refresh']);

        // Perfil do utilizador autenticado
        Route::get('me',              [ProfileController::class, 'show']);
        Route::patch('me',            [ProfileController::class, 'update']);
        Route::post('me/avatar',      [ProfileController::class, 'avatar']);
        Route::patch('me/password',   [ProfileController::class, 'password']);
        Route::get('me/summary',      [ProfileController::class, 'summary']);

        // Notificações
        Route::get('notifications',              [NotificationController::class, 'index']);
        Route::patch('notifications/{id}/read',  [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all',    [NotificationController::class, 'readAll']);

        // Documentos (acesso partilhado entre roles)
        Route::get('documents',               [DocumentController::class, 'index']);
        Route::post('documents/upload',       [DocumentController::class, 'upload']);
        Route::get('documents/{id}/download', [DocumentController::class, 'download']);
        Route::delete('documents/{id}',       [DocumentController::class, 'destroy']);

        // ── ADMIN ────────────────────────────────────
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('dashboard',                 [AdminDashboardController::class, 'index']);
            Route::apiResource('users',             AdminUserController::class);
            Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
            Route::apiResource('courses',           AdminCourseController::class);
            Route::apiResource('classes',           AdminClassController::class);
            Route::post('classes/{class}/students', [AdminClassController::class, 'addStudent']);
            Route::delete('classes/{class}/students/{student}', [AdminClassController::class, 'removeStudent']);
            Route::post('classes/{class}/schedules',[AdminClassController::class, 'addSchedule']);
            Route::apiResource('payments',          AdminPaymentController::class);
            Route::get('payments/export',           [AdminPaymentController::class, 'export']);
            Route::apiResource('exams',             AdminExamController::class);
            Route::apiResource('grades',            AdminGradeController::class);
            Route::get('reports/students',          [AdminReportController::class, 'students']);
            Route::get('reports/payments',          [AdminReportController::class, 'payments']);
            Route::get('reports/attendance',        [AdminReportController::class, 'attendance']);
            Route::get('settings',                  [AdminSettingController::class, 'index']);
            Route::patch('settings',                [AdminSettingController::class, 'update']);
        });

        // ── TEACHER ──────────────────────────────────
        Route::prefix('teacher')->middleware('role:teacher|admin')->group(function () {
            Route::get('classes',                   [TeacherClassController::class, 'index']);
            Route::get('classes/{class}',           [TeacherClassController::class, 'show']);
            Route::get('students',                  [TeacherStudentController::class, 'index']);
            Route::get('students/{student}',        [TeacherStudentController::class, 'show']);
            Route::apiResource('exams',             TeacherExamController::class);
            Route::post('exams/{exam}/publish',     [TeacherExamController::class, 'publish']);
            Route::post('exams/{exam}/close',       [TeacherExamController::class, 'close']);
            Route::get('exams/{exam}/stats',        [TeacherExamController::class, 'stats']);
            Route::get('grades',                    [TeacherGradeController::class, 'index']);
            Route::post('grades',                   [TeacherGradeController::class, 'store']);
            Route::patch('grades/{grade}',          [TeacherGradeController::class, 'update']);
            Route::post('attendance',               [TeacherAttendanceController::class, 'store']);
            Route::get('attendance',                [TeacherAttendanceController::class, 'index']);
            Route::post('fouls',                    [TeacherFoulController::class, 'store']);
            Route::get('schedule',                  [TeacherClassController::class, 'schedule']);
        });

        // ── STUDENT ──────────────────────────────────
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('dashboard',                 [StudentDashboardController::class, 'index']);
            Route::get('courses',                   [StudentCourseController::class, 'index']);
            Route::post('courses/{enrollment}/progress', [StudentCourseController::class, 'progress']);
            Route::get('schedule',                  [StudentScheduleController::class, 'index']);
            Route::get('exams',                     [StudentExamController::class, 'index']);
            Route::post('exams/{exam}/start',       [StudentExamController::class, 'start']);
            Route::post('exams/attempts/{attempt}/submit', [StudentExamController::class, 'submit']);
            Route::get('exams/attempts/{attempt}',  [StudentExamController::class, 'result']);
            Route::get('grades',                    [StudentGradeController::class, 'index']);
            Route::get('payments',                  [StudentPaymentController::class, 'index']);
            Route::get('payments/{payment}/receipt',[StudentPaymentController::class, 'receipt']);
            Route::get('fouls',                     [StudentFoulController::class, 'index']);
            Route::post('fouls/pay',                [StudentFoulController::class, 'pay']);
            Route::get('attendance',                [StudentAttendanceController::class, 'index']);
        });
    });
});
```

### 1.4 AuthController

```php
// app/Http/Controllers/Api/Auth/AuthController.php

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name'          => $request->first_name,
                'last_name'           => $request->last_name,
                'email'               => $request->email,
                'phone'               => $request->phone,
                'password'            => Hash::make($request->password),
                'role'                => $request->role ?? 'student',
                'status'              => 'pending',
                'country'             => $request->country ?? 'AO',
                'preferred_language'  => $request->language ?? 'pt',
                'preferred_currency'  => $request->currency ?? 'AOA',
            ]);

            // Atribuir role via Spatie
            $user->assignRole($user->role);

            // Criar perfil específico
            if ($user->role === 'student') {
                $code = $this->generateStudentCode();
                StudentProfile::create([
                    'user_id'      => $user->id,
                    'student_code' => $code,
                ]);
            } elseif ($user->role === 'teacher') {
                $code = $this->generateTeacherCode();
                TeacherProfile::create([
                    'user_id'      => $user->id,
                    'teacher_code' => $code,
                ]);
            }

            // Enviar email de verificação (job assíncrono)
            $user->sendEmailVerificationNotification();

            // Registar em audit log (Spatie Activity Log)
            activity('auth')->performedOn($user)->log('User registered');

            DB::commit();

            return response()->json([
                'message' => 'Conta criada. Verifica o teu email.',
                'user_id' => $user->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => ['code' => 'INVALID_CREDENTIALS', 'message' => 'Email ou password incorrectos.']
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'error' => ['code' => 'ACCOUNT_INACTIVE', 'message' => 'Conta não activa. Verifica o teu email ou contacta o suporte.']
            ], 403);
        }

        // Revogar tokens anteriores (single session por defeito)
        $user->tokens()->delete();

        // Criar token Sanctum
        $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        // Actualizar último login
        $user->update(['last_login_at' => now()]);

        activity('auth')->performedOn($user)->log('User logged in');

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => new UserResource($user->load('studentProfile', 'teacherProfile')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function generateStudentCode(): string
    {
        $year  = now()->year;
        $count = StudentProfile::whereYear('created_at', $year)->count() + 1;
        return 'OLS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function generateTeacherCode(): string
    {
        $count = TeacherProfile::count() + 1;
        return 'TCH-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
```

### 1.5 Form Requests (Validação)

```php
// app/Http/Requests/Auth/RegisterRequest.php
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email:rfc,dns', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role'       => ['nullable', 'in:student,teacher'],
            'country'    => ['nullable', 'string', 'size:2'],
            'language'   => ['nullable', 'in:pt,en'],
            'currency'   => ['nullable', 'in:AOA,EUR,USD'],
            'service_type' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'   => 'Este email já está registado.',
            'password.min'   => 'A password deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As passwords não coincidem.',
        ];
    }
}
```

### 1.6 API Resource (Transformador de Resposta)

```php
// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'firstName'           => $this->first_name,
            'lastName'            => $this->last_name,
            'fullName'            => $this->full_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'role'                => $this->role,
            'status'              => $this->status,
            'avatarUrl'           => $this->avatar_url,
            'country'             => $this->country,
            'preferredLanguage'   => $this->preferred_language,
            'preferredCurrency'   => $this->preferred_currency,
            'emailVerifiedAt'     => $this->email_verified_at,
            'lastLoginAt'         => $this->last_login_at,
            'createdAt'           => $this->created_at,
            // Perfil condicional — só carregado se existir (eager loaded)
            'studentProfile'      => $this->whenLoaded('studentProfile',
                fn() => new StudentProfileResource($this->studentProfile)
            ),
            'teacherProfile'      => $this->whenLoaded('teacherProfile',
                fn() => new TeacherProfileResource($this->teacherProfile)
            ),
        ];
    }
}
```

---

## Fase 2 — Gestão de Utilizadores e Roles (Spatie Permissions)

**Duração estimada:** 1 semana  
**Prioridade:** Alta  
**Interfaces que alimenta:** `AdminUsers.tsx`, `DashboardLayout.tsx`

### 2.1 Roles e Permissões Iniciais (Seeder)

```php
// database/seeders/RolePermissionSeeder.php
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Criar permissões granulares
        $permissions = [
            'manage_users', 'view_users',
            'manage_courses', 'view_courses',
            'manage_classes', 'view_classes',
            'manage_payments', 'view_payments',
            'manage_exams', 'view_exams',
            'manage_grades', 'view_grades',
            'manage_documents', 'view_documents',
            'manage_settings',
            'view_reports',
            'manage_fouls',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        // Criar roles e atribuir permissões
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->syncPermissions(Permission::all());

        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'sanctum']);
        $teacher->syncPermissions([
            'view_classes', 'manage_exams', 'view_exams',
            'manage_grades', 'view_grades',
            'view_documents', 'manage_documents',
            'manage_fouls',
        ]);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'sanctum']);
        $student->syncPermissions([
            'view_courses', 'view_exams', 'view_grades',
            'view_payments', 'view_documents',
        ]);
    }
}
```

### 2.2 AdminUserController

```php
// app/Http/Controllers/Api/Admin/AdminUserController.php
class AdminUserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(['role', 'status', Filter::scope('search')])
            ->allowedSorts(['first_name', 'email', 'created_at', 'last_login_at'])
            ->with(['studentProfile', 'teacherProfile'])
            ->withTrashed()   // Admin pode ver soft deleted
            ->paginate($request->get('limit', 20));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        // ... igual ao register mas com mais campos
        // Admin pode criar qualquer role, incluindo outros admins
        $user = User::create([...]);
        $user->assignRole($request->role);

        // Criar perfil
        // Opcionalmente enviar email de convite com password temporária

        activity()->performedOn($user)->causedBy(auth()->user())->log('Admin created user');

        return response()->json(new UserResource($user), 201);
    }

    public function update(StoreUserRequest $request, User $user): JsonResponse
    {
        $before = $user->toArray();
        $user->update($request->validated());

        if ($request->has('role') && $request->role !== $user->role) {
            $user->syncRoles([$request->role]);
        }

        activity()->performedOn($user)->causedBy(auth()->user())
                  ->withProperties(['before' => $before, 'after' => $user->fresh()->toArray()])
                  ->log('Admin updated user');

        return response()->json(new UserResource($user->fresh()));
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete(); // SoftDelete
        activity()->performedOn($user)->causedBy(auth()->user())->log('Admin deleted user');
        return response()->json(null, 204);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $newPassword = $request->new_password ?? Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);
        $user->tokens()->delete(); // Revogar sessões activas

        // Notificar utilizador com a nova password
        Mail::to($user->email)->queue(new PasswordResetByAdmin($user, $newPassword));

        return response()->json(['message' => 'Password redefinida.']);
    }
}
```

---

## Fase 3 — Cursos, Turmas e Horários

**Duração estimada:** 1.5 semanas  
**Prioridade:** Alta  
**Interfaces que alimenta:** `ServicesPage.tsx`, `CoursesPage.tsx`, `AdminClasses.tsx`, `TeacherClasses.tsx`, `StudentCourses.tsx`, `StudentSchedule.tsx`

### 3.1 PublicCourseController (sem autenticação, com cache)

```php
class PublicCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'pt');

        $courses = Cache::remember("public_courses_{$lang}", now()->addMinutes(30), function () use ($lang) {
            return Course::active()
                ->orderBy('service_type')
                ->get()
                ->map(fn($c) => [
                    'id'          => $c->id,
                    'slug'        => $c->slug,
                    'title'       => $lang === 'en' ? $c->title_en : $c->title_pt,
                    'description' => $lang === 'en' ? $c->description_en : $c->description_pt,
                    'level'       => $c->level,
                    'serviceType' => $c->service_type,
                    'flyerUrl'    => $c->flyer_url,
                    'prices'      => ['AOA' => $c->price_aoa, 'EUR' => $c->price_eur, 'USD' => $c->price_usd],
                ]);
        });

        return response()->json(['data' => $courses]);
    }

    public function stats(): JsonResponse
    {
        // Cache por 1h — estatísticas da HomePage
        return Cache::remember('public_stats', now()->addHour(), function () {
            return response()->json([
                'studentsFormed'   => StudentProfile::count(),
                'approvalRate'     => 98,
                'averageRating'    => 5.0,
                'activeClasses'    => ClassGroup::active()->count(),
            ]);
        });
    }

    public function rates(): JsonResponse
    {
        // Taxa de câmbio — cache de 1h
        return Cache::remember('exchange_rates', now()->addHour(), function () {
            // Chamar API externa (exchangerate-api.com)
            // ou usar valores fixos se não houver API disponível
            return response()->json([
                'AOA_EUR' => 0.001,
                'AOA_USD' => 0.001,
                'updatedAt' => now(),
            ]);
        });
    }
}
```

### 3.2 AdminClassController — Validação de Capacidade

```php
class AdminClassController extends Controller
{
    public function store(StoreClassRequest $request): JsonResponse
    {
        // Validar capacidade (regra OLS: máx 6)
        if ($request->capacity > 6) {
            return response()->json([
                'error' => ['code' => 'CAPACITY_EXCEEDED', 'message' => 'A Olsangola Corporation tem limite máximo de 6 alunos por turma.']
            ], 422);
        }

        $class = DB::transaction(function () use ($request) {
            $class = ClassGroup::create($request->safe()->except('schedules'));

            // Criar horários
            if ($request->has('schedules')) {
                foreach ($request->schedules as $schedule) {
                    // Verificar conflito de horário do professor
                    $conflict = ClassSchedule::whereHas('classGroup', fn($q) =>
                        $q->where('teacher_id', $request->teacher_id)
                    )->where('day_of_week', $schedule['day_of_week'])
                     ->where(function ($q) use ($schedule) {
                         $q->whereBetween('start_time', [$schedule['start_time'], $schedule['end_time']])
                           ->orWhereBetween('end_time', [$schedule['start_time'], $schedule['end_time']]);
                     })->exists();

                    if ($conflict) {
                        throw new \Exception('Conflito de horário para este professor.');
                    }

                    $class->schedules()->create($schedule);
                }
            }

            return $class;
        });

        return response()->json(new ClassGroupResource($class->load('course', 'teacher.user', 'schedules')), 201);
    }

    public function addStudent(Request $request, ClassGroup $class): JsonResponse
    {
        // Verificar capacidade
        if (!$class->hasCapacity()) {
            return response()->json([
                'error' => ['code' => 'CLASS_FULL', 'message' => "Esta turma já atingiu o limite de {$class->capacity} alunos."]
            ], 422);
        }

        $student = StudentProfile::findOrFail($request->student_id);

        // Verificar se já está inscrito
        if ($class->students()->where('student_id', $student->id)->exists()) {
            return response()->json([
                'error' => ['code' => 'ALREADY_ENROLLED', 'message' => 'O aluno já está inscrito nesta turma.']
            ], 422);
        }

        Enrollment::create([
            'student_id'     => $student->id,
            'course_id'      => $class->course_id,
            'class_group_id' => $class->id,
            'start_date'     => now(),
        ]);

        // Notificar o aluno
        $student->user->notify(new EnrolledInClass($class));

        return response()->json(['message' => 'Aluno inscrito com sucesso.']);
    }
}
```

### 3.3 StudentScheduleController

```php
class StudentScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student  = $request->user()->studentProfile;
        $weekStart = $request->get('week')
            ? Carbon::parse($request->week)->startOfWeek()
            : Carbon::now()->startOfWeek();
        $weekEnd  = $weekStart->copy()->endOfWeek();

        // Buscar todas as turmas activas do aluno
        $enrollments = Enrollment::with([
            'classGroup.course',
            'classGroup.teacher.user',
            'classGroup.schedules',
        ])->where('student_id', $student->id)
          ->where('status', 'active')
          ->get();

        $classes = [];
        foreach ($enrollments as $enrollment) {
            $classGroup = $enrollment->classGroup;
            foreach ($classGroup->schedules as $schedule) {
                // Calcular a data desta semana para este dia da semana
                $date = $weekStart->copy()->addDays($schedule->day_of_week);
                if ($date->between($weekStart, $weekEnd)) {
                    $classes[] = [
                        'id'        => $classGroup->id . '-' . $date->format('Y-m-d'),
                        'title'     => $classGroup->course->{'title_' . app()->getLocale()},
                        'day'       => $date->translatedFormat('l'),
                        'date'      => $date->format('M d'),
                        'startTime' => $schedule->start_time,
                        'endTime'   => $schedule->end_time,
                        'teacher'   => ['name' => $classGroup->teacher->user->full_name],
                        'room'      => $schedule->room ?? $classGroup->room,
                        'course'    => ['name' => $classGroup->name, 'level' => $classGroup->course->level],
                    ];
                }
            }
        }

        return response()->json([
            'classes' => $classes,
            'stats'   => [
                'classesThisWeek' => count($classes),
                'totalHours'      => collect($classes)->sum(fn($c) => $this->timeDiff($c['startTime'], $c['endTime'])),
            ]
        ]);
    }
}
```

---

## Fase 4 — Sistema de Avaliações e Notas

**Duração estimada:** 1.5 semanas  
**Prioridade:** Alta  
**Interfaces que alimenta:** `StudentExams.tsx`, `StudentGrades.tsx`, `TeacherExams.tsx`

### 4.1 TeacherExamController — CRUD de Exames

```php
class TeacherExamController extends Controller
{
    public function store(StoreExamRequest $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        // Verificar que a turma pertence ao professor
        if ($request->class_group_id) {
            $class = ClassGroup::where('id', $request->class_group_id)
                               ->where('teacher_id', $teacher->id)
                               ->firstOrFail();
        }

        $exam = DB::transaction(function () use ($request, $teacher) {
            $exam = Exam::create([
                'title'          => $request->title,
                'class_group_id' => $request->class_group_id,
                'course_id'      => $request->course_id,
                'teacher_id'     => $teacher->id,
                'duration'       => $request->duration,
                'max_attempts'   => $request->max_attempts ?? 1,
                'status'         => 'draft',
                'due_date'       => $request->due_date,
                'pass_score'     => $request->pass_score ?? 50,
            ]);

            // Criar perguntas
            foreach ($request->questions as $index => $q) {
                $exam->questions()->create([
                    'text'    => $q['text'],
                    'type'    => $q['type'],
                    'options' => $q['options'],
                    'correct' => $q['correct'],
                    'points'  => $q['points'] ?? 10,
                    'order'   => $index + 1,
                ]);
            }

            return $exam;
        });

        return response()->json(new ExamResource($exam->load('questions')), 201);
    }

    public function publish(Exam $exam): JsonResponse
    {
        $this->authorize('update', $exam);

        if ($exam->questions()->count() === 0) {
            return response()->json([
                'error' => ['code' => 'NO_QUESTIONS', 'message' => 'O exame precisa de ter pelo menos uma pergunta.']
            ], 422);
        }

        $exam->update(['status' => 'published']);

        // Notificar todos os alunos da turma
        if ($exam->classGroup) {
            $exam->classGroup->students->each(function ($student) use ($exam) {
                $student->user->notify(new ExamPublishedNotification($exam));
            });
        }

        return response()->json(new ExamResource($exam));
    }

    public function stats(Exam $exam): JsonResponse
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
                               ->where('status', 'completed')
                               ->with('student.user')
                               ->get();

        $distribution = [
            '0-50'   => $attempts->where('score', '<', 50)->count(),
            '50-70'  => $attempts->whereBetween('score', [50, 69])->count(),
            '70-90'  => $attempts->whereBetween('score', [70, 89])->count(),
            '90-100' => $attempts->where('score', '>=', 90)->count(),
        ];

        return response()->json([
            'totalStudents'     => $exam->classGroup?->students()->count() ?? 0,
            'completedStudents' => $attempts->count(),
            'avgScore'          => round($attempts->avg('score'), 1),
            'passRate'          => $attempts->count() > 0
                ? round($attempts->where('passed', true)->count() / $attempts->count() * 100)
                : 0,
            'distribution'      => $distribution,
            'studentResults'    => $attempts->map(fn($a) => [
                'student'     => ['name' => $a->student->user->full_name, 'code' => $a->student->student_code],
                'score'       => $a->score,
                'passed'      => $a->passed,
                'submittedAt' => $a->submitted_at,
            ]),
        ]);
    }
}
```

### 4.2 StudentExamController — Realizar Exame

```php
class StudentExamController extends Controller
{
    public function start(Request $request, Exam $exam): JsonResponse
    {
        $student = $request->user()->studentProfile;

        // Validações antes de começar
        if ($exam->status !== 'published') {
            abort(403, 'Este exame não está disponível.');
        }

        if ($exam->due_date && now()->isAfter($exam->due_date)) {
            abort(403, 'O prazo para este exame expirou.');
        }

        $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                                   ->where('student_id', $student->id)
                                   ->count();

        if ($attemptCount >= $exam->max_attempts) {
            abort(422, 'Já atingiste o número máximo de tentativas.');
        }

        // Criar tentativa
        $attempt = ExamAttempt::create([
            'exam_id'    => $exam->id,
            'student_id' => $student->id,
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        // Retornar perguntas SEM o campo "correct" (segurança!)
        return response()->json([
            'attemptId' => $attempt->id,
            'timeLimit' => $exam->duration * 60,
            'dueAt'     => now()->addMinutes($exam->duration),
            'questions' => $exam->questions->map(fn($q) => [
                'id'      => $q->id,
                'text'    => $q->text,
                'type'    => $q->type,
                'options' => $q->options,
                'points'  => $q->points,
            ]),
        ]);
    }

    public function submit(SubmitExamRequest $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('submit', $attempt);

        // Verificar que ainda está em progresso
        if ($attempt->status !== 'in_progress') {
            abort(422, 'Esta tentativa já foi submetida.');
        }

        $exam      = $attempt->exam->load('questions');
        $answers   = collect($request->answers); // [{questionId, answer}]
        $correct   = 0;
        $totalPts  = 0;
        $earnedPts = 0;
        $breakdown = [];

        DB::transaction(function () use ($attempt, $exam, $answers, &$correct, &$totalPts, &$earnedPts, &$breakdown) {
            foreach ($exam->questions as $question) {
                $given = $answers->firstWhere('questionId', $question->id);
                $answerValue = $given ? $given['answer'] : -1;
                $isCorrect   = $answerValue === $question->correct;

                if ($isCorrect) {
                    $correct++;
                    $earnedPts += $question->points;
                }
                $totalPts += $question->points;

                ExamAnswer::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $question->id,
                    'answer'      => $answerValue,
                    'is_correct'  => $isCorrect,
                ]);

                $breakdown[] = [
                    'questionId'    => $question->id,
                    'correct'       => $isCorrect,
                    'yourAnswer'    => $answerValue,
                    'correctAnswer' => $question->correct,
                ];
            }

            $score  = $totalPts > 0 ? round($earnedPts / $totalPts * 100, 2) : 0;
            $passed = $score >= $exam->pass_score;

            $attempt->update([
                'status'       => 'completed',
                'submitted_at' => now(),
                'score'        => $score,
                'passed'       => $passed,
            ]);

            // Criar Grade automaticamente
            Grade::create([
                'student_id'     => $attempt->student_id,
                'teacher_id'     => $exam->teacher_id,
                'title'          => $exam->title,
                'type'           => 'Exam',
                'course_id'      => $exam->course_id,
                'class_group_id' => $exam->class_group_id,
                'grade'          => $score,
                'max_grade'      => 100,
                'date'           => now(),
            ]);

            // Notificar o aluno com o resultado
            $attempt->student->user->notify(new GradeReleasedNotification($exam->title, $score, $passed));
        });

        return response()->json([
            'score'          => $attempt->score,
            'passed'         => $attempt->passed,
            'correctCount'   => $correct,
            'totalQuestions' => $exam->questions->count(),
            'breakdown'      => $breakdown,
        ]);
    }
}
```

---

## Fase 5 — Pagamentos e Finanças

**Duração estimada:** 1.5 semanas  
**Prioridade:** Alta  
**Interfaces que alimenta:** `StudentPayments.tsx`, `StudentFouls.tsx`, `AdminPayments.tsx`

### 5.1 StudentPaymentController com Geração de Recibo PDF

```php
class StudentPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student  = $request->user()->studentProfile;
        $currency = $request->header('X-Currency', $request->user()->preferred_currency);

        $payments = QueryBuilder::for(Payment::class)
            ->where('student_id', $student->id)
            ->allowedFilters(['status'])
            ->allowedSorts(['due_date', 'created_at', 'amount'])
            ->latest('due_date')
            ->paginate($request->get('limit', 20));

        // Somar por status
        $all = Payment::where('student_id', $student->id)->get();
        $stats = [
            'totalPaid'    => $all->where('status', 'paid')->sum('amount'),
            'totalPending' => $all->whereIn('status', ['pending', 'overdue'])->sum('amount'),
            'totalOverdue' => $all->where('status', 'overdue')->sum('amount'),
            'total'        => $all->count(),
            'currency'     => $currency,
        ];

        return response()->json([
            'data'  => PaymentResource::collection($payments),
            'meta'  => [
                'total'      => $payments->total(),
                'page'       => $payments->currentPage(),
                'totalPages' => $payments->lastPage(),
            ],
            'stats' => $stats,
        ]);
    }

    public function receipt(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        // Verificar se o PDF já foi gerado e guardado no S3
        if ($payment->receipt_url) {
            return redirect($payment->receipt_url);
        }

        // Gerar PDF com DomPDF
        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment->load('student.user', 'course'),
            'school'  => [
                'name'    => 'Olsangola Corporation',
                'address' => 'Centralidade do KM44, Bloco 1, Prédio 50 — Luanda, Angola',
                'email'   => 'info@olsangola.ao',
                'phone'   => '+244 972 851 284 / +244 972 851 289',
            ],
        ])->setPaper('a4');

        // Guardar no S3 para evitar re-geração
        $key = "receipts/{$payment->invoice_number}.pdf";
        Storage::disk('s3')->put($key, $pdf->output(), 'private');
        $url = Storage::disk('s3')->temporaryUrl($key, now()->addMinutes(30));

        $payment->update(['receipt_url' => Storage::disk('s3')->url($key)]);

        return $pdf->download("{$payment->invoice_number}.pdf");
    }
}
```

### 5.2 AdminPaymentController — Gestão e Exportação

```php
class AdminPaymentController extends Controller
{
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'status'          => ['sometimes', 'in:paid,pending,overdue,cancelled,refunded'],
            'paid_at'         => ['required_if:status,paid', 'date'],
            'method'          => ['required_if:status,paid', 'in:credit_card,bank_transfer,mpesa,multicaixa,cash,other'],
            'transaction_ref' => ['nullable', 'string'],
        ]);

        $payment->update($validated);

        // Se marcado como pago, disparar job de geração de recibo e email
        if ($request->status === 'paid') {
            GeneratePaymentReceiptPdf::dispatch($payment);
            SendPaymentReceiptEmail::dispatch($payment);

            // Notificação in-app ao aluno
            $payment->student->user->notify(new PaymentConfirmedNotification($payment));
        }

        // Se ficou em overdue, actualizar status na tabela
        activity()->performedOn($payment)->causedBy(auth()->user())->log('Payment updated');

        return response()->json(new PaymentResource($payment));
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'format'     => ['required', 'in:csv,xlsx,pdf'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'status'     => ['nullable', 'in:paid,pending,overdue'],
        ]);

        if ($request->format === 'xlsx') {
            return Excel::download(
                new PaymentsExport($request->all()),
                'pagamentos-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        if ($request->format === 'csv') {
            return response()->streamDownload(function () use ($request) {
                $payments = Payment::with('student.user')
                    ->whereBetween('due_date', [$request->start_date, $request->end_date])
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->cursor();

                $header = ['Nº Fatura', 'Aluno', 'Descrição', 'Valor', 'Moeda', 'Estado', 'Data Vencimento', 'Data Pagamento', 'Método'];
                echo implode(',', $header) . "\n";
                foreach ($payments as $p) {
                    echo implode(',', [
                        $p->invoice_number, $p->student->user->full_name,
                        $p->description, $p->amount, $p->currency,
                        $p->status, $p->due_date, $p->paid_at ?? '',
                        $p->method ?? '',
                    ]) . "\n";
                }
            }, 'pagamentos.csv', ['Content-Type' => 'text/csv']);
        }
    }
}
```

### 5.3 Cron Jobs de Pagamentos (Laravel Schedule)

```php
// app/Console/Kernel.php (ou routes/console.php no Laravel 11)

Schedule::command('payments:generate-monthly')
    ->monthlyOn(1, '08:00')
    ->timezone('Africa/Luanda')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('payments:send-reminders')
    ->dailyAt('09:00')
    ->timezone('Africa/Luanda');

Schedule::command('payments:mark-overdue')
    ->dailyAt('00:01')
    ->timezone('Africa/Luanda');
```

```php
// app/Console/Commands/GenerateMonthlyPayments.php
class GenerateMonthlyPayments extends Command
{
    protected $signature = 'payments:generate-monthly {--month=}';

    public function handle(): void
    {
        $month = $this->option('month') ?? now()->month;
        $year  = now()->year;
        $monthName = Carbon::create($year, $month)->translatedFormat('F Y');

        $enrollments = Enrollment::with(['student', 'course'])
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            // Verificar se o pagamento deste mês já foi gerado
            $exists = Payment::where('student_id', $enrollment->student_id)
                              ->where('enrollment_id', $enrollment->id)
                              ->whereMonth('due_date', $month)
                              ->whereYear('due_date', $year)
                              ->exists();

            if (!$exists) {
                $payment = Payment::create([
                    'student_id'    => $enrollment->student_id,
                    'course_id'     => $enrollment->course_id,
                    'enrollment_id' => $enrollment->id,
                    'description'   => "Mensalidade — {$enrollment->course->title_pt} — {$monthName}",
                    'amount'        => $enrollment->course->price_aoa,
                    'currency'      => 'AOA',
                    'due_date'      => Carbon::create($year, $month, 5),
                    'status'        => 'pending',
                ]);

                // Notificar o aluno
                $enrollment->student->user->notify(new PaymentDueNotification($payment));
                $count++;
            }
        }

        $this->info("Gerados {$count} pagamentos para {$monthName}.");
    }
}
```

---

## Fase 6 — Documentos, Ficheiros e Conteúdo

**Duração estimada:** 1 semana  
**Prioridade:** Média  
**Interfaces que alimenta:** `StudentDocuments.tsx`, `AdminContent.tsx`

### 6.1 DocumentController com Spatie Media Library

```php
class DocumentController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'      => ['required', 'file', 'max:512000'], // 500MB
            'name'      => ['required', 'string', 'max:255'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'is_public' => ['boolean'],
        ]);

        $file     = $request->file('file');
        $mimeType = $file->getMimeType();

        // Whitelist de tipos de ficheiro permitidos
        $allowed = [
            'application/pdf', 'video/mp4', 'video/avi', 'video/quicktime',
            'image/jpeg', 'image/png', 'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!in_array($mimeType, $allowed)) {
            return response()->json([
                'error' => ['code' => 'INVALID_FILE_TYPE', 'message' => 'Tipo de ficheiro não permitido.']
            ], 422);
        }

        // Determinar tipo
        $type = match(true) {
            str_contains($mimeType, 'pdf')   => 'PDF',
            str_contains($mimeType, 'video') => 'Video',
            str_contains($mimeType, 'image') => 'Image',
            default                          => 'Document',
        };

        // Gerar chave única e fazer upload para S3/R2
        $key       = 'documents/' . Str::uuid() . '/' . $file->getClientOriginalName();
        $path      = Storage::disk('s3')->putFileAs('', $file, $key);
        $publicUrl = $request->is_public ? Storage::disk('s3')->url($path) : null;

        $document = Document::create([
            'name'           => $request->name,
            'type'           => $type,
            'mime_type'      => $mimeType,
            'size_bytes'     => $file->getSize(),
            'storage_key'    => $key,
            'public_url'     => $publicUrl,
            'course_id'      => $request->course_id,
            'uploaded_by_id' => auth()->id(),
            'is_public'      => $request->is_public ?? false,
        ]);

        return response()->json([
            'id'          => $document->id,
            'name'        => $document->name,
            'type'        => $document->type,
            'sizeBytes'   => $document->size_bytes,
            'url'         => $publicUrl ?? 'access-via-download-endpoint',
        ], 201);
    }

    public function download(Document $document): RedirectResponse
    {
        $this->authorize('download', $document);

        // Gerar URL temporária (15min) do S3
        $url = Storage::disk('s3')->temporaryUrl(
            $document->storage_key,
            now()->addMinutes(15)
        );

        // Incrementar contador de downloads
        $document->increment('downloads');

        return redirect($url);
    }
}
```

---

## Fase 7 — Notificações e Comunicação

**Duração estimada:** 1 semana  
**Prioridade:** Média  
**Interfaces que alimenta:** `StudentNotifications.tsx`, badge do `DashboardLayout.tsx`

### 7.1 Laravel Notifications — Multicanal

O Laravel tem um sistema de notificações nativo que permite enviar para múltiplos canais (`mail`, `database`, `broadcast`) com uma única classe.

```php
// app/Notifications/PaymentDueNotification.php
class PaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    // Canais: base de dados + email + broadcast (WebSocket)
    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    // Guardado na tabela "notifications" (já incluída no Laravel)
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'  => 'PAYMENT',
            'title' => 'Mensalidade pendente',
            'body'  => "A tua mensalidade de {$this->payment->amount} {$this->payment->currency} vence em " . $this->payment->due_date->format('d/m/Y'),
            'link'  => '/student/payments',
            'paymentId' => $this->payment->id,
        ];
    }

    // Enviado via Blade template para o email do aluno
    public function toMail(object $notifiable): MailMessage
    {
        $lang = $notifiable->preferred_language;
        return (new MailMessage)
            ->subject($lang === 'pt' ? 'Mensalidade Pendente — Olsangola Corporation' : 'Payment Due — Olsangola Corporation')
            ->view("emails.payment-due.{$lang}", [
                'user'    => $notifiable,
                'payment' => $this->payment,
            ]);
    }

    // Transmitido via WebSocket para o frontend (Laravel Echo)
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type'  => 'PAYMENT',
            'title' => 'Mensalidade pendente',
            'body'  => "Vencimento: " . $this->payment->due_date->format('d/m/Y'),
        ]);
    }
}
```

### 7.2 NotificationController

```php
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user          = $request->user();
        $notifications = $user->notifications()
            ->when($request->status === 'unread', fn($q) => $q->unread())
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'data'        => NotificationResource::collection($notifications),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['message' => 'Notificação marcada como lida.']);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Todas as notificações marcadas como lidas.']);
    }
}
```

### 7.3 Configuração Laravel Echo (WebSocket) no Frontend React

O frontend existente deverá instalar o cliente Laravel Echo para receber notificações em tempo real:

```bash
# No projecto React
npm install laravel-echo pusher-js
```

```typescript
// src/lib/echo.ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    cluster: 'mt1',
});

// Uso no DashboardLayout.tsx
// echo.private(`App.Models.User.${userId}`)
//     .notification((notification) => {
//         // Actualizar badge de notificações
//         setUnreadCount(c => c + 1);
//     });
```

### 7.4 Formulário de Contacto e Newsletter

```php
// app/Http/Controllers/Api/Public/ContactController.php
class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email:rfc'],
            'phone'      => ['nullable', 'string'],
            'subject'    => ['required', 'string', 'max:200'],
            'message'    => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // Guardar na BD
        ContactFormSubmission::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'language'   => $request->header('Accept-Language', 'pt'),
        ]);

        // Email para a escola (info@olsangola.ao)
        Mail::to('info@olsangola.ao')
            ->queue(new ContactFormReceived($validated));

        // Email de confirmação ao remetente
        Mail::to($validated['email'])
            ->queue(new ContactFormConfirmation($validated));

        return response()->json([
            'message' => 'Mensagem recebida! Respondemos em menos de 24 horas úteis.'
        ]);
    }
}
```

---

## Fase 8 — Dashboard Analytics e Relatórios

**Duração estimada:** 1 semana  
**Prioridade:** Média  
**Interfaces que alimenta:** `AdminOverview.tsx`, `StudentOverview.tsx`

### 8.1 AdminDashboardController

```php
class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // Cache de 5 minutos — o dashboard admin não precisa de ser real-time
        return Cache::remember('admin_dashboard', now()->addMinutes(5), function () {

            $now          = now();
            $monthStart   = $now->copy()->startOfMonth();
            $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
            $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

            // Receita deste mês vs mês anterior
            $revenueThisMonth = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$monthStart, $now])
                ->sum('amount_aoa');

            $revenueLastMonth = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('amount_aoa');

            $revenueChange = $revenueLastMonth > 0
                ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1)
                : 0;

            // Gráfico financeiro — 6 meses
            $financialChart = collect(range(5, 0))->map(function ($monthsAgo) {
                $month = now()->subMonths($monthsAgo);
                return [
                    'month'    => $month->format('M'),
                    'Revenue'  => Payment::where('status', 'paid')
                        ->whereMonth('paid_at', $month->month)
                        ->whereYear('paid_at', $month->year)
                        ->sum('amount_aoa'),
                    'Expenses' => 0, // a implementar quando existir tabela de despesas
                ];
            });

            // Actividades recentes (via Spatie Activity Log)
            $activities = Activity::with('causer')->latest()->take(10)->get()->map(fn($a) => [
                'text' => $a->description,
                'time' => $a->created_at->diffForHumans(),
            ]);

            return response()->json([
                'stats' => [
                    'totalStudents'  => StudentProfile::count(),
                    'totalTeachers'  => TeacherProfile::count(),
                    'activeClasses'  => ClassGroup::where('is_active', true)->count(),
                    'monthlyRevenue' => ['AOA' => $revenueThisMonth],
                    'revenueChange'  => $revenueChange,
                ],
                'financialChart'    => $financialChart,
                'recentActivities'  => $activities,
                'paymentSummary' => [
                    'paid'    => Payment::where('status', 'paid')->whereMonth('due_date', $now->month)->count(),
                    'pending' => Payment::where('status', 'pending')->count(),
                    'overdue' => Payment::where('status', 'overdue')->count(),
                ],
                'alerts' => $this->getSystemAlerts(),
            ]);
        });
    }

    private function getSystemAlerts(): array
    {
        $alerts = [];

        $overdueCount = Payment::where('status', 'overdue')->count();
        if ($overdueCount > 0) {
            $alerts[] = ['text' => "{$overdueCount} pagamento(s) em atraso.", 'type' => 'error'];
        }

        $fullClasses = ClassGroup::where('is_active', true)->get()->filter(fn($c) => !$c->hasCapacity());
        if ($fullClasses->count() > 0) {
            $alerts[] = ['text' => "{$fullClasses->count()} turma(s) com capacidade máxima atingida.", 'type' => 'info'];
        }

        return $alerts;
    }
}
```

### 8.2 StudentDashboardController

```php
class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $student = $user->studentProfile;

        // Performance trend — últimos 7 registos de notas
        $grades       = Grade::where('student_id', $student->id)->latest('date')->take(7)->get();
        $trend        = $grades->sortBy('date')->map(fn($g) => [
            'date'  => $g->date->format('M d'),
            'grade' => $g->grade,
        ])->values();

        // Progresso por curso
        $enrollments  = Enrollment::with('course')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->get();

        $courseProgress = $enrollments->map(fn($e) => [
            'name'     => $e->course->{'title_' . app()->getLocale()},
            'progress' => $e->progress_pct,
        ]);

        // Exames futuros
        $upcomingExams = Exam::whereIn('class_group_id', $enrollments->pluck('class_group_id'))
            ->where('status', 'published')
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->map(fn($e) => [
                'title'    => $e->title,
                'course'   => $e->classGroup?->course->{'title_' . app()->getLocale()},
                'date'     => $e->due_date->format('M d'),
                'duration' => $e->duration . ' min',
            ]);

        return response()->json([
            'welcomeName'    => $user->first_name,
            'currentLevel'   => $student->current_level ?? 'N/A',
            'stats' => [
                'enrolledCourses'  => $enrollments->count(),
                'availableExams'   => $upcomingExams->count(),
                'averageGrade'     => round($grades->avg('grade') ?? 0),
                'pendingPayments'  => Payment::where('student_id', $student->id)
                    ->whereIn('status', ['pending', 'overdue'])->count(),
            ],
            'performanceTrend' => $trend,
            'courseProgress'   => $courseProgress,
            'upcomingExams'    => $upcomingExams,
        ]);
    }
}
```

---

## Fase 9 — API Pública e Integrações

**Duração estimada:** 0.5 semanas  
**Prioridade:** Baixa (fase final)

### 9.1 Inscrição Online (Lead)

Endpoint público que cria um utilizador com status `pending` e notifica o admin:

```php
public function enrollmentRequest(Request $request): JsonResponse
{
    $validated = $request->validate([
        'first_name'   => ['required', 'string'],
        'last_name'    => ['required', 'string'],
        'email'        => ['required', 'email', 'unique:users,email'],
        'phone'        => ['required', 'string'],
        'service_type' => ['required', 'string'],
        'country'      => ['nullable', 'string', 'size:2'],
        'language'     => ['nullable', 'in:pt,en'],
        'currency'     => ['nullable', 'in:AOA,EUR,USD'],
    ]);

    $user = User::create([
        ...$validated,
        'password'            => Hash::make(Str::random(16)),
        'role'                => 'student',
        'status'              => 'pending',
        'preferred_language'  => $validated['language'] ?? 'pt',
        'preferred_currency'  => $validated['currency'] ?? 'AOA',
    ]);
    $user->assignRole('student');
    StudentProfile::create([
        'user_id'      => $user->id,
        'student_code' => $this->generateStudentCode(),
    ]);

    // Email de boas-vindas com instruções para o aluno
    SendWelcomeEmail::dispatch($user, $validated['service_type']);

    // Notificar admin da nova inscrição
    User::role('admin')->each(fn($admin) =>
        $admin->notify(new NewEnrollmentRequestNotification($user))
    );

    return response()->json(['message' => 'Inscrição recebida! Entraremos em contacto em breve.'], 201);
}
```

---

## Fase 10 — Infraestrutura, Segurança e Deploy

**Duração estimada:** 1 semana  
**Prioridade:** Crítica (antes de produção)

### 10.1 Rate Limiting (Laravel nativo)

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinutes(15, 10)->by($request->ip()); // 10 tentativas por 15 min
});

RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(300)->by($request->user()->id)
        : Limit::perMinute(100)->by($request->ip());
});

RateLimiter::for('public', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

// Aplicar nas rotas:
Route::middleware(['throttle:auth'])->group(...)
Route::middleware(['throttle:api'])->group(...)
```

### 10.2 Política de Autorização (Laravel Policies)

```php
// app/Policies/PaymentPolicy.php
class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        // Admin pode ver todos; aluno só pode ver os seus
        return $user->hasRole('admin')
            || $user->studentProfile?->id === $payment->student_id;
    }

    public function update(User $user, Payment $payment): bool
    {
        // Apenas admin pode actualizar pagamentos
        return $user->hasRole('admin');
    }
}

// app/Policies/ExamPolicy.php
class ExamPolicy
{
    public function submit(User $user, ExamAttempt $attempt): bool
    {
        // Aluno só pode submeter a sua própria tentativa
        return $user->studentProfile?->id === $attempt->student_id
            && $attempt->status === 'in_progress';
    }
}
```

### 10.3 Handler de Erros Padronizado

```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->renderable(function (\Throwable $e, Request $request) {
        if ($request->is('api/*')) {
            $response = match(true) {
                $e instanceof AuthenticationException     => ['code' => 'AUTH_REQUIRED',   'message' => 'Autenticação necessária.',       'status' => 401],
                $e instanceof AuthorizationException      => ['code' => 'FORBIDDEN',        'message' => 'Sem permissão para este recurso.','status' => 403],
                $e instanceof ModelNotFoundException      => ['code' => 'NOT_FOUND',        'message' => 'Recurso não encontrado.',        'status' => 404],
                $e instanceof ValidationException         => ['code' => 'VALIDATION_ERROR', 'message' => 'Dados inválidos.',              'status' => 422, 'details' => $e->errors()],
                $e instanceof ThrottleRequestsException   => ['code' => 'RATE_LIMITED',     'message' => 'Muitos pedidos. Tente mais tarde.','status' => 429],
                default                                   => ['code' => 'SERVER_ERROR',     'message' => 'Erro interno. Tente mais tarde.', 'status' => 500],
            };

            return response()->json(['success' => false, 'error' => $response], $response['status']);
        }
    });
}
```

### 10.4 Middleware de Localização e Moeda

```php
// app/Http/Middleware/SetLocaleFromHeader.php
class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->header('Accept-Language', 'pt');
        $lang = in_array($lang, ['pt', 'en']) ? $lang : 'pt';
        App::setLocale($lang);
        return $next($request);
    }
}

// app/Http/Middleware/SetCurrencyFromHeader.php
class SetCurrencyFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $currency = $request->header('X-Currency',
            $request->user()?->preferred_currency ?? 'AOA'
        );
        $currency = in_array($currency, ['AOA', 'EUR', 'USD']) ? $currency : 'AOA';
        app()->instance('currency', $currency);
        return $next($request);
    }
}
```

### 10.5 Configuração Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name api.olsangola.ao;

    root /var/www/olsangola-api/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/api.olsangola.ao/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.olsangola.ao/privkey.pem;

    # Segurança
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    # Compressão
    gzip on;
    gzip_types application/json text/plain;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # WebSocket (Laravel WebSockets)
    location /app {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
    }
}
```

### 10.6 Deploy com Laravel Forge / Envoyer

**Opção recomendada:** Laravel Forge (servidor) + Envoyer (zero-downtime deploy)

```bash
# Script de deploy (executado pelo Envoyer ou manualmente)
cd /var/www/olsangola-api

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force          # executa migrations em produção
php artisan queue:restart            # reinicia workers da fila
sudo supervisorctl restart all       # reinicia PHP-FPM e workers
```

**Supervisor (gestão de filas e WebSocket):**
```ini
[program:olsangola-queue]
command=php /var/www/olsangola-api/artisan queue:work redis --sleep=3 --tries=3 --timeout=60
numprocs=4
autostart=true
autorestart=true
user=www-data

[program:olsangola-websockets]
command=php /var/www/olsangola-api/artisan websockets:serve
autostart=true
autorestart=true
user=www-data
```

### 10.7 Testes com Pest PHP

```php
// tests/Feature/Auth/AuthTest.php
it('allows a user to register and receive a token', function () {
    $response = postJson('/api/v1/auth/register', [
        'first_name'            => 'João',
        'last_name'             => 'Silva',
        'email'                 => 'joao@test.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
        'role'                  => 'student',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['message', 'user_id']);

    assertDatabaseHas('users', ['email' => 'joao@test.com', 'role' => 'student']);
    assertDatabaseHas('student_profiles', []); // perfil criado
});

it('rejects login with wrong password', function () {
    $user = User::factory()->student()->active()->create();

    postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(401)->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('prevents class from exceeding 6 student capacity', function () {
    $admin  = User::factory()->admin()->active()->create();
    $class  = ClassGroup::factory()->withStudents(6)->create();
    $student = StudentProfile::factory()->create();

    actingAs($admin)
        ->postJson("/api/v1/admin/classes/{$class->id}/students", [
            'student_id' => $student->id
        ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'CLASS_FULL');
});

it('calculates exam score correctly', function () {
    $student = User::factory()->student()->active()->create();
    $exam    = Exam::factory()->published()->withQuestions(5)->create();
    $attempt = ExamAttempt::factory()->inProgress()->for($exam)->for($student->studentProfile)->create();

    actingAs($student)
        ->postJson("/api/v1/student/exams/attempts/{$attempt->id}/submit", [
            'answers' => $exam->questions->map(fn($q) => [
                'questionId' => $q->id,
                'answer'     => $q->correct, // responder tudo correcto
            ])->toArray()
        ])
        ->assertOk()
        ->assertJsonPath('score', 100.0)
        ->assertJsonPath('passed', true);
});
```

---

## Integração Frontend → Backend por Página

| Página React | Método + Endpoint Laravel | Estado Actual |
|---|---|---|
| `HomePage.tsx` | `GET /api/v1/public/courses` + `GET /api/v1/public/stats` + `GET /api/v1/public/exchange-rates` | Hardcoded |
| `ServicesPage.tsx` | `GET /api/v1/public/courses?service_type=...` | Hardcoded |
| `CoursesPage.tsx` | `GET /api/v1/public/courses` | `/data/courses.ts` |
| `ContactPage.tsx` | `POST /api/v1/public/contact` | Simulado |
| `LoginPage.tsx` | `POST /api/v1/auth/login` | Simulado |
| `RegisterPage.tsx` | `POST /api/v1/auth/register` | Simulado |
| `ForgotPasswordPage.tsx` | `POST /api/v1/auth/forgot-password` | Não implementado |
| `CountrySelector.tsx` | `GET /api/v1/public/exchange-rates` | localStorage |
| `StudentOverview.tsx` | `GET /api/v1/student/dashboard` | Hardcoded |
| `StudentCourses.tsx` | `GET /api/v1/student/courses` + `POST /progress` | Hardcoded |
| `StudentSchedule.tsx` | `GET /api/v1/student/schedule?week=...` | Hardcoded |
| `StudentExams.tsx` | `GET /api/v1/student/exams` + `POST /start` + `POST /submit` | Lógica funcional, dados hardcoded |
| `StudentGrades.tsx` | `GET /api/v1/student/grades` | Hardcoded |
| `StudentPayments.tsx` | `GET /api/v1/student/payments` + `GET /:id/receipt` | Hardcoded |
| `StudentFouls.tsx` | `GET /api/v1/student/fouls` + `POST /pay` | Hardcoded |
| `StudentDocuments.tsx` | `GET /api/v1/documents` + `POST /upload` + `GET /:id/download` | Hardcoded |
| `StudentNotifications.tsx` | `GET /api/v1/notifications` + `PATCH /:id/read` | Hardcoded |
| `TeacherClasses.tsx` | `GET /api/v1/teacher/classes` | Hardcoded |
| `TeacherStudents.tsx` | `GET /api/v1/teacher/students` + `POST /grades` | Hardcoded |
| `TeacherExams.tsx` | `GET /api/v1/teacher/exams` + CRUD completo | CRUD local |
| `TeacherAttendance.tsx` | `POST /api/v1/teacher/attendance` | Não implementado |
| `AdminOverview.tsx` | `GET /api/v1/admin/dashboard` | Hardcoded |
| `AdminUsers.tsx` | `GET/POST/PATCH/DELETE /api/v1/admin/users` | CRUD local |
| `AdminClasses.tsx` | `GET/POST/PATCH/DELETE /api/v1/admin/classes` | CRUD local |
| `AdminPayments.tsx` | `GET/PATCH /api/v1/admin/payments` + `GET /export` | Hardcoded |
| `AdminSettings.tsx` | `GET/PATCH /api/v1/admin/settings` | Hardcoded |
| `AdminReports.tsx` | `GET /api/v1/admin/reports/*` | Não implementado |
| `DashboardLayout.tsx` | `GET /api/v1/me/summary` + WebSocket Echo | Badge estático |

### Configuração do Cliente HTTP no Frontend (Axios)

```typescript
// src/lib/api.ts
import axios from 'axios';

export const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? 'https://api.olsangola.ao/api/v1',
    withCredentials: true,           // necessário para Sanctum SPA (cookies)
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

// Interceptor — adicionar token e headers de localização/moeda
api.interceptors.request.use((config) => {
    const token    = localStorage.getItem('ols_token');
    const lang     = localStorage.getItem('ols_lang') ?? 'pt';
    const currency = localStorage.getItem('ols_currency') ?? 'AOA';

    if (token) config.headers.Authorization = `Bearer ${token}`;
    config.headers['Accept-Language'] = lang;
    config.headers['X-Currency']      = currency;

    return config;
});

// Interceptor — tratar token expirado (401)
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('ols_token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
```

---

## Cronograma Estimado

| Fase | Descrição | Duração |
|------|-----------|---------|
| **Setup** | Laravel, packages, Docker local, migrations base | 3 dias |
| **Fase 1** | Autenticação Sanctum completa (register, login, verify, reset) | 1 semana |
| **Fase 2** | Gestão de utilizadores com Spatie Permissions, policies | 1 semana |
| **Fase 3** | Cursos, turmas, horários, inscrições, progresso | 1.5 semanas |
| **Fase 4** | Exames (CRUD + realização + scoring automático) e notas | 1.5 semanas |
| **Fase 5** | Pagamentos, cron jobs de mensalidades, exportação | 1.5 semanas |
| **Fase 6** | Upload S3/R2, gestão de documentos, presigned URLs | 1 semana |
| **Fase 7** | Notificações multicanal (DB + email + WebSocket Echo) | 1 semana |
| **Fase 8** | Analytics e dashboards (admin + aluno) com cache Redis | 1 semana |
| **Fase 9** | API pública + lead de inscrição + taxas de câmbio | 3 dias |
| **Fase 10** | Nginx, Supervisor, deploy Forge/Envoyer, Pest tests | 1 semana |
| **Integração** | Ligar frontend React ao backend (substituir dados hardcoded) | 1 semana |
| **Buffer / QA** | Testes, bugs, ajustes de segurança | 1 semana |

**Total estimado: 13–14 semanas** com 1 developer Laravel full-time.  
Com 2 developers: as Fases 3–7 podem ser paralelizadas → **7–8 semanas**.

---

*Plano elaborado com base na análise completa do código-fonte React existente da Olsangola Corporation. Todos os endpoints, modelos, relações Eloquent e exemplos de código PHP foram desenhados para correspondência directa com as interfaces TypeScript, componentes React e fluxos de UI existentes.*
