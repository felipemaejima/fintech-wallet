<?php

namespace App\Modules\Wallet\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientBalanceException extends Exception
{
  protected $message = 'Saldo insuficiente para realizar o saque.';

  public function render(): JsonResponse
  {
    return response()->json([
      'message' => $this->getMessage(),
    ], 422);
  }
}
