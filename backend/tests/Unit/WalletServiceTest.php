<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Modules\Wallet\Exceptions\InsufficientBalanceException;
use App\Modules\Wallet\Services\WalletService;

// use function Pest\Laravel\mock;

beforeEach(function () {
  $this->service = new WalletService();
  $this->user = User::factory()->create();
  $this->wallet = Wallet::create(['user_id' => $this->user->id, 'balance' => 100]);
});

it('deposita e retorna a transação', function () {
  $transaction = $this->service->deposit($this->wallet, '50.00');

  expect($transaction)->toBeInstanceOf(Transaction::class);
  expect($this->wallet->fresh()->balance)->toEqual('150.00');
});

it('saca e retorna a transação', function () {
  $transaction = $this->service->withdraw($this->wallet, '40.00');

  expect($this->wallet->fresh()->balance)->toEqual('60.00');
});

it('lança InsufficientBalanceException ao sacar mais que o saldo', function () {
  $this->service->withdraw($this->wallet, '200.00');
})->throws(InsufficientBalanceException::class);

it('mantém o saldo intacto quando o saque falha', function () {
  try {
    $this->service->withdraw($this->wallet, '200.00');
  } catch (InsufficientBalanceException $e) {
  }

  expect($this->wallet->fresh()->balance)->toEqual('100.00');
  expect(Transaction::count())->toBe(0);
});

it('lida com precisão decimal sem erro de float', function () {
  $this->service->deposit($this->wallet, '0.10');
  $this->service->deposit($this->wallet, '0.20');

  expect($this->wallet->fresh()->balance)->toEqual('100.30');
});

it('faz rollback completo se a criação da transação falhar', function () {

  Transaction::creating(function () {
    throw new RuntimeException('Falha simulada na gravação da transação');
  });

  try {
    $this->service->deposit($this->wallet, '50.00');
  } catch (RuntimeException $e) {
  }

  expect($this->wallet->fresh()->balance)->toEqual('100.00');
  expect(Transaction::count())->toBe(0);
});
