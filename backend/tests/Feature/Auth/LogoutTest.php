<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

it('faz logout de usuário autenticado e revoga o token', function () {
  $user = User::factory()->create();
  Sanctum::actingAs($user);

  $response = postJson('/api/logout');

  $response->assertOk()->assertJson(['message' => 'Logout realizado com sucesso.']);
});

it('bloqueia logout sem autenticação', function () {
  $response = postJson('/api/logout');

  $response->assertStatus(401);
});

it('impede acesso a rota protegida com token inválido', function () {
  $response = postJson('/api/logout', [], [
    'Authorization' => 'Bearer token-invalido-qualquer',
  ]);

  $response->assertStatus(401);
});
