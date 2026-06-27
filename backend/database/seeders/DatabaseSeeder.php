<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    DB::transaction(function () {
      $user = User::firstOrCreate(
        ['email' => 'teste@fintech.com'],
        [
          'name' => 'Usuário de Teste',
          'password' => 'password',
        ]
      );

      $wallet = Wallet::firstOrCreate(
        ['user_id' => $user->id],
        ['balance' => 0]
      );

      if ($wallet->transactions()->exists()) {
        return;
      }

      $this->seedTransactions($wallet);
    });
  }

  private function seedTransactions(Wallet $wallet): void
  {
    $balance = '0.00';
    $transactions = [];

    $date = now()->subDays(90);

    for ($i = 0; $i < 50; $i++) {
      $date = $date->copy()->addHours(random_int(2, 48));

      $amount = number_format(random_int(500, 50000) / 100, 2, '.', '');

      $canDebit = bccomp($balance, $amount, 2) >= 0;
      $type = ($canDebit && random_int(0, 1) === 1) ? 'debit' : 'credit';

      $balance = $type === 'credit'
        ? bcadd($balance, $amount, 2)
        : bcsub($balance, $amount, 2);

      $transactions[] = [
        'id' => (string) \Illuminate\Support\Str::uuid7(),
        'wallet_id' => $wallet->id,
        'type' => $type,
        'amount' => $amount,
        'balance_after' => $balance,
        'status' => 'completed',
        'created_at' => $date,
        'updated_at' => $date,
      ];
    }

    Transaction::insert($transactions);

    $wallet->update(['balance' => $balance]);
  }
}
