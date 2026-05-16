<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Question;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $courses = $this->loadCourses();

        $teacher2 = $this->seedTeacher2();
        $teacher3 = $this->seedTeacher3();

        $students = $this->seedStudents();

        $classA1     = $this->seedClassA1($courses['a1'], $teacher2->teacherProfile);
        $classOilGas = $this->seedClassOilGas($courses['oil_gas'], $teacher2->teacherProfile);
        $classBanca  = $this->seedClassBanca($courses['banking'], $teacher3->teacherProfile);

        // Enroll students and seed their data
        $invoiceCounter = 4;

        // Turma A1 — Tarde: Esperança (paid), David (overdue), Inês (pending)
        foreach ([
            ['student' => $students['aluno2'], 'course' => $courses['a1'], 'class' => $classA1, 'mode' => 'paid',    'level' => 'A1'],
            ['student' => $students['aluno5'], 'course' => $courses['a1'], 'class' => $classA1, 'mode' => 'overdue', 'level' => 'A1'],
            ['student' => $students['aluno8'], 'course' => $courses['a1'], 'class' => $classA1, 'mode' => 'pending', 'level' => 'A1'],
        ] as $entry) {
            $sp = $entry['student']->studentProfile;
            $enrollment = $this->seedEnrollment($sp, $entry['course'], $entry['class']);
            $invoiceCounter = $this->seedStudentPayments($sp, $entry['course'], $enrollment, $entry['mode'], $invoiceCounter);
            $this->seedStudentGrades($sp, $teacher2->teacherProfile, $entry['course'], $entry['class']);
            $this->seedStudentAttendance($entry['class'], $sp, $teacher2->teacherProfile);
        }

        // Turma Oil&Gas: Ricardo (pending), Fátima (overdue), Paulo (overdue)
        foreach ([
            ['student' => $students['aluno3'], 'course' => $courses['oil_gas'], 'class' => $classOilGas, 'mode' => 'pending', 'level' => 'B1'],
            ['student' => $students['aluno4'], 'course' => $courses['oil_gas'], 'class' => $classOilGas, 'mode' => 'overdue', 'level' => 'B2'],
            ['student' => $students['aluno7'], 'course' => $courses['oil_gas'], 'class' => $classOilGas, 'mode' => 'overdue', 'level' => 'B2'],
        ] as $entry) {
            $sp = $entry['student']->studentProfile;
            $enrollment = $this->seedEnrollment($sp, $entry['course'], $entry['class']);
            $invoiceCounter = $this->seedStudentPayments($sp, $entry['course'], $enrollment, $entry['mode'], $invoiceCounter);
            $this->seedStudentGrades($sp, $teacher2->teacherProfile, $entry['course'], $entry['class']);
            $this->seedStudentAttendance($entry['class'], $sp, $teacher2->teacherProfile);
        }

        // Turma Banca: Leonor (paid)
        foreach ([
            ['student' => $students['aluno6'], 'course' => $courses['banking'], 'class' => $classBanca, 'mode' => 'paid', 'level' => 'B1'],
        ] as $entry) {
            $sp = $entry['student']->studentProfile;
            $enrollment = $this->seedEnrollment($sp, $entry['course'], $entry['class']);
            $invoiceCounter = $this->seedStudentPayments($sp, $entry['course'], $enrollment, $entry['mode'], $invoiceCounter);
            $this->seedStudentGrades($sp, $teacher3->teacherProfile, $entry['course'], $entry['class']);
            $this->seedStudentAttendance($entry['class'], $sp, $teacher3->teacherProfile);
        }

        // Exams
        $this->seedExamA1($classA1, $courses['a1'], $teacher2->teacherProfile, array_map(
            fn($k) => $students[$k]->studentProfile,
            ['aluno2', 'aluno5', 'aluno8']
        ));
        $this->seedExamOilGas($classOilGas, $courses['oil_gas'], $teacher2->teacherProfile, array_map(
            fn($k) => $students[$k]->studentProfile,
            ['aluno3', 'aluno4', 'aluno7']
        ));

        // Documents for new courses
        $this->seedDocuments($courses['a1'], $teacher2->id, 'a1');
        $this->seedDocuments($courses['oil_gas'], $teacher2->id, 'oil_gas');
        $this->seedDocuments($courses['banking'], $teacher3->id, 'banking');

        $this->call([
            CourseSeeder::class,
            BlogSeeder::class,
        ]);

        $this->command->info('✅  DemoSeeder concluído!');
        $this->command->table(
            ['Perfil', 'Email', 'Senha', 'Situação'],
            [
                ['Professor', 'teacher2@olsangola.ao', 'teacher12345', 'Oil & Gas / B2'],
                ['Professor', 'teacher3@olsangola.ao', 'teacher12345', 'Banca / A1'],
                ['Aluno',     'aluno2@olsangola.ao',   'aluno12345',   'Pago (A1)'],
                ['Aluno',     'aluno3@olsangola.ao',   'aluno12345',   'Pendente (B1)'],
                ['Aluno',     'aluno4@olsangola.ao',   'aluno12345',   'Em atraso (B2)'],
                ['Aluno',     'aluno5@olsangola.ao',   'aluno12345',   'Em atraso (A1)'],
                ['Aluno',     'aluno6@olsangola.ao',   'aluno12345',   'Pago (B1)'],
                ['Aluno',     'aluno7@olsangola.ao',   'aluno12345',   'Em atraso (B2)'],
                ['Aluno',     'aluno8@olsangola.ao',   'aluno12345',   'Pendente (A1)'],
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    private function loadCourses(): array
    {
        return [
            'a1'      => Course::where('slug', 'ingles-comunicacao-diaria-a1')->firstOrFail(),
            'b1'      => Course::where('slug', 'ingles-comunicacao-diaria-b1')->firstOrFail(),
            'oil_gas' => Course::where('slug', 'ingles-oil-gas')->firstOrFail(),
            'banking' => Course::where('slug', 'ingles-banca-financas')->firstOrFail(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEACHERS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedTeacher2(): User
    {
        $t = User::updateOrCreate(
            ['email' => 'teacher2@olsangola.ao'],
            [
                'first_name'         => 'Bruno',
                'last_name'          => 'Santos',
                'phone'              => '+244 923 200 022',
                'password'           => Hash::make('teacher12345'),
                'role'               => 'teacher',
                'status'             => 'active',
                'country'            => 'AO',
                'preferred_language' => 'pt',
                'preferred_currency' => 'AOA',
                'email_verified_at'  => now(),
                'last_login_at'      => now()->subDays(2),
            ]
        );
        $t->syncRoles('teacher');
        TeacherProfile::updateOrCreate(
            ['user_id' => $t->id],
            [
                'teacher_code'    => 'TCH-002',
                'bio'             => 'Professor de inglês técnico com experiência no sector petrolífero. 6 anos a lecionar inglês Oil & Gas em Luanda.',
                'certifications'  => ['IELTS 7.5', 'Oil & Gas Communication Certificate'],
                'specializations' => ['Oil & Gas English', 'B2 Advanced', 'Technical Writing'],
                'hire_date'       => '2021-06-01',
                'salary'          => 230000.00,
                'salary_currency' => 'AOA',
            ]
        );
        return $t;
    }

    private function seedTeacher3(): User
    {
        $t = User::updateOrCreate(
            ['email' => 'teacher3@olsangola.ao'],
            [
                'first_name'         => 'Maria',
                'last_name'          => 'Neto',
                'phone'              => '+244 923 200 033',
                'password'           => Hash::make('teacher12345'),
                'role'               => 'teacher',
                'status'             => 'active',
                'country'            => 'AO',
                'preferred_language' => 'pt',
                'preferred_currency' => 'AOA',
                'email_verified_at'  => now(),
                'last_login_at'      => now()->subDays(1),
            ]
        );
        $t->syncRoles('teacher');
        TeacherProfile::updateOrCreate(
            ['user_id' => $t->id],
            [
                'teacher_code'    => 'TCH-003',
                'bio'             => 'Professora especializada em inglês para banca e finanças. Formação em Economia e certificação Cambridge.',
                'certifications'  => ['CELTA', 'Cambridge TKT', 'Financial English Certificate'],
                'specializations' => ['Banking & Finance English', 'A1 Beginner', 'Business Writing'],
                'hire_date'       => '2022-09-01',
                'salary'          => 220000.00,
                'salary_currency' => 'AOA',
            ]
        );
        return $t;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STUDENTS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedStudents(): array
    {
        $defs = [
            'aluno2' => ['first_name' => 'Esperança', 'last_name' => 'Tchiami',  'email' => 'aluno2@olsangola.ao', 'phone' => '+244 923 300 022', 'code' => 'STU-002', 'level' => 'A1'],
            'aluno3' => ['first_name' => 'Ricardo',   'last_name' => 'Nkosi',    'email' => 'aluno3@olsangola.ao', 'phone' => '+244 923 300 033', 'code' => 'STU-003', 'level' => 'B1'],
            'aluno4' => ['first_name' => 'Fátima',    'last_name' => 'Cardoso',  'email' => 'aluno4@olsangola.ao', 'phone' => '+244 923 300 044', 'code' => 'STU-004', 'level' => 'B2'],
            'aluno5' => ['first_name' => 'David',     'last_name' => 'Nunes',    'email' => 'aluno5@olsangola.ao', 'phone' => '+244 923 300 055', 'code' => 'STU-005', 'level' => 'A1'],
            'aluno6' => ['first_name' => 'Leonor',    'last_name' => 'Augusto',  'email' => 'aluno6@olsangola.ao', 'phone' => '+244 923 300 066', 'code' => 'STU-006', 'level' => 'B1'],
            'aluno7' => ['first_name' => 'Paulo',     'last_name' => 'Ferreira', 'email' => 'aluno7@olsangola.ao', 'phone' => '+244 923 300 077', 'code' => 'STU-007', 'level' => 'B2'],
            'aluno8' => ['first_name' => 'Inês',      'last_name' => 'Rodrigues','email' => 'aluno8@olsangola.ao', 'phone' => '+244 923 300 088', 'code' => 'STU-008', 'level' => 'A1'],
        ];

        $created = [];
        foreach ($defs as $key => $d) {
            $u = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'first_name'         => $d['first_name'],
                    'last_name'          => $d['last_name'],
                    'phone'              => $d['phone'],
                    'password'           => Hash::make('aluno12345'),
                    'role'               => 'student',
                    'status'             => 'active',
                    'country'            => 'AO',
                    'preferred_language' => 'pt',
                    'preferred_currency' => 'AOA',
                    'email_verified_at'  => now(),
                    'last_login_at'      => now()->subDays(rand(1, 7)),
                ]
            );
            $u->syncRoles('student');
            StudentProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'student_code'    => $d['code'],
                    'current_level'   => $d['level'],
                    'enrollment_date' => '2026-01-20',
                    'notes'           => null,
                ]
            );
            $created[$key] = $u;
        }
        return $created;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLASS GROUPS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedClassA1(Course $course, TeacherProfile $tp): ClassGroup
    {
        $cg = ClassGroup::updateOrCreate(
            ['name' => 'Turma A1 — Tarde 2026'],
            ['course_id' => $course->id, 'teacher_id' => $tp->id, 'year' => 2026, 'capacity' => 8, 'room' => 'Sala 03', 'is_active' => true]
        );
        foreach ([['day_of_week' => 2, 'start' => '14:00', 'end' => '16:00'], ['day_of_week' => 4, 'start' => '14:00', 'end' => '16:00']] as $s) {
            ClassSchedule::updateOrCreate(
                ['class_group_id' => $cg->id, 'day_of_week' => $s['day_of_week']],
                ['start_time' => $s['start'], 'end_time' => $s['end'], 'room' => 'Sala 03', 'is_recurring' => true]
            );
        }
        return $cg;
    }

    private function seedClassOilGas(Course $course, TeacherProfile $tp): ClassGroup
    {
        $cg = ClassGroup::updateOrCreate(
            ['name' => 'Turma Oil&Gas — Manhã 2026'],
            ['course_id' => $course->id, 'teacher_id' => $tp->id, 'year' => 2026, 'capacity' => 6, 'room' => 'Sala 04', 'is_active' => true]
        );
        foreach ([['day_of_week' => 1, 'start' => '09:00', 'end' => '11:00'], ['day_of_week' => 3, 'start' => '09:00', 'end' => '11:00']] as $s) {
            ClassSchedule::updateOrCreate(
                ['class_group_id' => $cg->id, 'day_of_week' => $s['day_of_week']],
                ['start_time' => $s['start'], 'end_time' => $s['end'], 'room' => 'Sala 04', 'is_recurring' => true]
            );
        }
        return $cg;
    }

    private function seedClassBanca(Course $course, TeacherProfile $tp): ClassGroup
    {
        $cg = ClassGroup::updateOrCreate(
            ['name' => 'Turma Banca — Noite 2026'],
            ['course_id' => $course->id, 'teacher_id' => $tp->id, 'year' => 2026, 'capacity' => 8, 'room' => 'Sala 05', 'is_active' => true]
        );
        foreach ([['day_of_week' => 2, 'start' => '18:00', 'end' => '20:00'], ['day_of_week' => 4, 'start' => '18:00', 'end' => '20:00']] as $s) {
            ClassSchedule::updateOrCreate(
                ['class_group_id' => $cg->id, 'day_of_week' => $s['day_of_week']],
                ['start_time' => $s['start'], 'end_time' => $s['end'], 'room' => 'Sala 05', 'is_recurring' => true]
            );
        }
        return $cg;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENROLLMENT
    // ══════════════════════════════════════════════════════════════════════════
    private function seedEnrollment(StudentProfile $sp, Course $course, ClassGroup $cg): Enrollment
    {
        return Enrollment::updateOrCreate(
            ['student_id' => $sp->id, 'course_id' => $course->id],
            [
                'class_group_id' => $cg->id,
                'start_date'     => '2026-01-20',
                'end_date'       => '2026-05-20',
                'status'         => 'active',
                'progress_pct'   => rand(20, 70),
                'notes'          => null,
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYMENTS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedStudentPayments(
        StudentProfile $sp,
        Course $course,
        Enrollment $enrollment,
        string $mode,
        int $counter
    ): int {
        $price = $course->price_aoa;
        $courseName = $course->title_pt;

        $months = [
            ['desc' => "Mensalidade Janeiro 2026 — {$courseName}", 'due' => '2026-01-20', 'invoice_suffix' => str_pad($counter,     4, '0', STR_PAD_LEFT)],
            ['desc' => "Mensalidade Fevereiro 2026 — {$courseName}", 'due' => '2026-02-20', 'invoice_suffix' => str_pad($counter + 1, 4, '0', STR_PAD_LEFT)],
            ['desc' => "Mensalidade Março 2026 — {$courseName}",    'due' => '2026-03-20', 'invoice_suffix' => str_pad($counter + 2, 4, '0', STR_PAD_LEFT)],
        ];

        $statuses = match ($mode) {
            'paid'    => ['paid',    'paid',    'paid'],
            'pending' => ['paid',    'paid',    'pending'],
            'overdue' => ['paid',    'overdue', 'overdue'],
            default   => ['paid',    'paid',    'pending'],
        };

        $paidAts = [
            'paid'    => ['2026-01-18 10:00:00', '2026-02-17 10:00:00', '2026-03-18 10:00:00'],
            'pending' => ['2026-01-18 10:00:00', '2026-02-17 10:00:00', null],
            'overdue' => ['2026-01-18 10:00:00', null, null],
        ];

        foreach ($months as $i => $m) {
            $status = $statuses[$i];
            $paidAt = $paidAts[$mode][$i] ?? null;

            Payment::updateOrCreate(
                ['invoice_number' => 'OLS-2026-' . $m['invoice_suffix']],
                [
                    'student_id'    => $sp->id,
                    'course_id'     => $course->id,
                    'enrollment_id' => $enrollment->id,
                    'description'   => $m['desc'],
                    'amount'        => $price,
                    'currency'      => 'AOA',
                    'amount_aoa'    => $price,
                    'due_date'      => $m['due'],
                    'paid_at'       => $paidAt,
                    'status'        => $status,
                    'method'        => $paidAt ? 'multicaixa' : null,
                    'transaction_ref' => $paidAt ? 'MCX-2026-' . $m['invoice_suffix'] : null,
                    'notes'         => $status === 'overdue' ? 'Pagamento em atraso. Contactar aluno.' : null,
                ]
            );
        }

        return $counter + 3;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GRADES
    // ══════════════════════════════════════════════════════════════════════════
    private function seedStudentGrades(
        StudentProfile $sp,
        TeacherProfile $tp,
        Course $course,
        ClassGroup $cg
    ): void {
        $grades = [
            ['title' => "Teste de Avaliação — {$course->level} Unidade 1", 'type' => 'Exam',         'grade' => rand(55, 95), 'max_grade' => 100, 'date' => '2026-02-25'],
            ['title' => "Participação Oral — {$course->level} Janeiro",    'type' => 'Participation', 'grade' => rand(60, 95), 'max_grade' => 100, 'date' => '2026-01-31'],
            ['title' => "Trabalho de Casa — {$course->level} Unidade 1",   'type' => 'Homework',      'grade' => rand(65, 98), 'max_grade' => 100, 'date' => '2026-02-10'],
        ];

        foreach ($grades as $g) {
            Grade::updateOrCreate(
                ['student_id' => $sp->id, 'title' => $g['title']],
                array_merge($g, [
                    'teacher_id'     => $tp->id,
                    'course_id'      => $course->id,
                    'class_group_id' => $cg->id,
                    'notes'          => null,
                ])
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ATTENDANCE
    // ══════════════════════════════════════════════════════════════════════════
    private function seedStudentAttendance(ClassGroup $cg, StudentProfile $sp, TeacherProfile $tp): void
    {
        $records = [
            ['date' => '2026-01-21', 'status' => 'present',  'notes' => null],
            ['date' => '2026-01-23', 'status' => 'present',  'notes' => null],
            ['date' => '2026-01-28', 'status' => 'absent',   'notes' => 'Falta não justificada.'],
            ['date' => '2026-01-30', 'status' => 'present',  'notes' => null],
            ['date' => '2026-02-04', 'status' => 'late',     'notes' => 'Chegou 10 minutos atrasado.'],
            ['date' => '2026-02-06', 'status' => 'present',  'notes' => null],
            ['date' => '2026-02-11', 'status' => 'present',  'notes' => null],
            ['date' => '2026-02-13', 'status' => 'present',  'notes' => null],
        ];

        foreach ($records as $r) {
            Attendance::updateOrCreate(
                ['class_group_id' => $cg->id, 'student_id' => $sp->id, 'date' => $r['date']],
                ['teacher_id' => $tp->id, 'status' => $r['status'], 'notes' => $r['notes']]
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXAMS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedExamA1(ClassGroup $cg, Course $course, TeacherProfile $tp, array $students): void
    {
        $exam = Exam::updateOrCreate(
            ['title' => 'Teste Final A1 — Unidade 2'],
            [
                'class_group_id' => $cg->id,
                'course_id'      => $course->id,
                'teacher_id'     => $tp->id,
                'duration'       => 45,
                'max_attempts'   => 2,
                'status'         => 'published',
                'due_date'       => '2026-03-15 14:00:00',
                'total_points'   => 100,
                'pass_score'     => 50,
            ]
        );

        $questions = [
            ['text' => 'My name ___ Maria.', 'type' => 'multiple_choice', 'options' => ['am', 'is', 'are', 'be'], 'correct' => 1, 'points' => 20, 'order' => 1],
            ['text' => '"Good morning" is a greeting used in the evening.', 'type' => 'true_false', 'options' => ['True', 'False'], 'correct' => 1, 'points' => 20, 'order' => 2],
            ['text' => 'How do you say "obrigado" in English?', 'type' => 'multiple_choice', 'options' => ['please', 'sorry', 'thank you', 'hello'], 'correct' => 2, 'points' => 20, 'order' => 3],
            ['text' => 'The plural of "child" is "childs".', 'type' => 'true_false', 'options' => ['True', 'False'], 'correct' => 1, 'points' => 20, 'order' => 4],
            ['text' => 'Write 3 sentences about yourself in English.', 'type' => 'open_text', 'options' => [], 'correct' => 0, 'points' => 20, 'order' => 5],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['exam_id' => $exam->id, 'order' => $q['order']],
                $q
            );
        }

        $questionModels = Question::where('exam_id', $exam->id)->orderBy('order')->get();
        $answers = [0 => 1, 1 => 1, 2 => 2, 3 => 1, 4 => 0];

        foreach ($students as $sp) {
            $attempt = ExamAttempt::updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $sp->id],
                ['status' => 'completed', 'submitted_at' => '2026-03-14 15:30:00', 'score' => rand(50, 90), 'passed' => true]
            );
            foreach ($questionModels as $i => $q) {
                $ans = $answers[$i] ?? 0;
                ExamAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                    ['answer' => $ans, 'is_correct' => $q->type !== 'open_text' ? ($ans === $q->correct) : null]
                );
            }
        }
    }

    private function seedExamOilGas(ClassGroup $cg, Course $course, TeacherProfile $tp, array $students): void
    {
        $exam = Exam::updateOrCreate(
            ['title' => 'Avaliação Oil & Gas — Módulo 1'],
            [
                'class_group_id' => $cg->id,
                'course_id'      => $course->id,
                'teacher_id'     => $tp->id,
                'duration'       => 90,
                'max_attempts'   => 1,
                'status'         => 'published',
                'due_date'       => '2026-03-20 09:00:00',
                'total_points'   => 100,
                'pass_score'     => 60,
            ]
        );

        $questions = [
            ['text' => 'What does "upstream" mean in the oil & gas industry?', 'type' => 'multiple_choice', 'options' => ['Refining', 'Exploration and production', 'Distribution', 'Retail sales'], 'correct' => 1, 'points' => 20, 'order' => 1],
            ['text' => 'An HSE report focuses on Health, Safety and Environment.', 'type' => 'true_false', 'options' => ['True', 'False'], 'correct' => 0, 'points' => 20, 'order' => 2],
            ['text' => 'Which word means "perfuração"?', 'type' => 'multiple_choice', 'options' => ['flaring', 'drilling', 'piping', 'refining'], 'correct' => 1, 'points' => 20, 'order' => 3],
            ['text' => 'A "wellhead" is located offshore only.', 'type' => 'true_false', 'options' => ['True', 'False'], 'correct' => 1, 'points' => 20, 'order' => 4],
            ['text' => 'Write an email to your supervisor requesting a safety inspection of the rig.', 'type' => 'open_text', 'options' => [], 'correct' => 0, 'points' => 20, 'order' => 5],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['exam_id' => $exam->id, 'order' => $q['order']],
                $q
            );
        }

        $questionModels = Question::where('exam_id', $exam->id)->orderBy('order')->get();
        $answers = [0 => 1, 1 => 0, 2 => 1, 3 => 1, 4 => 0];

        foreach ($students as $sp) {
            $attempt = ExamAttempt::updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $sp->id],
                ['status' => 'completed', 'submitted_at' => '2026-03-19 10:00:00', 'score' => rand(55, 85), 'passed' => true]
            );
            foreach ($questionModels as $i => $q) {
                $ans = $answers[$i] ?? 0;
                ExamAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                    ['answer' => $ans, 'is_correct' => $q->type !== 'open_text' ? ($ans === $q->correct) : null]
                );
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DOCUMENTS
    // ══════════════════════════════════════════════════════════════════════════
    private function seedDocuments(Course $course, string $uploadedById, string $courseKey): void
    {
        $docs = [
            [
                'name'        => "Programa do Curso {$course->level} 2026",
                'type'        => 'PDF',
                'mime_type'   => 'application/pdf',
                'size_bytes'  => 204800,
                'storage_key' => "documents/{$courseKey}-programa-2026.pdf",
                'is_public'   => false,
                'downloads'   => rand(3, 20),
            ],
            [
                'name'        => "Guia de Estudo {$course->level} — Unidade 1",
                'type'        => 'Document',
                'mime_type'   => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size_bytes'  => 98304,
                'storage_key' => "documents/{$courseKey}-guia-unidade1.docx",
                'is_public'   => true,
                'downloads'   => rand(2, 15),
            ],
        ];

        foreach ($docs as $d) {
            Document::updateOrCreate(
                ['storage_key' => $d['storage_key']],
                array_merge($d, ['course_id' => $course->id, 'uploaded_by_id' => $uploadedById, 'public_url' => null])
            );
        }
    }
}
