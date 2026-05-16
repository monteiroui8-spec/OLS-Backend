# Soluções para Problemas de Imagens e Publicação de Cursos

## 📋 Problemas Identificados

1. **Imagens não aparecem no admin** (cursos e blog)
2. **Imagens estão em `c:\xampp\htdocs\OLS\src\assets\`** (diretório local do frontend)
3. **Dúvida se cursos publicados no admin aparecem no site**

---

## 🔍 Análise do Sistema

### Estrutura Atual

**Backend (Laravel):**
- Armazena apenas o **caminho/URL** da imagem no banco de dados (campos `image_url` e `flyer_url`)
- Upload de imagens vai para `storage/app/public/courses/images/` e `storage/app/public/blog/images/`
- Controllers possuem métodos `uploadImage()` que retornam URLs

**Frontend (React/Vue):**
- Imagens estáticas estão em `src/assets/`
- Essas imagens são apenas para exibição estática/demo

**Fluxo de Publicação:**
- Cursos com `visibility_status = 'published'` e `published_at <= now()` aparecem no site
- O método `visibleOnSite()` filtra cursos publicados
- Cache de 30 minutos para cursos públicos

---

## ✅ SOLUÇÃO 1: Configurar Upload de Imagens no Admin

### Passo 1: Verificar Storage Link do Laravel

No servidor/XAMPP, execute:

```bash
cd c:\xampp\htdocs\OLS
php artisan storage:link
```

Isso cria um link simbólico: `public/storage` → `storage/app/public`

### Passo 2: Verificar Permissões

No Windows (XAMPP), verifique que a pasta tem permissões:

```bash
# No PowerShell ou CMD
icacls "c:\xampp\htdocs\OLS\storage\app\public" /grant Everyone:F /T
```

### Passo 3: Verificar .env

Certifique-se de que o `.env` tem:

```env
APP_URL=http://localhost
FILESYSTEM_DISK=public
```

### Passo 4: Testar Upload no Admin

Ao criar/editar um curso ou post de blog:

1. Clique em "Upload de Imagem"
2. Selecione uma imagem
3. A API deve retornar algo como:
   ```json
   {
     "url": "http://localhost/storage/courses/images/xyz123.jpg"
   }
   ```

---

## ✅ SOLUÇÃO 2: Migrar Imagens de Assets para Storage

As imagens em `src/assets/` são apenas para o frontend. Para usá-las no admin:

### Opção A: Copiar Manualmente

```bash
# Copiar imagens de cursos
copy c:\xampp\htdocs\OLS\src\assets\course-*.jpg c:\xampp\htdocs\OLS\storage\app\public\courses\images\

# Copiar flyers
copy c:\xampp\htdocs\OLS\src\assets\flyer-*.png c:\xampp\htdocs\OLS\storage\app\public\courses\images\

# Copiar imagens de blog (se houver)
copy c:\xampp\htdocs\OLS\src\assets\blog-*.jpg c:\xampp\htdocs\OLS\storage\app\public\blog\images\
```

### Opção B: Script de Migração

Crie um arquivo `migrate-images.php` na raiz do projeto:

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$assetsPath = base_path('src/assets');
$coursesStorage = storage_path('app/public/courses/images');
$blogStorage = storage_path('app/public/blog/images');

// Criar diretórios se não existirem
if (!is_dir($coursesStorage)) mkdir($coursesStorage, 0755, true);
if (!is_dir($blogStorage)) mkdir($blogStorage, 0755, true);

// Migrar imagens de cursos
$courseImages = glob($assetsPath . '/course-*.{jpg,png}', GLOB_BRACE);
foreach ($courseImages as $image) {
    $filename = basename($image);
    copy($image, $coursesStorage . '/' . $filename);
    echo "✓ Copiado: $filename\n";
}

// Migrar flyers
$flyers = glob($assetsPath . '/flyer-*.{jpg,png}', GLOB_BRACE);
foreach ($flyers as $flyer) {
    $filename = basename($flyer);
    copy($flyer, $coursesStorage . '/' . $filename);
    echo "✓ Copiado: $filename\n";
}

echo "\n✅ Migração concluída!\n";
```

Execute:
```bash
php migrate-images.php
```

---

## ✅ SOLUÇÃO 3: Atualizar URLs no Banco de Dados

Depois de migrar as imagens, atualize os registros existentes:

```sql
-- Ver cursos sem imagem
SELECT id, title_pt, image_url, flyer_url FROM courses;

-- Atualizar URLs de exemplo (ajuste conforme necessário)
UPDATE courses 
SET image_url = 'http://localhost/storage/courses/images/course-business.jpg'
WHERE title_en LIKE '%Business%' AND image_url IS NULL;

UPDATE courses 
SET image_url = 'http://localhost/storage/courses/images/course-beginner.jpg'
WHERE title_en LIKE '%Beginner%' AND image_url IS NULL;

-- Atualizar flyers
UPDATE courses 
SET flyer_url = 'http://localhost/storage/courses/images/flyer-group.png'
WHERE service_type = 'group_daily_communication' AND flyer_url IS NULL;
```

Ou via Tinker:

```bash
php artisan tinker
```

```php
// Atualizar todos os cursos com imagens padrão
$courses = App\Models\Course::whereNull('image_url')->get();
foreach ($courses as $course) {
    $imageName = 'course-' . strtolower(str_replace(' ', '-', $course->level)) . '.jpg';
    $course->image_url = url('storage/courses/images/' . $imageName);
    $course->save();
}
```

---

## ✅ SOLUÇÃO 4: Garantir que Cursos Publicados Apareçam no Site

### Verificar Status de Publicação

```sql
-- Ver cursos e seus status
SELECT 
    id, 
    title_pt, 
    is_active, 
    visibility_status, 
    published_at,
    deleted_at
FROM courses;
```

### Regras para Aparecer no Site

Um curso aparece no site quando:

1. ✅ `is_active = 1` (true)
2. ✅ `visibility_status IN ('published', 'scheduled')`
3. ✅ `published_at IS NULL OR published_at <= NOW()`
4. ✅ `deleted_at IS NULL` (não está deletado)

### Publicar um Curso via Admin

No admin, ao criar/editar um curso:

```json
{
  "is_active": true,
  "visibility_status": "published",
  "published_at": null  // ou data no passado/presente
}
```

### Publicar via SQL (se necessário)

```sql
-- Publicar curso específico
UPDATE courses 
SET 
    is_active = 1,
    visibility_status = 'published',
    published_at = NOW()
WHERE id = 'SEU_CURSO_ID';

-- Publicar todos os cursos em rascunho
UPDATE courses 
SET 
    visibility_status = 'published',
    published_at = NOW()
WHERE visibility_status = 'draft' AND deleted_at IS NULL;
```

### Limpar Cache

Depois de publicar, limpe o cache:

```bash
php artisan cache:forget public_courses_pt
php artisan cache:forget public_courses_en
```

Ou via código (o controller já faz isso automaticamente):

```php
Cache::forget('public_courses_pt');
Cache::forget('public_courses_en');
```

---

## 🧪 Testes de Verificação

### 1. Testar API Pública de Cursos

```bash
# Listar cursos públicos
curl http://localhost/api/public/courses

# Deve retornar JSON com cursos publicados
```

### 2. Testar Upload no Admin

```bash
# Endpoint de upload (requer autenticação)
POST http://localhost/api/admin/courses/upload-image
Content-Type: multipart/form-data

image: [arquivo]
```

### 3. Verificar Imagens Acessíveis

Abra no navegador:
```
http://localhost/storage/courses/images/course-business.jpg
http://localhost/storage/courses/images/flyer-group.png
```

---

## 📁 Estrutura de Diretórios Correta

```
c:\xampp\htdocs\OLS\
├── public/
│   ├── index.php
│   └── storage/  ← Link simbólico para storage/app/public
│
├── storage/
│   └── app/
│       └── public/
│           ├── courses/
│           │   └── images/
│           │       ├── course-business.jpg
│           │       ├── flyer-group.png
│           │       └── [outras imagens]
│           │
│           └── blog/
│               └── images/
│                   └── [imagens do blog]
│
└── src/  ← Frontend (React/Vue)
    └── assets/  ← Imagens estáticas do frontend
        ├── course-*.jpg
        └── flyer-*.png
```

---

## 🚨 Checklist de Troubleshooting

- [ ] `php artisan storage:link` executado
- [ ] Pasta `storage/app/public` tem permissões corretas
- [ ] Arquivo `.env` tem `APP_URL` correto
- [ ] Link `public/storage` existe e aponta para `storage/app/public`
- [ ] Imagens foram copiadas de `src/assets/` para `storage/app/public/courses/images/`
- [ ] Registros no banco têm `image_url` e `flyer_url` corretos
- [ ] Cursos têm `is_active=1` e `visibility_status='published'`
- [ ] Cache foi limpo após mudanças
- [ ] URL `http://localhost/storage/courses/images/teste.jpg` funciona no navegador

---

## 📌 Resposta às Suas Perguntas

### "Se publicar um curso no admin aparece no site?"

**SIM**, desde que:

1. O curso tenha `is_active = true`
2. O `visibility_status = 'published'` (ou 'scheduled' com data passada)
3. O `published_at` seja NULL ou uma data no passado/presente
4. O curso não esteja deletado (`deleted_at IS NULL`)

O controller `PublicCourseController` usa o método `visibleOnSite()` que aplica esses filtros automaticamente.

**Cache:** Os cursos são cacheados por 30 minutos. Após publicar, espere ou limpe o cache.

---

## 🔧 Script Completo de Configuração

Execute estes comandos em ordem:

```bash
# 1. Criar link de storage
php artisan storage:link

# 2. Criar diretórios
mkdir -p storage/app/public/courses/images
mkdir -p storage/app/public/blog/images

# 3. Migrar imagens (se necessário)
php migrate-images.php

# 4. Limpar cache
php artisan cache:clear
php artisan config:clear

# 5. Testar
php artisan route:list | grep courses
```

---

## 📞 Próximos Passos

1. Execute o `storage:link`
2. Migre as imagens de `src/assets/` para `storage/app/public/`
3. Atualize os registros no banco com os URLs corretos
4. Publique os cursos (via admin ou SQL)
5. Limpe o cache
6. Teste a API pública: `http://localhost/api/public/courses`

Se precisar de ajuda com algum passo específico, me avise!
