<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@olsangola.ao')->first();
        if (!$admin) {
            $admin = User::first();
        }

        $categories = [
            ['name_pt' => 'Dicas de Estudo', 'name_en' => 'Study Tips', 'slug' => 'dicas-de-estudo'],
            ['name_pt' => 'Notícias da Escola', 'name_en' => 'School News', 'slug' => 'noticias-da-escola'],
            ['name_pt' => 'Inglês para Negócios', 'name_en' => 'Business English', 'slug' => 'ingles-para-negocios'],
        ];

        foreach ($categories as $cat) {
            BlogCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $posts = [
            [
                'title' => 'Como melhorar o seu Speaking em 30 dias',
                'slug' => 'como-melhorar-o-seu-speaking-em-30-dias',
                'category_slug' => 'dicas-de-estudo',
                'excerpt' => 'Descubra técnicas simples e eficazes para ganhar confiança e fluência na fala.',
                'content' => '<h2>1. Pratique todos os dias</h2><p>A consistência é a chave. Fale inglês por pelo menos 15 minutos diariamente.</p><h2>2. Encontre um parceiro de conversação</h2><p>Ter alguém para praticar ajuda a perder o medo.</p>',
                'image_url' => 'assets/about-approach.jpg',
            ],
            [
                'title' => 'Nova turma de Inglês para Negócios',
                'slug' => 'nova-turma-de-ingles-para-negocios',
                'category_slug' => 'noticias-da-escola',
                'excerpt' => 'Estamos a abrir novas inscrições para o curso de Business English. Saiba mais!',
                'content' => '<p>Se você quer dar um salto na sua carreira internacional, a OLS preparou uma turma especial de Business English.</p><p>Vagas limitadas!</p>',
                'image_url' => 'assets/flyer-corporate.png',
            ],
            [
                'title' => 'Expressões essenciais no mundo corporativo',
                'slug' => 'expressoes-essenciais-no-mundo-corporativo',
                'category_slug' => 'ingles-para-negocios',
                'excerpt' => 'Aprenda os idioms mais usados em reuniões de negócios em inglês.',
                'content' => '<p>Expressões como "Think outside the box", "Touch base" e "Get the ball rolling" são fundamentais para entender o que os nativos dizem nas reuniões.</p>',
                'image_url' => 'assets/course-business.jpg',
            ],
        ];

        foreach ($posts as $postData) {
            $cat = BlogCategory::where('slug', $postData['category_slug'])->first();
            BlogPost::firstOrCreate(
                ['slug' => $postData['slug']],
                [
                    'title' => $postData['title'],
                    'excerpt' => $postData['excerpt'],
                    'content' => $postData['content'],
                    'category_id' => $cat->id,
                    'author_id' => $admin->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 10)),
                    'image_url' => $postData['image_url'],
                ]
            );
        }
    }
}
