# Olsangola Corporation — Backend API (Laravel 11)

## Stack
- PHP 8.3 + Laravel 11
- PostgreSQL 16
- Redis 7 (cache, queues, sessions)
- Laravel Sanctum (autenticação SPA)
- Spatie Laravel Permission (roles e permissões)

## Instalação

```bash
# 1. Instalar dependências
composer install

# 2. Copiar e configurar variáveis de ambiente
cp .env.example .env
php artisan key:generate

# 3. Configurar a base de dados no .env
DB_CONNECTION=pgsql
DB_DATABASE=olsangola
DB_USERNAME=ols_user
DB_PASSWORD=secret

# 4. Correr migrations
php artisan migrate

# 5. Correr seeders (dados iniciais)
php artisan db:seed

# 6. Iniciar servidor de desenvolvimento
php artisan serve
```

## Etapas de Implementação

| Etapa | Estado      | Conteúdo                                      |
|-------|-------------|-----------------------------------------------|
| 1     | ✅ Completa  | Migrations (22 tabelas) + Models (18 classes) |
| 2     | 🔲 Pendente  | Auth Controllers + Middleware + Form Requests + API Resources |
| 3     | 🔲 Pendente  | Admin Controllers (Users, Classes, Payments, Dashboard) |
| 4     | 🔲 Pendente  | Teacher Controllers (Classes, Exams, Grades, Attendance) |
| 5     | 🔲 Pendente  | Student Controllers (Dashboard, Payments, Exams, Schedule) |
| 6     | 🔲 Pendente  | Seeders + Jobs (mensalidades) + Notifications + routes/api.php |
| 7     | 🔲 Pendente  | Integração Frontend (src/lib/api.ts + substituir dados hardcoded) |

## Estrutura de Tabelas (Etapa 1)

- `users` — utilizadores unificados (admin, teacher, student)
- `student_profiles` — dados específicos do aluno (código OLS-2026-001)
- `teacher_profiles` — dados específicos do professor (TCH-001)
- `admin_profiles` — perfil de administrador
- `courses` — cursos com preços em AOA/EUR/USD e flyers
- `class_groups` — turmas com limite de 6 alunos (regra OLS)
- `class_schedules` — horários recorrentes por turma
- `enrollments` — inscrições aluno↔turma com progresso
- `attendances` — presenças por aula
- `exams` — avaliações (draft → published → closed)
- `questions` — perguntas de exame (multiple_choice, true_false)
- `exam_attempts` — tentativas dos alunos
- `exam_answers` — respostas individuais por pergunta
- `grades` — notas lançadas (manuais ou automáticas via exam)
- `payments` — pagamentos com invoice_number automático (INV-2026-0001)
- `fouls` — multas disciplinares
- `documents` — ficheiros uploaded para S3/R2
- `notifications` — notificações in-app (Laravel nativo)
- `system_settings` — configurações do sistema (key→value)
- `newsletter_subscribers` — subscritores da newsletter
- `contact_form_submissions` — submissões do formulário de contacto
- `personal_access_tokens` — tokens Sanctum

## API Base URL
```
http://localhost:8000/api/v1/
```

## Contacto
info@olsangola.ao | +244 972 851 284
