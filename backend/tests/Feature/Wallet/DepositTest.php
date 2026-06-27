<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

beforeEach(function () {
  $this->user = User::factory()->create();
  $this->wallet = Wallet::create(['user_id' => $this->user->id, 'balance' => 0]);
  Sanctum::actingAs($this->user);
});

it('realiza um depósito com sucesso', function () {
  $response = postJson('/api/wallet/deposit', ['amount' => '100.00']);

  $response->assertCreated()
    ->assertJsonPath('balance', '100.00');

  expect($this->wallet->fresh()->balance)->toEqual('100.00');
});

it('registra a transação de crédito com balance_after correto', function () {
  postJson('/api/wallet/deposit', ['amount' => '100.00']);

  $transaction = Transaction::where('wallet_id', $this->wallet->id)->first();

  expect($transaction->type)->toBe('credit');
  expect($transaction->amount)->toEqual('100.00');
  expect($transaction->balance_after)->toEqual('100.00');
  expect($transaction->status)->toBe('completed');
});

it('acumula depósitos sucessivos corretamente', function () {
  postJson('/api/wallet/deposit', ['amount' => '100.00']);
  postJson('/api/wallet/deposit', ['amount' => '50.50']);

  expect($this->wallet->fresh()->balance)->toEqual('150.50');
});

it('rejeita depósito de valor zero', function () {
  $response = postJson('/api/wallet/deposit', ['amount' => '0']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
  expect($this->wallet->fresh()->balance)->toEqual('0.00');
});

it('rejeita depósito de valor negativo', function () {
  $response = postJson('/api/wallet/deposit', ['amount' => '-10.00']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita depósito abaixo do mínimo de 0,01', function () {
  $response = postJson('/api/wallet/deposit', ['amount' => '0.001']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita depósito com mais de 2 casas decimais', function () {
  $response = postJson('/api/wallet/deposit', ['amount' => '10.999']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita depósito sem o campo amount', function () {
  $response = postJson('/api/wallet/deposit', []);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita depósito de usuário não autenticado', function () {
  app('auth')->forgetGuards();

  $response = postJson('/api/wallet/deposit', ['amount' => '100.00'], [
    'Authorization' => '',
  ]);

  $response->assertStatus(401);
})
  // ->skip('Ajustar conforme estratégia de teste de guard; ver nota abaixo');
;
