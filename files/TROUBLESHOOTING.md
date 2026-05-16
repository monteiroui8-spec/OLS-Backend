# 🔧 Guia Rápido de Troubleshooting - Imagens no OLS

## ❌ Problema 1: Imagens não aparecem no Admin

### Sintomas
- Upload funciona mas imagens não são exibidas
- Campos de imagem aparecem vazios
- Console do navegador mostra erro 404

### Diagnóstico Rápido

```bash
# 1. Verificar se o link simbólico existe
cd c:\xampp\htdocs\OLS\public
dir | findstr storage

# Deve mostrar: storage [c:\xampp\htdocs\OLS\storage\app\public]
```

### Soluções

**A) Recriar Link Simbólico**
```bash
cd c:\xampp\htdocs\OLS
php artisan storage:link
```

**B) No Windows, se der erro de permissão, execute como Administrador:**
```powershell
# PowerShell como Administrador
cd c:\xampp\htdocs\OLS
New-Item -ItemType SymbolicLink -Path "public\storage" -Target "storage\app\public"
```

**C) Verificar Permissões**
```bash
icacls "c:\xampp\htdocs\OLS\storage\app\public" /grant Everyone:F /T
```

---

## ❌ Problema 2: Upload retorna erro 500

### Sintomas
- Erro ao fazer upload de imagens
- Logs mostram "permission denied"

### Diagnóstico

```bash
# Verificar logs
tail -f c:\xampp\htdocs\OLS\storage\logs\laravel.log
```

### Soluções

**A) Permissões incorretas**
```bash
icacls "c:\xampp\htdocs\OLS\storage" /grant Everyone:F /T
icacls "c:\xampp\htdocs\OLS\bootstrap\cache" /grant Everyone:F /T
```

**B) Diretório não existe**
```bash
mkdir c:\xampp\htdocs\OLS\storage\app\public\courses\images
mkdir c:\xampp\htdocs\OLS\storage\app\public\blog\images
```

**C) Configuração do .env**
```env
# Verificar estas linhas no .env
FILESYSTEM_DISK=public
APP_URL=http://localhost
```

---

## ❌ Problema 3: Cursos publicados não aparecem no site

### Sintomas
- Curso está ativo no admin
- Não aparece na API pública
- Status "published" mas invisível

### Diagnóstico

```sql
-- Ver cursos e filtros
SELECT 
    id,
    title_pt,
    is_active,
    visibility_status,
    published_at,
    deleted_at
FROM courses;
```

### Checklist para Aparecer no Site

✅ Deve ter TODOS estes valores:

```sql
is_active = 1
visibility_status IN ('published', 'scheduled')
published_at IS NULL OU published_at <= NOW()
deleted_at IS NULL
```

### Soluções

**A) Publicar via SQL**
```sql
UPDATE courses 
SET 
    is_active = 1,
    visibility_status = 'published',
    published_at = NOW()
WHERE id = 'SEU_ID_AQUI';
```

**B) Limpar Cache**
```bash
php artisan cache:forget public_courses_pt
php artisan cache:forget public_courses_en
# ou
php artisan cache:clear
```

**C) Verificar via API**
```bash
# Testar endpoint público
curl http://localhost/api/public/courses
```

---

## ❌ Problema 4: Imagens retornam 404

### Sintomas
- URL da imagem existe no banco
- Navegador retorna 404 ao acessar
- Link simbólico existe

### Diagnóstico

```bash
# 1. Verificar se arquivo físico existe
dir c:\xampp\htdocs\OLS\storage\app\public\courses\images\course-business.jpg

# 2. Testar acesso direto
# Abrir no navegador: http://localhost/storage/courses/images/course-business.jpg
```

### Soluções

**A) Arquivo não existe**
```bash
# Copiar do assets
copy c:\xampp\htdocs\OLS\src\assets\course-*.jpg c:\xampp\htdocs\OLS\storage\app\public\courses\images\
```

**B) URL incorreta no banco**
```sql
-- Verificar URLs
SELECT id, title_pt, image_url FROM courses LIMIT 5;

-- URLs devem ser:
-- http://localhost/storage/courses/images/nome.jpg
-- NÃO: /src/assets/nome.jpg
-- NÃO: ../assets/nome.jpg
```

**C) Atualizar URLs**
```sql
UPDATE courses 
SET image_url = REPLACE(image_url, '/src/assets/', '/storage/courses/images/')
WHERE image_url LIKE '%/src/assets/%';
```

---

## ❌ Problema 5: Cache não atualiza

### Sintomas
- Mudanças no admin não refletem no site
- API retorna dados antigos
- Cursos publicados não aparecem

### Soluções

```bash
# 1. Limpar cache do Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Limpar caches específicos
php artisan cache:forget public_courses_pt
php artisan cache:forget public_courses_en
php artisan cache:forget public_stats
php artisan cache:forget exchange_rates

# 3. Reiniciar servidor (se usando artisan serve)
# Ctrl+C e depois:
php artisan serve
```

---

## ❌ Problema 6: Frontend não mostra imagens do backend

### Sintomas
- Admin mostra imagens corretamente
- Site público não carrega imagens
- CORS errors no console

### Diagnóstico

```javascript
// Verificar console do navegador (F12)
// Procurar por erros de CORS ou 404
```

### Soluções

**A) Configurar CORS (config/cors.php)**
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
    'allowed_origins' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
];
```

**B) Verificar URL da API no Frontend**
```javascript
// No arquivo .env do frontend
VITE_API_URL=http://localhost/api
VITE_STORAGE_URL=http://localhost/storage
```

**C) Verificar se o frontend está usando os URLs corretos**
```javascript
// Exemplo correto:
const imageUrl = `${import.meta.env.VITE_STORAGE_URL}/courses/images/${course.imageUrl}`;

// OU
const imageUrl = course.imageUrl; // se a API já retorna URL completo
```

---

## 🧪 Testes de Verificação Completos

### 1. Verificar Storage Link
```bash
cd c:\xampp\htdocs\OLS\public
dir storage
# Deve mostrar que é um link para storage\app\public
```

### 2. Verificar Arquivos Físicos
```bash
dir c:\xampp\htdocs\OLS\storage\app\public\courses\images
# Deve listar os arquivos de imagem
```

### 3. Testar URL no Navegador
```
http://localhost/storage/courses/images/course-business.jpg
# Deve mostrar a imagem
```

### 4. Testar API Pública
```bash
curl http://localhost/api/public/courses
# Deve retornar JSON com cursos publicados
```

### 5. Verificar Banco de Dados
```sql
-- Cursos visíveis no site
SELECT COUNT(*) FROM courses
WHERE is_active = 1
  AND visibility_status IN ('published', 'scheduled')
  AND (published_at IS NULL OR published_at <= NOW())
  AND deleted_at IS NULL;

-- Deve retornar número > 0
```

### 6. Verificar Logs
```bash
# Ver últimas linhas do log
tail -n 50 c:\xampp\htdocs\OLS\storage\logs\laravel.log
# Não deve ter erros recentes
```

---

## 📞 Comandos Úteis de Debug

### Laravel Tinker
```bash
php artisan tinker

# Listar cursos visíveis
>>> \App\Models\Course::active()->visibleOnSite()->count();

# Ver primeiro curso com detalhes
>>> \App\Models\Course::active()->visibleOnSite()->first();

# Limpar cache específico
>>> \Illuminate\Support\Facades\Cache::forget('public_courses_pt');

# Verificar URL de storage
>>> \Illuminate\Support\Facades\Storage::disk('public')->url('courses/images/teste.jpg');
```

### MySQL Debug
```sql
-- Ver todos os cursos com imagens
SELECT id, title_pt, image_url, is_active, visibility_status
FROM courses
WHERE image_url IS NOT NULL;

-- Contar cursos por status
SELECT 
    visibility_status,
    is_active,
    COUNT(*) as total
FROM courses
GROUP BY visibility_status, is_active;
```

---

## 🔍 Checklist Final de Troubleshooting

Execute na ordem:

- [ ] `php artisan storage:link` executado sem erros
- [ ] Link `public/storage` existe e aponta corretamente
- [ ] Pasta `storage/app/public` tem permissões de escrita
- [ ] Arquivos físicos existem em `storage/app/public/courses/images/`
- [ ] URL `http://localhost/storage/courses/images/teste.jpg` funciona
- [ ] .env tem `APP_URL` e `FILESYSTEM_DISK` corretos
- [ ] Banco de dados tem `image_url` preenchidos
- [ ] Cursos têm `is_active=1` e `visibility_status='published'`
- [ ] `published_at` é NULL ou data passada
- [ ] Cache foi limpo: `php artisan cache:clear`
- [ ] API pública retorna cursos: `curl localhost/api/public/courses`

---

## 🆘 Ainda não funciona?

### Coleta de Informações para Debug

Execute e envie os resultados:

```bash
# 1. Informações do sistema
php artisan about

# 2. Estado do storage link
cd public && dir storage

# 3. Arquivos no storage
dir ..\storage\app\public\courses\images

# 4. Teste de URL
curl http://localhost/storage/courses/images/course-business.jpg

# 5. Cursos no banco
mysql -u root -e "SELECT id, title_pt, image_url, is_active, visibility_status FROM ols.courses LIMIT 5;"

# 6. Últimos erros do log
tail -n 20 ..\storage\logs\laravel.log
```

Com essas informações, é possível identificar exatamente onde está o problema!
