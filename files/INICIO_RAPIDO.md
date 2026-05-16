# 🚀 Configuração Rápida - Resolver Problemas de Imagens

## Execute em 5 minutos ⏱️

### PASSO 1: Execute o Script de Configuração

**Opção A - Usando o Script Batch (Recomendado para Windows/XAMPP):**

1. Coloque o arquivo `setup-images.bat` na pasta do projeto
2. Execute como Administrador (clique direito → Executar como administrador)

**Opção B - Manualmente:**

```bash
# Abra o CMD ou PowerShell na pasta do projeto
cd c:\xampp\htdocs\OLS

# 1. Criar link do storage
php artisan storage:link

# 2. Criar diretórios
mkdir storage\app\public\courses\images
mkdir storage\app\public\blog\images

# 3. Dar permissões
icacls "storage\app\public" /grant Everyone:F /T

# 4. Copiar imagens (se existirem em src/assets)
copy src\assets\course-*.jpg storage\app\public\courses\images\
copy src\assets\flyer-*.png storage\app\public\courses\images\

# 5. Limpar cache
php artisan cache:clear
```

---

### PASSO 2: Atualizar URLs no Banco de Dados

**Opção A - Via MySQL Workbench ou phpMyAdmin:**

Execute este SQL:

```sql
-- Atualizar URLs para o novo caminho
UPDATE courses 
SET image_url = CONCAT('http://localhost/storage/courses/images/', 
    SUBSTRING_INDEX(image_url, '/', -1))
WHERE image_url LIKE '%/src/assets/%' 
   OR image_url LIKE '%assets/%';

-- Ou usar imagens padrão por nível
UPDATE courses 
SET image_url = CONCAT('http://localhost/storage/courses/images/course-', 
    LOWER(level), '.jpg')
WHERE image_url IS NULL OR image_url = '';
```

**Opção B - Via Tinker:**

```bash
php artisan tinker

# Atualizar todos os cursos
$courses = \App\Models\Course::whereNull('image_url')->get();
foreach ($courses as $course) {
    $course->image_url = url('storage/courses/images/course-' . $course->level . '.jpg');
    $course->save();
}
```

---

### PASSO 3: Publicar os Cursos

Para que apareçam no site, os cursos precisam estar publicados:

```sql
-- Ver cursos não publicados
SELECT id, title_pt, is_active, visibility_status 
FROM courses 
WHERE visibility_status != 'published';

-- Publicar TODOS os cursos
UPDATE courses 
SET 
    is_active = 1,
    visibility_status = 'published',
    published_at = NOW()
WHERE deleted_at IS NULL;

-- Ou publicar curso específico
UPDATE courses 
SET 
    is_active = 1,
    visibility_status = 'published',
    published_at = NOW()
WHERE id = 'SEU_ID_AQUI';
```

---

### PASSO 4: Limpar Cache

```bash
php artisan cache:forget public_courses_pt
php artisan cache:forget public_courses_en
php artisan cache:clear
```

---

### PASSO 5: Testar

#### A) Testar Imagem Diretamente

Abra no navegador:
```
http://localhost/storage/courses/images/course-business.jpg
```

✅ Deve mostrar a imagem
❌ Se der 404, volte ao PASSO 1

#### B) Testar API Pública

Abra no navegador ou use Postman:
```
http://localhost/api/public/courses
```

✅ Deve retornar JSON com cursos e imagens
❌ Se retornar vazio, volte ao PASSO 3 (publicar cursos)

#### C) Testar no Admin

1. Acesse o admin
2. Vá em Cursos
3. Abra um curso
4. As imagens devem aparecer

---

## ✅ Verificação Rápida

Use este checklist para confirmar que tudo está funcionando:

```bash
# 1. Link simbólico existe?
cd c:\xampp\htdocs\OLS\public
dir | findstr storage
# ✅ Deve mostrar: storage [...]

# 2. Arquivos físicos existem?
dir ..\storage\app\public\courses\images
# ✅ Deve listar arquivos .jpg e .png

# 3. URL funciona?
curl http://localhost/storage/courses/images/course-business.jpg
# ✅ Deve retornar dados da imagem

# 4. API retorna cursos?
curl http://localhost/api/public/courses
# ✅ Deve retornar JSON com array de cursos
```

---

## 📊 Resumo Visual do Fluxo

```
┌─────────────────────────────────────────────────────────────┐
│  ANTES (Não funciona)                                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  src/assets/course-business.jpg  ←  Apenas frontend        │
│       ↓                                                     │
│  image_url: "/src/assets/course-business.jpg"               │
│       ↓                                                     │
│  ❌ Backend não consegue servir essas imagens               │
│                                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  DEPOIS (Funciona)                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  storage/app/public/courses/images/course-business.jpg      │
│       ↓                                                     │
│  public/storage  →  Link simbólico                          │
│       ↓                                                     │
│  image_url: "http://localhost/storage/courses/.../jpg"      │
│       ↓                                                     │
│  ✅ Acessível via HTTP para admin e site público            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Resposta Direta às Suas Perguntas

### "As imagens não aparecem no admin"

**Causa:** O admin está tentando carregar imagens de `src/assets/` que não são servidas pelo backend Laravel.

**Solução:** 
1. Migre as imagens para `storage/app/public/courses/images/`
2. Atualize os URLs no banco de dados
3. Crie o link simbólico com `php artisan storage:link`

### "Se publicar um curso no admin aparece no site?"

**SIM**, aparece automaticamente se:

✅ `is_active = 1`  
✅ `visibility_status = 'published'`  
✅ `published_at` é NULL ou data passada  
✅ `deleted_at IS NULL`  

**Cache:** Aguarde até 30 minutos OU limpe o cache:
```bash
php artisan cache:forget public_courses_pt
php artisan cache:forget public_courses_en
```

---

## 🆘 Se ainda não funcionar

Execute este comando e me envie o resultado:

```bash
cd c:\xampp\htdocs\OLS

echo "=== STORAGE LINK ===" && dir public | findstr storage
echo.
echo "=== ARQUIVOS FISICOS ===" && dir storage\app\public\courses\images
echo.
echo "=== CURSOS NO BANCO ===" && mysql -u root -e "SELECT id, title_pt, image_url, is_active, visibility_status FROM ols.courses LIMIT 3;"
echo.
echo "=== TESTE DE URL ===" && curl http://localhost/storage/courses/images/course-business.jpg
```

Com isso consigo identificar exatamente o que está errado!
