<?php

/**
 * fix_missing_profiles.php
 *
 * Coloca este ficheiro na raiz do projeto Laravel (junto ao artisan).
 * Executa com:  php fix_missing_profiles.php
 *
 * Cria StudentProfile / TeacherProfile / AdminProfile para todos os
 * utilizadores que foram criados sem o perfil correspondente.
 */

// ── Bootstrap do Laravel ────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ── Modelos ──────────────────────────────────────────────────────────────────
use App\Models\AdminProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n=== Fix Missing Profiles ===\n\n";

$fixed   = 0;
$skipped = 0;
$errors  = 0;

$users = User::whereNull('deleted_at')->get();

foreach ($users as $user) {
    try {
        DB::beginTransaction();

        $created = false;

        switch ($user->role) {
            case 'student':
                if (! $user->studentProfile) {
                    $year  = now()->year;
                    $count = StudentProfile::whereYear('created_at', $year)->count() + 1;
                    $code  = 'OLS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                    StudentProfile::create([
                        'user_id'         => $user->id,
                        'student_code'    => $code,
                        'enrollment_date' => now()->toDateString(),
                    ]);
                    $created = true;
                }
                break;

            case 'teacher':
                if (! $user->teacherProfile) {
                    $count = TeacherProfile::count() + 1;
                    $code  = 'TCH-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                    TeacherProfile::create([
                        'user_id'      => $user->id,
                        'teacher_code' => $code,
                        'hire_date'    => now()->toDateString(),
                    ]);
                    $created = true;
                }
                break;

            case 'admin':
                if (! $user->adminProfile) {
                    $count = AdminProfile::count() + 1;
                    $code  = 'ADM-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                    AdminProfile::create([
                        'user_id'    => $user->id,
                        'admin_code' => $code,
                    ]);
                    $created = true;
                }
                break;
        }

        DB::commit();

        if ($created) {
            echo "[FIXED]   {$user->email}  (role: {$user->role})\n";
            $fixed++;
        } else {
            $skipped++;
        }
    } catch (\Throwable $e) {
        DB::rollBack();
        echo "[ERROR]   {$user->email} — " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n--- Resultado ---\n";
echo "Corrigidos : {$fixed}\n";
echo "Já tinham  : {$skipped}\n";
echo "Erros      : {$errors}\n\n";
