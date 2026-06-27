<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
  /**
   * Registra um novo usuário e inicializa a carteira zerada.
   */
  public function register(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => $data['password'],
      ]);

      Wallet::create([
        'user_id' => $user->id,
        'balance' => 0,
      ]);

      $token = $user->createToken('auth_token')->plainTextToken;

      return ['user' => $user, 'token' => $token];
    });
  }

  /**
   * Autentica e gera o token de acesso.
   */
  public function login(array $credentials): array
  {
    $user = User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
      throw ValidationException::withMessages([
        'email' => ['As credenciais informadas estão incorretas.'],
      ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return ['user' => $user, 'token' => $token];
  }
}
