<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug', function () {
    return \App\Models\EnrollmentRequest::with('course')->get()->map(fn($r) => [
        'id' => $r->id,
        'course_id' => $r->course_id,
        'class_group_id' => $r->class_group_id,
        'course_exists' => $r->course !== null
    ]);
});
