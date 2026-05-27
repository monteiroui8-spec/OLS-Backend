<?php

use App\Http\Controllers\Api\Admin\AdminClassController;
use App\Http\Controllers\Api\Admin\AdminCourseController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminEnrollmentRequestController;
use App\Http\Controllers\Api\Admin\AdminExamController;
use App\Http\Controllers\Api\Admin\AdminGradeController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminTestimonialController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminBlogController;
use App\Http\Controllers\Api\Admin\AdminNewsletterController;
use App\Http\Controllers\Api\Public\PublicBlogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Public\ContactController;
use App\Http\Controllers\Api\Public\CourseEnrollmentController;
use App\Http\Controllers\Api\Public\EnrollmentInterestController;
use App\Http\Controllers\Api\Public\NewsletterController;
use App\Http\Controllers\Api\PublicCourseController;
use App\Http\Controllers\Api\Student\StudentCourseController;
use App\Http\Controllers\Api\Student\StudentDashboardController;
use App\Http\Controllers\Api\Student\StudentExamController;
use App\Http\Controllers\Api\Student\StudentGradeController;
use App\Http\Controllers\Api\Student\StudentPaymentController;
use App\Http\Controllers\Api\Student\StudentScheduleController;
use App\Http\Controllers\Api\Student\StudentAssignmentController;
use App\Http\Controllers\Api\Student\StudentMaterialController;
use App\Http\Controllers\Api\Student\StudentReportController;
use App\Http\Controllers\Api\Student\StudentTestimonialController;
use App\Http\Controllers\Api\Admin\AdminAttendanceController;
use App\Http\Controllers\Api\Student\StudentAttendanceController;
use App\Http\Controllers\Api\Teacher\TeacherAttendanceController;
use App\Http\Controllers\Api\Teacher\TeacherClassController;
use App\Http\Controllers\Api\Teacher\TeacherExamController;
use App\Http\Controllers\Api\Teacher\TeacherGradeController;
use App\Http\Controllers\Api\Teacher\TeacherMaterialController;
use App\Http\Controllers\Api\Teacher\TeacherAssignmentController;
use App\Http\Controllers\Api\Teacher\TeacherReportController;
use App\Http\Controllers\Api\Teacher\TeacherScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Olsangola Corporation
|--------------------------------------------------------------------------
|
| Prefixo base: /api  (definido em bootstrap/app.php)
|
| Grupos:
|   /auth/*         — público (login, registo, reset password)
|   /auth/email/*   — verificação de email (requer auth)
|   /profile        — perfil do utilizador autenticado
|   /admin/*        — apenas role=admin    (Etapa 3)
|   /teacher/*      — apenas role=teacher  (Etapa 4)
|   /student/*      — apenas role=student  (Etapa 5)
|   /public/*       — sem autenticação (Etapa 6)
|
*/

// ─── Auth (público) ─────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->middleware('throttle:auth')->group(function () {

    Route::post('login',           [AuthController::class, 'login'])->name('login');
    Route::post('register',        [AuthController::class, 'register'])->name('register');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
    Route::post('reset-password',  [ForgotPasswordController::class, 'reset'])->name('password.reset');

    // Requer autenticação
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('me',      [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh',[AuthController::class, 'refresh'])->name('refresh');

        // Verificação de email
        Route::prefix('email')->name('email.')->group(function () {
            Route::post('verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
                ->middleware('signed')
                ->name('verify');
            Route::post('resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('resend');
        });
    });
});

// ─── Perfil (autenticado — qualquer role) ───────────────────────────────────
Route::middleware(['auth:sanctum', 'check.account.status'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::put('/',         [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/avatar',  [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
        Route::delete('/avatar',[ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });

// ─── Público (sem autenticação) ─────────────────────────────────────────────
Route::prefix('public')->middleware('throttle:public')
    ->group(function () {
        Route::get('courses',                    [PublicCourseController::class, 'index']);
        Route::get('courses/{course}/classes',   [PublicCourseController::class, 'classes']);
        Route::get('stats',                      [PublicCourseController::class, 'stats']);
        Route::get('exchange-rates',             [PublicCourseController::class, 'rates']);
        Route::get('testimonials',               [PublicCourseController::class, 'testimonials']);
        Route::post('contact',               [ContactController::class, 'store']);
        Route::post('enrollment-request',    [EnrollmentInterestController::class, 'store']);
        Route::post('newsletter',                [NewsletterController::class, 'subscribe']);
        Route::get('newsletter/unsubscribe',     [NewsletterController::class, 'unsubscribe']);

        // Blog
        Route::get('blog/posts',          [PublicBlogController::class, 'index']);
        Route::get('blog/posts/{slug}',   [PublicBlogController::class, 'show']);
        Route::get('blog/categories',     [PublicBlogController::class, 'categories']);

        // Inscrições
        Route::post('course-enrollments', [CourseEnrollmentController::class, 'store']);
    });

// ─── Student ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.account.status', 'throttle:api'])
    ->prefix('student')
    ->group(function () {
        Route::get('dashboard',                      [StudentDashboardController::class, 'index']);
        Route::get('courses',                        [StudentCourseController::class, 'index']);
        Route::get('schedule',                       [StudentScheduleController::class, 'index']);
        Route::get('materials',                      [StudentMaterialController::class, 'index']);
        Route::get('materials/{material}/view',      [StudentMaterialController::class, 'view']);
        Route::get('materials/{material}/download',  [StudentMaterialController::class, 'download']);
        Route::get('exams',                          [StudentExamController::class, 'index']);
        Route::get('exams/{exam}/ranking',           [StudentExamController::class, 'ranking']);
        Route::post('exams/{exam}/start',            [StudentExamController::class, 'start']);
        Route::post('exams/attempts/{attempt}/submit', [StudentExamController::class, 'submit']);
        Route::get('exams/attempts/{attempt}',       [StudentExamController::class, 'result']);
        Route::get('grades',                         [StudentGradeController::class, 'index']);
        Route::get('report',                         [StudentReportController::class, 'index']);
        Route::get('assignments',                    [StudentAssignmentController::class, 'index']);
        Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit']);
        Route::get('payments',                       [StudentPaymentController::class, 'index']);
        Route::get('payments/{payment}/receipt',     [StudentPaymentController::class, 'receipt']);
        Route::post('payments/{payment}/proof',      [StudentPaymentController::class, 'uploadProof']);
        // Testemunhos
        Route::get('testimonials',                   [StudentTestimonialController::class, 'index']);
        Route::post('testimonials',                  [StudentTestimonialController::class, 'store']);
        // Presenças
        Route::get('attendance',                     [StudentAttendanceController::class, 'index']);
    });

// ─── Teacher ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.account.status', 'role:teacher|admin', 'throttle:api'])
    ->prefix('teacher')
    ->group(function () {
        Route::get('classes',                              [TeacherClassController::class, 'index']);
        Route::get('classes/{classGroup}',                 [TeacherClassController::class, 'show']);
        Route::get('students',                             [TeacherClassController::class, 'students']);
        Route::get('assignments',                          [TeacherAssignmentController::class, 'index']);
        Route::post('assignments',                         [TeacherAssignmentController::class, 'store']);
        Route::post('assignments/{submission}/grade',      [TeacherAssignmentController::class, 'grade']);
        Route::get('schedule',                             [TeacherScheduleController::class, 'index']);
        Route::get('materials',                            [TeacherMaterialController::class, 'index']);
        Route::post('materials/upload',                    [TeacherMaterialController::class, 'upload']);
        Route::patch('materials/{material}',               [TeacherMaterialController::class, 'update']);
        Route::delete('materials/{material}',              [TeacherMaterialController::class, 'destroy']);
        Route::get('materials/{material}/download',        [TeacherMaterialController::class, 'download']);
        Route::get('exams',                                [TeacherExamController::class, 'index']);
        Route::post('exams',                               [TeacherExamController::class, 'store']);
        Route::put('exams/{exam}',                         [TeacherExamController::class, 'update']);
        Route::post('exams/{exam}/publish',                [TeacherExamController::class, 'publish']);
        Route::get('exams/{exam}/stats',                   [TeacherExamController::class, 'stats']);
        Route::get('grades',                               [TeacherGradeController::class, 'index']);
        Route::post('grades',                              [TeacherGradeController::class, 'store']);
        Route::get('report',                               [TeacherReportController::class, 'index']);
        // ── Presenças ────────────────────────────────────────────────────────
        Route::get('attendance/dates',                     [TeacherAttendanceController::class, 'dates']);
        Route::get('attendance',                           [TeacherAttendanceController::class, 'index']);
        Route::post('attendance',                          [TeacherAttendanceController::class, 'store']);
    });

// ─── Admin ───────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.account.status', 'role:admin', 'throttle:api'])
    ->prefix('admin')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index']);

        // ── Utilizadores ────────────────────────────────────────────────────
        // FIX: rotas batch DEVEM vir ANTES de `users/{user}` para não serem
        // capturadas pelo wildcard {user} quando o segmento é "batch-activate" etc.
        Route::post('users/batch-activate',        [AdminUserController::class, 'batchActivate']);
        Route::post('users/batch-deactivate',      [AdminUserController::class, 'batchDeactivate']);
        Route::post('users/batch-delete',          [AdminUserController::class, 'batchDelete']);
        Route::get('users',                        [AdminUserController::class, 'index']);
        Route::post('users',                       [AdminUserController::class, 'store']);
        Route::get('users/{user}',                 [AdminUserController::class, 'show']);
        Route::put('users/{user}',                 [AdminUserController::class, 'update']);
        Route::delete('users/{user}',              [AdminUserController::class, 'destroy']);
        Route::post('users/{user}/restore',        [AdminUserController::class, 'restore']);
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);

        // ── Turmas ──────────────────────────────────────────────────────────
        Route::get('classes',                                  [AdminClassController::class, 'index']);
        Route::post('classes',                                 [AdminClassController::class, 'store']);
        Route::put('classes/{class}',                          [AdminClassController::class, 'update']);
        Route::post('classes/{class}/students',                [AdminClassController::class, 'addStudent']);
        Route::post('classes/{class}/schedules',               [AdminClassController::class, 'addSchedule']);
        Route::delete('classes/{class}/schedules/{schedule}',  [AdminClassController::class, 'removeSchedule']);

        // ── Pagamentos ──────────────────────────────────────────────────────
        Route::get('payments/export',      [AdminPaymentController::class, 'export']);
        Route::get('payments',             [AdminPaymentController::class, 'index']);
        Route::post('payments',            [AdminPaymentController::class, 'store']);
        Route::get('payments/{payment}',   [AdminPaymentController::class, 'show']);
        Route::put('payments/{payment}',   [AdminPaymentController::class, 'update']);

        // ── Cursos ──────────────────────────────────────────────────────────
        Route::post('courses/upload-image',      [AdminCourseController::class, 'uploadImage']);
        Route::get('courses',                    [AdminCourseController::class, 'index']);
        Route::post('courses',                   [AdminCourseController::class, 'store']);
        Route::get('courses/{course}',           [AdminCourseController::class, 'show']);
        Route::put('courses/{course}',           [AdminCourseController::class, 'update']);
        Route::delete('courses/{course}',        [AdminCourseController::class, 'destroy']);
        Route::post('courses/{course}/restore',  [AdminCourseController::class, 'restore']);

        // ── Presenças ────────────────────────────────────────────────────────
        Route::get('attendance/summary',                                       [AdminAttendanceController::class, 'summary']);
        Route::get('attendance',                                               [AdminAttendanceController::class, 'index']);

        // ── Pedidos de inscrição ─────────────────────────────────────────────
        Route::get('enrollment-requests',                                      [AdminEnrollmentRequestController::class, 'index']);
        Route::post('enrollment-requests/{enrollmentRequest}/approve',         [AdminEnrollmentRequestController::class, 'approve']);
        Route::post('enrollment-requests/{enrollmentRequest}/reject',          [AdminEnrollmentRequestController::class, 'reject']);

        // ── Definições ───────────────────────────────────────────────────────
        Route::get('settings',  [AdminSettingsController::class, 'index']);
        Route::put('settings',  [AdminSettingsController::class, 'update']);

        // ── Testemunhos ──────────────────────────────────────────────────────
        Route::apiResource('testimonials', AdminTestimonialController::class);
        Route::post('testimonials/{testimonial}/approve', [AdminTestimonialController::class, 'approve']);
        Route::post('testimonials/{testimonial}/reject',  [AdminTestimonialController::class, 'reject']);

        // ── Relatórios ───────────────────────────────────────────────────────
        Route::get('reports',                    [AdminReportController::class, 'index']);
        Route::get('reports/students',           [AdminReportController::class, 'students']);
        Route::get('reports/enrolled-students',  [AdminReportController::class, 'enrolledStudents']);
        Route::get('reports/payments',           [AdminReportController::class, 'payments']);
        Route::get('reports/attendance',         [AdminReportController::class, 'attendance']);
        Route::get('reports/export/excel',       [AdminReportController::class, 'exportExcel']);
        Route::get('reports/export/word',        [AdminReportController::class, 'exportWord']);

        // ── Blog ─────────────────────────────────────────────────────────────
        Route::post('blog/upload-image',            [AdminBlogController::class, 'uploadImage']);
        Route::get('blog/posts',                    [AdminBlogController::class, 'index']);
        Route::post('blog/posts',                   [AdminBlogController::class, 'store']);
        Route::get('blog/posts/{post}',             [AdminBlogController::class, 'show']);
        Route::put('blog/posts/{post}',             [AdminBlogController::class, 'update']);
        Route::delete('blog/posts/{post}',          [AdminBlogController::class, 'destroy']);
        Route::get('blog/categories',               [AdminBlogController::class, 'indexCategories']);
        Route::post('blog/categories',              [AdminBlogController::class, 'storeCategory']);
        Route::put('blog/categories/{category}',    [AdminBlogController::class, 'updateCategory']);
        Route::delete('blog/categories/{category}', [AdminBlogController::class, 'destroyCategory']);

        // ── Newsletter ────────────────────────────────────────────────────────
        Route::get('newsletter',                 [AdminNewsletterController::class, 'index']);
        Route::post('newsletter/send',           [AdminNewsletterController::class, 'send']);
        Route::delete('newsletter/{subscriber}', [AdminNewsletterController::class, 'destroy']);

        // ── Exames ────────────────────────────────────────────────────────────
        Route::get('exams',                 [AdminExamController::class, 'index']);
        Route::post('exams',                [AdminExamController::class, 'store']);
        Route::put('exams/{exam}',          [AdminExamController::class, 'update']);
        Route::post('exams/{exam}/publish', [AdminExamController::class, 'publish']);
        Route::get('exams/{exam}/stats',    [AdminExamController::class, 'stats']);
        Route::delete('exams/{exam}',       [AdminExamController::class, 'destroy']);

        // ── Notas ─────────────────────────────────────────────────────────────
        Route::get('grades',         [AdminGradeController::class, 'index']);
        Route::put('grades/{grade}', [AdminGradeController::class, 'update']);
    });

// ─── Autenticado (qualquer role) ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.account.status', 'throttle:api'])
    ->group(function () {
        Route::get('documents',                      [DocumentController::class, 'index']);
        Route::post('documents/upload',              [DocumentController::class, 'upload']);
        Route::get('documents/{document}/download',  [DocumentController::class, 'download']);
        Route::delete('documents/{document}',        [DocumentController::class, 'destroy']);
        Route::get('notifications',                  [NotificationController::class, 'index']);
        Route::patch('notifications/{id}/read',      [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all',        [NotificationController::class, 'readAll']);

        // ── Chat ───────────────────────────────────────────────────────────
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('conversations',               [ChatController::class, 'conversations'])->name('conversations.index');
            Route::post('conversations',              [ChatController::class, 'createConversation'])->name('conversations.store');
            Route::get('conversations/{id}/messages', [ChatController::class, 'messages'])->name('messages.index');
            Route::post('conversations/{id}/read',    [ChatController::class, 'markRead'])->name('messages.read');
            Route::post('messages',                   [ChatController::class, 'sendMessage'])->name('messages.store');
            Route::post('attachments',                [ChatController::class, 'uploadAttachment'])->name('attachments.store');
        });

        // FIX: rota de pesquisa de utilizadores movida para /chat/users para
        // evitar conflito com GET /admin/users (que era sobrescrita aqui).
        Route::get('chat/users', [ChatController::class, 'searchUsers'])->name('users.search');
    });