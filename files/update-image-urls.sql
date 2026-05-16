-- =========================================
-- Script SQL: Atualizar URLs de Imagens
-- =========================================
-- Execute após migrar as imagens para storage

SET @base_url = 'http://localhost:8000';

-- 1. Ver status atual dos cursos
SELECT 
    id, 
    title_pt,
    title_en,
    service_type,
    level,
    image_url, 
    flyer_url,
    is_active,
    visibility_status
FROM courses
ORDER BY title_pt;

-- =========================================
-- 2. MIGRAR URLs ANTIGAS (src/assets → storage)
-- =========================================

UPDATE courses 
SET image_url = CONCAT(
    @base_url,
    '/storage/courses/images/',
    SUBSTRING_INDEX(REPLACE(image_url, '\\', '/'), '/', -1)
)
WHERE image_url IS NOT NULL
  AND image_url <> ''
  AND (
    image_url LIKE '%src/assets/%'
    OR image_url LIKE '%/assets/%'
    OR image_url LIKE 'assets/%'
  );

UPDATE courses 
SET flyer_url = CONCAT(
    @base_url,
    '/storage/courses/images/',
    SUBSTRING_INDEX(REPLACE(flyer_url, '\\', '/'), '/', -1)
)
WHERE flyer_url IS NOT NULL
  AND flyer_url <> ''
  AND (
    flyer_url LIKE '%src/assets/%'
    OR flyer_url LIKE '%/assets/%'
    OR flyer_url LIKE 'assets/%'
  );

UPDATE blog_posts 
SET image_url = CONCAT(
    @base_url,
    '/storage/blog/images/',
    SUBSTRING_INDEX(REPLACE(image_url, '\\', '/'), '/', -1)
)
WHERE image_url IS NOT NULL
  AND image_url <> ''
  AND (
    image_url LIKE '%src/assets/%'
    OR image_url LIKE '%/assets/%'
    OR image_url LIKE 'assets/%'
  );

-- =========================================
-- 3. DEFINIR IMAGENS PADRÃO (onde estiver vazio)
-- =========================================

UPDATE courses 
SET image_url = CONCAT(
    @base_url,
    '/storage/courses/images/',
    CASE
        WHEN UPPER(level) IN ('A1', 'A2') THEN 'course-beginner.jpg'
        WHEN UPPER(level) IN ('B1', 'B2') THEN 'course-intermediate.jpg'
        WHEN UPPER(level) IN ('C1', 'C2') THEN 'course-advanced.jpg'
        ELSE 'course-business.jpg'
    END
)
WHERE image_url IS NULL OR image_url = '';

-- =========================================
-- 4. ATUALIZAR FLYERS POR TIPO DE SERVIÇO
-- =========================================

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-group.png')
WHERE service_type = 'group_daily_communication'
  AND (flyer_url IS NULL OR flyer_url = '');

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-banking.png')
WHERE service_type IN ('banking_finance', 'oil_gas')
  AND (flyer_url IS NULL OR flyer_url = '');

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-corporate.png')
WHERE service_type = 'corporate'
  AND (flyer_url IS NULL OR flyer_url = '');

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-individual.png')
WHERE service_type = 'individual'
  AND (flyer_url IS NULL OR flyer_url = '');

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-kanuca.png')
WHERE service_type = 'kanuca'
  AND (flyer_url IS NULL OR flyer_url = '');

UPDATE courses 
SET flyer_url = CONCAT(@base_url, '/storage/courses/images/flyer-oilgas.png')
WHERE service_type = 'oil_gas'
  AND (flyer_url IS NULL OR flyer_url = '');

-- =========================================
-- 5. PUBLICAR CURSOS (se necessário)
-- =========================================

-- Ver cursos não publicados
SELECT id, title_pt, is_active, visibility_status, published_at
FROM courses
WHERE visibility_status != 'published';

-- Publicar todos os cursos em rascunho
-- ATENÇÃO: Remova o comentário abaixo apenas se quiser publicar TODOS
-- UPDATE courses 
-- SET 
--     is_active = 1,
--     visibility_status = 'published',
--     published_at = NOW()
-- WHERE visibility_status = 'draft' 
--   AND deleted_at IS NULL;

-- Publicar curso específico (substitua 'CURSO_ID_AQUI')
-- UPDATE courses 
-- SET 
--     is_active = 1,
--     visibility_status = 'published',
--     published_at = NOW()
-- WHERE id = 'CURSO_ID_AQUI';

-- =========================================
-- 6. VERIFICAÇÃO FINAL
-- =========================================

-- Ver cursos com imagens atualizadas
SELECT 
    title_pt,
    level,
    service_type,
    image_url,
    flyer_url,
    is_active,
    visibility_status
FROM courses
WHERE image_url IS NOT NULL
ORDER BY title_pt;

-- Ver cursos ainda sem imagem
SELECT 
    id,
    title_pt,
    level,
    service_type
FROM courses
WHERE image_url IS NULL OR image_url = '';

-- Ver quantos cursos estão publicados e visíveis no site
SELECT 
    COUNT(*) as total_visiveis,
    COUNT(CASE WHEN image_url IS NOT NULL THEN 1 END) as com_imagem
FROM courses
WHERE is_active = 1
  AND visibility_status IN ('published', 'scheduled')
  AND (published_at IS NULL OR published_at <= NOW())
  AND deleted_at IS NULL;

-- =========================================
-- 7. BLOG POSTS (se necessário)
-- =========================================

-- Ver posts sem imagem
SELECT id, title, status, image_url
FROM blog_posts
WHERE image_url IS NULL OR image_url = '';

-- Atualizar posts com imagem padrão
-- UPDATE blog_posts 
-- SET image_url = 'http://localhost/storage/blog/images/default-blog.jpg'
-- WHERE image_url IS NULL OR image_url = '';
