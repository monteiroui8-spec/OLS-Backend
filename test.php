<?php
$token = json_decode(file_get_contents('http://localhost:8000/api/auth/login', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['email' => 'admin@olsangola.ao', 'password' => 'admin12345'])
    ]
])))->token;

$teachers = json_decode(file_get_contents('http://localhost:8000/api/admin/users?filter[role]=teacher&limit=100', false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\nAccept: application/json",
    ]
])));
$teacher_profile_id = $teachers->data[0]->teacher_profile->id;

$response = file_get_contents('http://localhost:8000/api/admin/exams', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer $token\r\nAccept: application/json",
        'content' => json_encode([
            'title' => 'Test Exam',
            'teacher_id' => $teacher_profile_id,
            'duration' => 60,
            'questions' => [
                [
                    'text' => 'Question 1',
                    'type' => 'true_false',
                    'options' => ['True', 'False'],
                    'correct' => 0
                ]
            ]
        ]),
        'ignore_errors' => true
    ]
]));

echo $response;
