<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\TeacherProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = TeacherProfile::first();
        $teacherId = $teacher ? $teacher->id : null;

        $courses = [
            [
                'title_en' => 'General English',
                'title_pt' => 'Inglês Geral',
                'description_en' => 'Build a strong foundation in English with comprehensive lessons covering speaking, listening, reading, and writing skills.',
                'description_pt' => 'Construa uma base forte em inglês com lições compreensivas cobrindo habilidades de fala, escuta, leitura e escrita.',
                'level' => 'beginner',
                'duration' => '12 weeks',
                'service_type' => 'group_daily_communication',
                'image_url' => 'assets/course-beginner.jpg',
                'price_aoa' => 45000,
            ],
            [
                'title_en' => 'Business English',
                'title_pt' => 'Inglês para Negócios',
                'description_en' => 'Master professional English for the workplace including presentations, negotiations, and business correspondence.',
                'description_pt' => 'Domine o inglês profissional para o ambiente de trabalho, incluindo apresentações, negociações e correspondência comercial.',
                'level' => 'intermediate',
                'duration' => '16 weeks',
                'service_type' => 'corporate',
                'image_url' => 'assets/course-business.jpg',
                'flyer_url' => 'assets/flyer-corporate.png',
                'price_aoa' => 85000,
            ],
            [
                'title_en' => 'Conversational English',
                'title_pt' => 'Inglês para Conversação',
                'description_en' => 'Improve your fluency and confidence in everyday conversations with native-speaking instructors.',
                'description_pt' => 'Melhore sua fluência e confiança nas conversas do dia a dia com instrutores falantes nativos.',
                'level' => 'intermediate',
                'duration' => '8 weeks',
                'service_type' => 'group_daily_communication',
                'image_url' => 'assets/course-conversation.jpg',
                'price_aoa' => 40000,
            ],
            [
                'title_en' => 'IELTS Preparation',
                'title_pt' => 'Preparação para IELTS',
                'description_en' => 'Prepare for the IELTS exam with targeted practice in all four skills and test-taking strategies.',
                'description_pt' => 'Prepare-se para o exame IELTS com prática direcionada nas quatro habilidades e estratégias para a prova.',
                'level' => 'advanced',
                'duration' => '12 weeks',
                'service_type' => 'individual',
                'image_url' => 'assets/course-ielts.jpg',
                'price_aoa' => 95000,
            ],
            [
                'title_en' => 'English for Kids',
                'title_pt' => 'Inglês para Crianças (Kanuca)',
                'description_en' => 'Fun and interactive English lessons designed specifically for children aged 6-12 years.',
                'description_pt' => 'Lições divertidas e interativas de inglês projetadas especificamente para crianças de 6 a 12 anos.',
                'level' => 'beginner',
                'duration' => '20 weeks',
                'service_type' => 'kanuca',
                'image_url' => 'assets/class-3.jpg',
                'flyer_url' => 'assets/flyer-kanuca.png',
                'price_aoa' => 35000,
            ],
            [
                'title_en' => 'Advanced Writing',
                'title_pt' => 'Escrita Avançada',
                'description_en' => 'Enhance your writing skills for academic and professional purposes with expert guidance.',
                'description_pt' => 'Aprimore suas habilidades de escrita para fins acadêmicos e profissionais com orientação especializada.',
                'level' => 'advanced',
                'duration' => '10 weeks',
                'service_type' => 'individual',
                'image_url' => 'assets/course-advanced.jpg',
                'price_aoa' => 50000,
            ]
        ];

        foreach ($courses as $data) {
            $slug = Str::slug($data['title_en']) . '-' . Str::random(5);
            Course::firstOrCreate(
                ['title_en' => $data['title_en']],
                [
                    'title_pt' => $data['title_pt'],
                    'slug' => $slug,
                    'description_pt' => $data['description_pt'],
                    'description_en' => $data['description_en'],
                    'level' => $data['level'],
                    'duration' => $data['duration'],
                    'service_type' => $data['service_type'],
                    'image_url' => $data['image_url'],
                    'flyer_url' => $data['flyer_url'] ?? null,
                    'price_aoa' => $data['price_aoa'],
                    'price_eur' => $data['price_aoa'] / 1000, // mock eur
                    'price_usd' => $data['price_aoa'] / 900, // mock usd
                    'is_active' => true,
                    'visibility_status' => 'published',
                    'published_at' => now(),
                    'prerequisites' => 'Nenhum',
                    'syllabus' => 'Conteúdo programático em desenvolvimento...',
                    'required_material' => 'Caderno e Caneta',
                    'available_seats' => 10,
                    'start_date' => now()->addDays(15),
                    'responsible_teacher_id' => $teacherId,
                ]
            );
        }
    }
}
