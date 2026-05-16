@echo off
chcp 65001 >nul
echo.
echo ══════════════════════════════════════════════════════
echo    Configuração de Imagens - OLS Laravel Backend
echo ══════════════════════════════════════════════════════
echo.

REM Definir o diretório do projeto (ajuste se necessário)
for %%I in ("%~dp0..") do set "PROJECT_DIR=%%~fI"

set "ASSETS_DIR=%PROJECT_DIR%\src\assets"
if not exist "%ASSETS_DIR%" (
    for %%J in ("%PROJECT_DIR%\..") do set "HTDOCS_DIR=%%~fJ"
    set "ASSETS_DIR=%HTDOCS_DIR%\OLS\src\assets"
)

echo 📁 Diretório do projeto: %PROJECT_DIR%
echo 🖼️  Diretório das imagens (assets): %ASSETS_DIR%
echo.

REM Navegar para o diretório do projeto
cd /d "%PROJECT_DIR%"

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 1: Criar Link Simbólico do Storage
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

php artisan storage:link
if %errorlevel% neq 0 (
    echo ❌ Erro ao criar link do storage
    pause
    exit /b 1
)

echo ✓ Link criado com sucesso!
echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 2: Criar Diretórios Necessários
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

if not exist "storage\app\public\courses" mkdir "storage\app\public\courses"
if not exist "storage\app\public\courses\images" mkdir "storage\app\public\courses\images"
if not exist "storage\app\public\blog" mkdir "storage\app\public\blog"
if not exist "storage\app\public\blog\images" mkdir "storage\app\public\blog\images"

echo ✓ Diretórios criados!
echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 3: Configurar Permissões
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

icacls "storage\app\public" /grant Everyone:F /T /Q
icacls "bootstrap\cache" /grant Everyone:F /T /Q

echo ✓ Permissões configuradas!
echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 4: Migrar Imagens (se existirem)
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

if exist "%ASSETS_DIR%" (
    echo 📋 Copiando imagens de %ASSETS_DIR%...
    
    REM Copiar imagens de cursos
    if exist "%ASSETS_DIR%\course-*.jpg" (
        copy /Y "%ASSETS_DIR%\course-*.jpg" "storage\app\public\courses\images\" >nul 2>&1
        echo ✓ Imagens de cursos copiadas
    )
    
    if exist "%ASSETS_DIR%\course-*.png" (
        copy /Y "%ASSETS_DIR%\course-*.png" "storage\app\public\courses\images\" >nul 2>&1
        echo ✓ Imagens PNG de cursos copiadas
    )
    
    REM Copiar flyers
    if exist "%ASSETS_DIR%\flyer-*.png" (
        copy /Y "%ASSETS_DIR%\flyer-*.png" "storage\app\public\courses\images\" >nul 2>&1
        echo ✓ Flyers copiados
    )
    
    if exist "%ASSETS_DIR%\flyer-*.jpg" (
        copy /Y "%ASSETS_DIR%\flyer-*.jpg" "storage\app\public\courses\images\" >nul 2>&1
        echo ✓ Flyers JPG copiados
    )
    
    REM Copiar outras imagens
    if exist "%ASSETS_DIR%\blog-*.jpg" (
        copy /Y "%ASSETS_DIR%\blog-*.jpg" "storage\app\public\blog\images\" >nul 2>&1
        echo ✓ Imagens de blog copiadas
    )
    
    echo.
    echo ✓ Migração de imagens concluída!
) else (
    echo ⚠ Diretório de assets não encontrado
    echo   Verifique se existe: %ASSETS_DIR%
)

echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 5: Limpar Cache
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo ✓ Cache limpo!
echo.

echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo PASSO 6: Verificação
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

echo 📋 Verificando arquivos...
dir /b "storage\app\public\courses\images" 2>nul | find /c /v "" > temp.txt
set /p count=<temp.txt
del temp.txt

echo ✓ Total de imagens encontradas: %count%
echo.

echo ══════════════════════════════════════════════════════
echo ✅ CONFIGURAÇÃO CONCLUÍDA!
echo ══════════════════════════════════════════════════════
echo.
echo 📋 Próximos passos:
echo.
echo    1. Atualize os URLs no banco de dados com:
echo       mysql -u root olsangola_dev ^< files\update-image-urls.sql
echo.
echo    2. Teste uma imagem no navegador:
echo       http://localhost/storage/courses/images/course-business.jpg
echo.
echo    3. Teste a API pública:
echo       http://localhost/api/public/courses
echo.
echo ══════════════════════════════════════════════════════
echo.

pause
