<?php
/**
 * Script para Migrar Imagens de src/assets para storage/app/public
 * 
 * Uso: php migrate-images.php
 */

// Configuração de paths
$projectRoot = realpath(__DIR__ . '/..') ?: dirname(__DIR__);

$candidates = [
    $projectRoot . '/src/assets',
    realpath($projectRoot . '/../OLS/src/assets') ?: null,
];

$assetsPath = null;
foreach ($candidates as $candidate) {
    if (is_string($candidate) && $candidate !== '' && is_dir($candidate)) {
        $assetsPath = $candidate;
        break;
    }
}

$coursesStorage = $projectRoot . '/storage/app/public/courses/images';
$blogStorage = $projectRoot . '/storage/app/public/blog/images';

echo "\n🚀 Iniciando migração de imagens...\n\n";

// Criar diretórios se não existirem
if (!is_dir($coursesStorage)) {
    mkdir($coursesStorage, 0755, true);
    echo "✓ Diretório criado: $coursesStorage\n";
}

if (!is_dir($blogStorage)) {
    mkdir($blogStorage, 0755, true);
    echo "✓ Diretório criado: $blogStorage\n";
}

echo "\n";

// Verificar se o diretório de assets existe
if ($assetsPath === null || !is_dir($assetsPath)) {
    echo "❌ Erro: Diretório de assets não encontrado.\n";
    echo "   Tentativas:\n";
    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '') {
            echo "   - $candidate\n";
        }
    }
    echo "\n";
    exit(1);
}

// Função para copiar imagens
function copyImages($pattern, $destination, $label) {
    global $assetsPath;
    $images = glob($assetsPath . '/' . $pattern, GLOB_BRACE);
    $count = 0;
    
    echo "📁 Migrando $label...\n";
    
    foreach ($images as $image) {
        $filename = basename($image);
        $destPath = $destination . '/' . $filename;
        
        if (copy($image, $destPath)) {
            echo "   ✓ $filename\n";
            $count++;
        } else {
            echo "   ❌ Erro ao copiar $filename\n";
        }
    }
    
    echo "   Total: $count arquivos\n\n";
    return $count;
}

// Migrar imagens de cursos
$totalCourses = copyImages('course-*.{jpg,jpeg,png}', $coursesStorage, 'Imagens de Cursos');

// Migrar flyers
$totalFlyers = copyImages('flyer-*.{jpg,jpeg,png}', $coursesStorage, 'Flyers');

// Migrar imagens de blog (se existirem)
$totalBlog = copyImages('blog-*.{jpg,jpeg,png}', $blogStorage, 'Imagens de Blog');

// Migrar outras imagens relevantes
$totalOthers = copyImages('{class,about,hero,team}-*.{jpg,jpeg,png}', $coursesStorage, 'Outras Imagens');

// Resumo
$total = $totalCourses + $totalFlyers + $totalBlog + $totalOthers;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Migração concluída!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "   Total de arquivos migrados: $total\n";
echo "\n";

// Próximos passos
echo "📋 Próximos passos:\n";
echo "   1. Execute: php artisan storage:link\n";
echo "   2. Atualize os URLs no banco de dados\n";
echo "   3. Execute: php artisan cache:clear\n";
echo "   4. Teste: http://localhost/storage/courses/images/course-business.jpg\n";
echo "\n";
