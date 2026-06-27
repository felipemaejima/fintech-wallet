<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

beforeEach(function () {
  $this->user = User::factory()->create();
  $this->wallet = Wallet::create(['user_id' => $this->user->id, 'balance' => 100]);
  Sanctum::actingAs($this->user);
});

it('realiza um saque com sucesso', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '30.00']);

  $response->assertCreated()
    ->assertJsonPath('balance', '70.00');

  expect($this->wallet->fresh()->balance)->toEqual('70.00');
});

it('registra a transação de débito com balance_after correto', function () {
  postJson('/api/wallet/withdraw', ['amount' => '30.00']);

  $transaction = Transaction::where('wallet_id', $this->wallet->id)
    ->where('type', 'debit')->first();

  expect($transaction->type)->toBe('debit');
  expect($transaction->amount)->toEqual('30.00');
  expect($transaction->balance_after)->toEqual('70.00');
});

it('permite sacar exatamente o saldo total', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '100.00']);

  $response->assertCreated();
  expect($this->wallet->fresh()->balance)->toEqual('0.00');
});

it('rejeita saque com saldo insuficiente', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '150.00']);

  $response->assertStatus(422)
    ->assertJson(['message' => 'Saldo insuficiente para realizar o saque.']);

  // o saldo NÃO pode ter mudado
  expect($this->wallet->fresh()->balance)->toEqual('100.00');
});

it('não cria transação quando o saque falha por saldo insuficiente', function () {
  postJson('/api/wallet/withdraw', ['amount' => '150.00']);

  expect(Transaction::where('wallet_id', $this->wallet->id)->count())->toBe(0);
});

it('rejeita saque de valor zero', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '0']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita saque de valor negativo', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '-10.00']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});

it('rejeita saque abaixo do mínimo de 0,01', function () {
  $response = postJson('/api/wallet/withdraw', ['amount' => '0.005']);

  $response->assertStatus(422)->assertJsonValidationErrorFor('amount');
});
