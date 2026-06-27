<?php

namespace Database\Seeders;

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

      Wallet::firstOrCreate(
        ['user_id' => $user->id],
        ['balance' => 1000.00]
      );
    });
  }
}
