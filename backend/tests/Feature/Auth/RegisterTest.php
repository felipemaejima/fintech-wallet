<?php

use App\Models\User;
use App\Models\Wallet;

use function Pest\Laravel\postJson;

it('registra um usuário com sucesso e retorna token', function () {
  $response = postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'felipe@teste.com',
    'password' => 'senha123',
    'password_confirmation' => 'senha123',
  ]);

  $response->assertCreated()
    ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

  expect(User::where('email', 'felipe@teste.com')->exists())->toBeTrue();
});

it('cria a carteira zerada automaticamente ao registrar', function () {
  postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'felipe@teste.com',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $user = User::where('email', 'felipe@teste.com')->first();

  expect($user->wallet)->not->toBeNull();
  expect($user->wallet->balance)->toEqual('0.00');
});

it('não expõe a senha na resposta', function () {
  $response = postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'felipe@teste.com',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $response->assertJsonMissingPath('user.password');
});

it('armazena a senha com hash, não em texto puro', function () {
  postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'felipe@teste.com',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $user = User::where('email', 'felipe@teste.com')->first();

  expect($user->password)->not->toEqual('password');
});

it('rejeita registro com email já existente', function () {
  User::factory()->create(['email' => 'felipe@teste.com']);

  $response = postJson('/api/register', [
    'name' => 'Outro',
    'email' => 'felipe@teste.com',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $response->assertStatus(422)->assertJsonValidationErrorFor('email');
});

it('rejeita registro sem confirmação de senha correta', function () {
  $response = postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'felipe@teste.com',
    'password' => 'password',
    'password_confirmation' => 'diferente',
  ]);

  $response->assertStatus(422)->assertJsonValidationErrorFor('password');
});

it('rejeita registro com campos obrigatórios ausentes', function () {
  $response = postJson('/api/register', []);

  $response->assertStatus(422)
    ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejeita registro com email inválido', function () {
  $response = postJson('/api/register', [
    'name' => 'Felipe',
    'email' => 'nao-eh-email',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $response->assertStatus(422)->assertJsonValidationErrorFor('email');
});

it('não cria usuário nem carteira quando a validação falha', function () {
  postJson('/api/register', ['email' => 'invalido']);

  expect(User::count())->toBe(0);
  expect(Wallet::count())->toBe(0);
});
