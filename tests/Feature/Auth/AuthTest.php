<?php

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ─── Login ───────────────────────────────────────────────────────────────────

test('utilizador pode fazer login com credenciais válidas', function () {
    $user = User::factory()->create([
        'email'    => 'test@olsangola.ao',
        'password' => Hash::make('password123'),
        'status'   => 'active',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'test@olsangola.ao',
        'password' => 'password123',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
});

test('login falha com password incorrecta', function () {
    User::factory()->create([
        'email'    => 'test@olsangola.ao',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/auth/login', [
        'email'    => 'test@olsangola.ao',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

test('login falha se conta está suspensa', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'status'   => 'suspended',
    ]);

    $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'password123',
    ])->assertForbidden();
});

// ─── Registo ─────────────────────────────────────────────────────────────────

test('estudante pode criar conta', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name'            => 'Ana',
        'last_name'             => 'Santos',
        'email'                 => 'ana@olsangola.ao',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
             ->assertJsonStructure(['token', 'user', 'message']);

    $this->assertDatabaseHas('users', ['email' => 'ana@olsangola.ao', 'role' => 'student']);
    $this->assertDatabaseHas('student_profiles', []);
});

test('registo falha com email duplicado', function () {
    User::factory()->create(['email' => 'ana@olsangola.ao']);

    $this->postJson('/api/auth/register', [
        'first_name'            => 'Ana',
        'last_name'             => 'Santos',
        'email'                 => 'ana@olsangola.ao',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable();
});

// ─── Me & Logout ─────────────────────────────────────────────────────────────

test('utilizador autenticado pode obter o seu perfil', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->getJson('/api/auth/me')
         ->assertOk()
         ->assertJsonFragment(['email' => $user->email]);
});

test('utilizador pode fazer logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->postJson('/api/auth/logout')
         ->assertOk()
         ->assertJsonFragment(['message' => 'Sessão encerrada com sucesso.']);
});

test('rota protegida rejeita pedidos sem token', function () {
    $this->getJson('/api/auth/me')->assertUnauthorized();
});

// ─── Conta suspensa após autenticação ────────────────────────────────────────

test('middleware bloqueia utilizador com conta inactiva', function () {
    $user = User::factory()->create(['status' => 'inactive']);

    $this->actingAs($user)
         ->putJson('/api/profile', ['first_name' => 'Novo'])
         ->assertForbidden();
});

// ─── Profile ─────────────────────────────────────────────────────────────────

test('utilizador pode actualizar o seu perfil', function () {
    $user = User::factory()->create(['status' => 'active']);

    $this->actingAs($user)
         ->putJson('/api/profile', ['first_name' => 'Maria'])
         ->assertOk()
         ->assertJsonFragment(['first_name' => 'Maria']);
});

test('utilizador pode alterar a sua password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
        'status'   => 'active',
    ]);

    $this->actingAs($user)
         ->putJson('/api/profile/password', [
             'current_password'      => 'oldpassword',
             'password'              => 'newpassword123',
             'password_confirmation' => 'newpassword123',
         ])
         ->assertOk();

    $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
});
