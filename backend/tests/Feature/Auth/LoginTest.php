<?php

use App\Models\User;

use function Pest\Laravel\postJson;

it('autentica com credenciais válidas e retorna token', function () {
  User::factory()->create([
    'email' => 'felipe@teste.com',
    'password' => 'password',
  ]);

  $response = postJson('/api/login', [
    'email' => 'felipe@teste.com',
    'password' => 'password',
  ]);

  $response->assertOk()
    ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
});

it('rejeita login com senha incorreta', function () {
  User::factory()->create([
    'email' => 'felipe@teste.com',
    'password' => 'password',
  ]);

  $response = postJson('/api/login', [
    'email' => 'felipe@teste.com',
    'password' => 'senha-errada',
  ]);

  $response->assertStatus(422)->assertJsonValidationErrorFor('email');
});

it('rejeita login com email inexistente', function () {
  $response = postJson('/api/login', [
    'email' => 'naoexiste@teste.com',
    'password' => 'password',
  ]);

  $response->assertStatus(422)->assertJsonValidationErrorFor('email');
});

it('rejeita login sem credenciais', function () {
  $response = postJson('/api/login', []);

  $response->assertStatus(422)
    ->assertJsonValidationErrors(['email', 'password']);
});

it('não vaza qual campo está errado por segurança', function () {
  User::factory()->create(['email' => 'felipe@teste.com', 'password' => 'password']);

  $response = postJson('/api/login', [
    'email' => 'felipe@teste.com',
    'password' => 'errada',
  ]);

  $response->assertStatus(422);
  expect($response->json('errors.email'))->toContain('As credenciais informadas estão incorretas.');
});
