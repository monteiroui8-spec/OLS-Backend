<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Foul;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Question;
use App\Models\StudentProfile;
use App\Models\SystemSetting;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── 1. Roles & Permissions ────────────────────────────────────────────
        $this->call(RolePermissionSeeder::class);

        // ─── 2. System Settings ────────────────────────────────────────────────
        $this->seedSystemSettings();

        // ─── 3. Courses ────────────────────────────────────────────────────────
        $courses = $this->seedCourses();

        // ─── 4. Users & Profiles ───────────────────────────────────────────────
        $admin   = $this->seedAdmin();
        $teacher = $this->seedTeacher();
        $student = $this->seedStudent();

        // ─── 5. Class Group + Schedules ────────────────────────────────────────
        $classGroup = $this->seedClassGroup($courses['b1'], $teacher->teacherProfile);

        // ─── 6. Enrollment ─────────────────────────────────────────────────────
        $enrollment = $this->seedEnrollment($student->studentProfile, $courses['b1'], $classGroup);

        // ─── 7. Payments ───────────────────────────────────────────────────────
        $this->seedPayments($student->studentProfile, $courses['b1'], $enrollment);

        // ─── 8. Attendances ────────────────────────────────────────────────────
        $this->seedAttendances($classGroup, $student->studentProfile, $teacher->teacherProfile);

        // ─── 9. Exam + Questions + Attempt + Answers ───────────────────────────
        $exam = $this->seedExam($classGroup, $courses['b1'], $teacher->teacherProfile);
        $this->seedExamAttempt($exam, $student->studentProfile);

        // ─── 10. Grades ────────────────────────────────────────────────────────
        $this->seedGrades($student->studentProfile, $teacher->teacherProfile, $courses['b1'], $classGroup);

        // ─── 11. Fouls ─────────────────────────────────────────────────────────
        $this->seedFouls($student->studentProfile, $courses['b1'], $admin->id);

        // ─── 12. Documents ─────────────────────────────────────────────────────
        $this->seedDocuments($courses['b1'], $teacher->id);

        // ─── 13. Demo data (multiple students, overdue payments, extra classes) ─
        $this->call(DemoSeeder::class);

        $this->command->info('✅  Seed concluído com sucesso!');
        $this->command->table(
            ['Perfil', 'Email', 'Senha'],
            [
                ['Admin',    'admin@olsangola.ao',   'admin12345'],
                ['Professor','teacher@olsangola.ao', 'teacher12345'],
                ['Aluno',    'student@olsangola.ao', 'student12345'],
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SYSTEM SETTINGS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedSystemSettings(): void
    {
        $settings = [
            ['key' => 'school_name',           'value' => 'OLS Angola',                          'description' => 'Nome oficial da escola'],
            ['key' => 'school_email',          'value' => 'geral@olsangola.ao',                  'description' => 'Email de contacto geral'],
            ['key' => 'school_phone',          'value' => '+244 923 456 789',                    'description' => 'Telefone principal'],
            ['key' => 'school_address',        'value' => 'Rua Major Kanhangulo, 123, Luanda',   'description' => 'Morada da escola'],
            ['key' => 'school_country',        'value' => 'AO',                                  'description' => 'País'],
            ['key' => 'default_currency',      'value' => 'AOA',                                 'description' => 'Moeda padrão'],
            ['key' => 'default_language',      'value' => 'pt',                                  'description' => 'Idioma padrão'],
            ['key' => 'timezone',              'value' => 'Africa/Luanda',                       'description' => 'Fuso horário'],
            ['key' => 'max_class_capacity',    'value' => '8',                                   'description' => 'Capacidade máxima por turma'],
            ['key' => 'trial_lesson_enabled',  'value' => 'true',                                'description' => 'Aula experimental habilitada'],
            ['key' => 'invoice_prefix',        'value' => 'OLS',                                 'description' => 'Prefixo das facturas'],
            ['key' => 'late_fee_pct',          'value' => '5',                                   'description' => 'Percentagem de multa por atraso (%)'],
            ['key' => 'academic_year',         'value' => '2025/2026',                           'description' => 'Ano lectivo actual'],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(['key' => $s['key']], [
                'value'       => $s['value'],
                'description' => $s['description'],
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COURSES
    // ══════════════════════════════════════════════════════════════════════════
    private function seedCourses(): array
    {
        $list = [
            [
                'slug'           => 'ingles-comunicacao-diaria-a1',
                'title_pt'       => 'Inglês — Comunicação Diária A1',
                'title_en'       => 'English — Daily Communication A1',
                'description_pt' => 'Curso introdutório para quem não tem qualquer conhecimento de inglês. Foco em vocabulário básico, saudações e frases do quotidiano.',
                'description_en' => 'Introductory course for absolute beginners. Focus on basic vocabulary, greetings and everyday phrases.',
                'level'          => 'A1',
                'service_type'   => 'group_daily_communication',
                'duration'       => '3 meses',
                'price_aoa'      => 35000.00,
                'price_eur'      => 35.00,
                'price_usd'      => 38.00,
                'is_active'      => true,
                'tags'           => ['iniciante', 'grupo', 'comunicação'],
            ],
            [
                'slug'           => 'ingles-comunicacao-diaria-b1',
                'title_pt'       => 'Inglês — Comunicação Diária B1',
                'title_en'       => 'English — Daily Communication B1',
                'description_pt' => 'Nível intermédio. O aluno aperfeiçoa a compreensão oral e escrita e adquire fluência para conversas do dia-a-dia.',
                'description_en' => 'Intermediate level. Students improve listening and reading comprehension and develop fluency for everyday conversations.',
                'level'          => 'B1',
                'service_type'   => 'group_daily_communication',
                'duration'       => '4 meses',
                'price_aoa'      => 45000.00,
                'price_eur'      => 45.00,
                'price_usd'      => 49.00,
                'is_active'      => true,
                'tags'           => ['intermédio', 'grupo', 'conversação'],
            ],
            [
                'slug'           => 'ingles-individual-b2',
                'title_pt'       => 'Inglês Individual B2',
                'title_en'       => 'Individual English B2',
                'description_pt' => 'Aulas individuais personalizadas para nível B2. Ideal para quem precisa de evoluir rapidamente.',
                'description_en' => 'Personalised one-on-one lessons at B2 level. Ideal for students who need to progress quickly.',
                'level'          => 'B2',
                'service_type'   => 'individual',
                'duration'       => 'Flexível',
                'price_aoa'      => 70000.00,
                'price_eur'      => 70.00,
                'price_usd'      => 75.00,
                'is_active'      => true,
                'tags'           => ['individual', 'avançado'],
            ],
            [
                'slug'           => 'ingles-oil-gas',
                'title_pt'       => 'Inglês para Petróleo e Gás',
                'title_en'       => 'English for Oil & Gas',
                'description_pt' => 'Inglês técnico para profissionais do sector petrolífero. Vocabulário especializado, relatórios e comunicação offshore.',
                'description_en' => 'Technical English for oil and gas professionals. Specialised vocabulary, reports and offshore communication.',
                'level'          => 'B2',
                'service_type'   => 'oil_gas',
                'duration'       => '6 meses',
                'price_aoa'      => 120000.00,
                'price_eur'      => 120.00,
                'price_usd'      => 130.00,
                'is_active'      => true,
                'tags'           => ['oil&gas', 'corporativo', 'técnico'],
            ],
            [
                'slug'           => 'ingles-banca-financas',
                'title_pt'       => 'Inglês para Banca e Finanças',
                'title_en'       => 'English for Banking & Finance',
                'description_pt' => 'Curso especializado para profissionais do sector bancário e financeiro angolano.',
                'description_en' => 'Specialised course for banking and finance professionals in Angola.',
                'level'          => 'B1',
                'service_type'   => 'banking_finance',
                'duration'       => '5 meses',
                'price_aoa'      => 95000.00,
                'price_eur'      => 95.00,
                'price_usd'      => 103.00,
                'is_active'      => true,
                'tags'           => ['banca', 'finanças', 'corporativo'],
            ],
        ];

        $created = [];
        $keys    = ['a1', 'b1', 'individual_b2', 'oil_gas', 'banking'];

        foreach ($list as $i => $data) {
            $created[$keys[$i]] = Course::updateOrCreate(['slug' => $data['slug']], $data);
        }

        return $created;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN
    // ══════════════════════════════════════════════════════════════════════════
    private function seedAdmin(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@olsangola.ao'],
            [
                'first_name'          => 'Carlos',
                'last_name'           => 'Mendonça',
                'phone'               => '+244 923 100 001',
                'password'            => Hash::make('admin12345'),
                'role'                => 'admin',
                'status'              => 'active',
                'country'             => 'AO',
                'preferred_language'  => 'pt',
                'preferred_currency'  => 'AOA',
                'email_verified_at'   => now(),
                'last_login_at'       => now(),
            ]
        );

        $admin->syncRoles('admin');

        AdminProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'admin_code'  => 'ADM-001',
                'permissions' => ['full_access', 'manage_billing', 'manage_staff'],
            ]
        );

        return $admin;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEACHER
    // ══════════════════════════════════════════════════════════════════════════
    private function seedTeacher(): User
    {
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@olsangola.ao'],
            [
                'first_name'          => 'Ana',
                'last_name'           => 'Ferreira',
                'phone'               => '+244 923 200 002',
                'password'            => Hash::make('teacher12345'),
                'role'                => 'teacher',
                'status'              => 'active',
                'country'             => 'AO',
                'preferred_language'  => 'pt',
                'preferred_currency'  => 'AOA',
                'email_verified_at'   => now(),
                'last_login_at'       => now()->subDays(1),
            ]
        );

        $teacher->syncRoles('teacher');

        TeacherProfile::updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'teacher_code'    => 'TCH-001',
                'bio'             => 'Professora de inglês com 8 anos de experiência no ensino de adultos em Angola. Certificada pelo Cambridge Assessment English.',
                'certifications'  => ['CELTA', 'Cambridge TKT', 'IELTS 8.0'],
                'specializations' => ['Business English', 'Oil & Gas English', 'Daily Communication'],
                'hire_date'       => '2020-03-01',
                'salary'          => 250000.00,
                'salary_currency' => 'AOA',
            ]
        );

        return $teacher;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STUDENT
    // ══════════════════════════════════════════════════════════════════════════
    private function seedStudent(): User
    {
        $student = User::updateOrCreate(
            ['email' => 'student@olsangola.ao'],
            [
                'first_name'          => 'João',
                'last_name'           => 'Silva',
                'phone'               => '+244 923 300 003',
                'password'            => Hash::make('student12345'),
                'role'                => 'student',
                'status'              => 'active',
                'country'             => 'AO',
                'preferred_language'  => 'pt',
                'preferred_currency'  => 'AOA',
                'email_verified_at'   => now(),
                'last_login_at'       => now()->subHours(3),
            ]
        );

        $student->syncRoles('student');

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'student_code'    => 'STU-001',
                'current_level'   => 'B1',
                'enrollment_date' => '2026-01-15',
                'notes'           => 'Aluno motivado. Tem dificuldades com tempos verbais no passado. Recomendado reforço em listening.',
            ]
        );

        return $student;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLASS GROUP + SCHEDULES
    // ══════════════════════════════════════════════════════════════════════════
    private function seedClassGroup(Course $course, TeacherProfile $teacherProfile): ClassGroup
    {
        $classGroup = ClassGroup::updateOrCreate(
            ['name' => 'Turma B1 — Manhã 2026'],
            [
                'course_id'  => $course->id,
                'teacher_id' => $teacherProfile->id,
                'year'       => 2026,
                'capacity'   => 8,
                'room'       => 'Sala 02',
                'is_active'  => true,
            ]
        );

        // Segunda, Quarta e Sexta — 08:00–10:00
        $schedules = [
            ['day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00'], // Segunda
            ['day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '10:00'], // Quarta
            ['day_of_week' => 5, 'start_time' => '08:00', 'end_time' => '10:00'], // Sexta
        ];

        foreach ($schedules as $s) {
            ClassSchedule::updateOrCreate(
                [
                    'class_group_id' => $classGroup->id,
                    'day_of_week'    => $s['day_of_week'],
                ],
                [
                    'start_time'   => $s['start_time'],
                    'end_time'     => $s['end_time'],
                    'room'         => 'Sala 02',
                    'is_recurring' => true,
                ]
            );
        }

        return $classGroup;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENROLLMENT
    // ══════════════════════════════════════════════════════════════════════════
    private function seedEnrollment(
        StudentProfile $studentProfile,
        Course $course,
        ClassGroup $classGroup
    ): Enrollment {
        return Enrollment::updateOrCreate(
            [
                'student_id' => $studentProfile->id,
                'course_id'  => $course->id,
            ],
            [
                'class_group_id' => $classGroup->id,
                'start_date'     => '2026-01-20',
                'end_date'       => '2026-05-20',
                'status'         => 'active',
                'progress_pct'   => 45,
                'notes'          => 'Inscrição confirmada. Pagamento parcial recebido.',
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYMENTS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedPayments(
        StudentProfile $studentProfile,
        Course $course,
        Enrollment $enrollment
    ): void {
        $payments = [
            [
                'description'     => 'Mensalidade Janeiro 2026 — Inglês B1',
                'amount'          => 45000.00,
                'currency'        => 'AOA',
                'amount_aoa'      => 45000.00,
                'due_date'        => '2026-01-20',
                'paid_at'         => '2026-01-18 10:30:00',
                'status'          => 'paid',
                'method'          => 'multicaixa',
                'transaction_ref' => 'MCX-2026-0118-001',
                'invoice_number'  => 'OLS-2026-0001',
                'notes'           => 'Pago antecipado.',
            ],
            [
                'description'     => 'Mensalidade Fevereiro 2026 — Inglês B1',
                'amount'          => 45000.00,
                'currency'        => 'AOA',
                'amount_aoa'      => 45000.00,
                'due_date'        => '2026-02-20',
                'paid_at'         => '2026-02-19 09:15:00',
                'status'          => 'paid',
                'method'          => 'bank_transfer',
                'transaction_ref' => 'BNK-2026-0219-002',
                'invoice_number'  => 'OLS-2026-0002',
                'notes'           => null,
            ],
            [
                'description'     => 'Mensalidade Março 2026 — Inglês B1',
                'amount'          => 45000.00,
                'currency'        => 'AOA',
                'amount_aoa'      => 45000.00,
                'due_date'        => '2026-03-20',
                'paid_at'         => null,
                'status'          => 'pending',
                'method'          => null,
                'transaction_ref' => null,
                'invoice_number'  => 'OLS-2026-0003',
                'notes'           => 'Aguarda pagamento.',
            ],
        ];

        foreach ($payments as $p) {
            Payment::updateOrCreate(
                ['invoice_number' => $p['invoice_number']],
                array_merge($p, [
                    'student_id'    => $studentProfile->id,
                    'course_id'     => $course->id,
                    'enrollment_id' => $enrollment->id,
                ])
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ATTENDANCES
    // ══════════════════════════════════════════════════════════════════════════
    private function seedAttendances(
        ClassGroup $classGroup,
        StudentProfile $studentProfile,
        TeacherProfile $teacherProfile
    ): void {
        $records = [
            ['date' => '2026-01-20', 'status' => 'present',   'notes' => null],
            ['date' => '2026-01-22', 'status' => 'present',   'notes' => null],
            ['date' => '2026-01-24', 'status' => 'absent',    'notes' => 'Falta não justificada.'],
            ['date' => '2026-01-27', 'status' => 'present',   'notes' => null],
            ['date' => '2026-01-29', 'status' => 'late',      'notes' => 'Chegou 15 minutos atrasado.'],
            ['date' => '2026-01-31', 'status' => 'present',   'notes' => null],
            ['date' => '2026-02-03', 'status' => 'present',   'notes' => null],
            ['date' => '2026-02-05', 'status' => 'justified', 'notes' => 'Atestado médico apresentado.'],
            ['date' => '2026-02-07', 'status' => 'present',   'notes' => null],
            ['date' => '2026-02-10', 'status' => 'present',   'notes' => null],
        ];

        foreach ($records as $r) {
            Attendance::updateOrCreate(
                [
                    'class_group_id' => $classGroup->id,
                    'student_id'     => $studentProfile->id,
                    'date'           => $r['date'],
                ],
                [
                    'teacher_id' => $teacherProfile->id,
                    'status'     => $r['status'],
                    'notes'      => $r['notes'],
                ]
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXAM + QUESTIONS + ATTEMPT + ANSWERS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedExam(
        ClassGroup $classGroup,
        Course $course,
        TeacherProfile $teacherProfile
    ): Exam {
        $exam = Exam::updateOrCreate(
            ['title' => 'Teste de Avaliação B1 — Unidade 1'],
            [
                'class_group_id' => $classGroup->id,
                'course_id'      => $course->id,
                'teacher_id'     => $teacherProfile->id,
                'duration'       => 60,
                'max_attempts'   => 2,
                'status'         => 'published',
                'due_date'       => '2026-02-28 10:00:00',
                'total_points'   => 100,
                'pass_score'     => 50,
            ]
        );

        $questions = [
            [
                'text'    => 'Which sentence is grammatically correct?',
                'type'    => 'multiple_choice',
                'options' => [
                    'She don\'t like coffee.',
                    'She doesn\'t likes coffee.',
                    'She doesn\'t like coffee.',
                    'She not like coffee.',
                ],
                'correct' => 2,
                'points'  => 20,
                'order'   => 1,
            ],
            [
                'text'    => 'English is the official language of Angola.',
                'type'    => 'true_false',
                'options' => ['True', 'False'],
                'correct' => 1,
                'points'  => 20,
                'order'   => 2,
            ],
            [
                'text'    => 'What is the past tense of "go"?',
                'type'    => 'multiple_choice',
                'options' => ['goed', 'gone', 'went', 'going'],
                'correct' => 2,
                'points'  => 20,
                'order'   => 3,
            ],
            [
                'text'    => '"I have been studying English for 2 years." — This sentence uses the Present Perfect Continuous tense.',
                'type'    => 'true_false',
                'options' => ['True', 'False'],
                'correct' => 0,
                'points'  => 20,
                'order'   => 4,
            ],
            [
                'text'    => 'Describe in 3–5 sentences your daily routine in English.',
                'type'    => 'open_text',
                'options' => [],
                'correct' => 0,
                'points'  => 20,
                'order'   => 5,
            ],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['exam_id' => $exam->id, 'order' => $q['order']],
                $q
            );
        }

        return $exam;
    }

    private function seedExamAttempt(Exam $exam, StudentProfile $studentProfile): void
    {
        $attempt = ExamAttempt::updateOrCreate(
            [
                'exam_id'    => $exam->id,
                'student_id' => $studentProfile->id,
            ],
            [
                'status'       => 'completed',
                'submitted_at' => '2026-02-25 09:55:00',
                'score'        => 80.00,
                'passed'       => true,
            ]
        );

        $questions = Question::where('exam_id', $exam->id)->orderBy('order')->get();

        $studentAnswers = [0 => 2, 1 => 1, 2 => 2, 3 => 0, 4 => 0];

        foreach ($questions as $i => $question) {
            $answer    = $studentAnswers[$i] ?? 0;
            $isCorrect = ($question->type !== 'open_text')
                ? ($answer === $question->correct)
                : null;

            ExamAnswer::updateOrCreate(
                [
                    'attempt_id'  => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'answer'     => $answer,
                    'is_correct' => $isCorrect,
                ]
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GRADES
    // ══════════════════════════════════════════════════════════════════════════
    private function seedGrades(
        StudentProfile $studentProfile,
        TeacherProfile $teacherProfile,
        Course $course,
        ClassGroup $classGroup
    ): void {
        $grades = [
            ['title' => 'Teste de Avaliação — Unidade 1', 'type' => 'Exam',        'grade' => 80.00, 'max_grade' => 100, 'date' => '2026-02-25', 'notes' => 'Bom desempenho. Errou na questão de texto.'],
            ['title' => 'Participação Oral — Janeiro',    'type' => 'Participation','grade' => 85.00, 'max_grade' => 100, 'date' => '2026-01-31', 'notes' => 'Boa participação. Vocabulário em expansão.'],
            ['title' => 'Trabalho de Casa — Unidade 1',   'type' => 'Homework',     'grade' => 90.00, 'max_grade' => 100, 'date' => '2026-02-10', 'notes' => 'Entregue no prazo. Excelente.'],
        ];

        foreach ($grades as $g) {
            Grade::updateOrCreate(
                [
                    'student_id' => $studentProfile->id,
                    'title'      => $g['title'],
                ],
                array_merge($g, [
                    'teacher_id'     => $teacherProfile->id,
                    'course_id'      => $course->id,
                    'class_group_id' => $classGroup->id,
                ])
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FOULS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedFouls(
        StudentProfile $studentProfile,
        Course $course,
        string $createdBy
    ): void {
        Foul::updateOrCreate(
            [
                'student_id'  => $studentProfile->id,
                'description' => 'Falta não justificada — 24 de Janeiro de 2026',
            ],
            [
                'course_id'   => $course->id,
                'amount'      => 2500.00,
                'currency'    => 'AOA',
                'status'      => 'pending',
                'paid_at'     => null,
                'due_date'    => '2026-02-05',
                'created_by'  => $createdBy,
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DOCUMENTS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedDocuments(Course $course, string $uploadedById): void
    {
        $docs = [
            [
                'name'            => 'Programa do Curso B1 2026',
                'type'            => 'PDF',
                'mime_type'       => 'application/pdf',
                'size_bytes'      => 204800,
                'storage_key'     => 'documents/b1-programa-2026.pdf',
                'public_url'      => null,
                'is_public'       => false,
                'downloads'       => 12,
            ],
            [
                'name'            => 'Guia de Estudo — Unidade 1',
                'type'            => 'Document',
                'mime_type'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size_bytes'      => 98304,
                'storage_key'     => 'documents/b1-guia-unidade1.docx',
                'public_url'      => null,
                'is_public'       => true,
                'downloads'       => 7,
            ],
            [
                'name'            => 'Exercícios de Listening — Unidade 1',
                'type'            => 'Audio',
                'mime_type'       => 'audio/mpeg',
                'size_bytes'      => 5242880,
                'storage_key'     => 'documents/b1-listening-unidade1.mp3',
                'public_url'      => null,
                'is_public'       => false,
                'downloads'       => 5,
            ],
        ];

        foreach ($docs as $d) {
            Document::updateOrCreate(
                ['storage_key' => $d['storage_key']],
                array_merge($d, [
                    'course_id'      => $course->id,
                    'uploaded_by_id' => $uploadedById,
                ])
            );
        }
    }
}