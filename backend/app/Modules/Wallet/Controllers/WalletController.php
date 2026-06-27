<?php

namespace App\Modules\Wallet\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Requests\DepositRequest;
use App\Modules\Wallet\Requests\WithdrawRequest;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
  public function __construct(
    private readonly WalletService $walletService
  ) {
  }

  public function show(Request $request): JsonResponse
  {
    return response()->json([
      'balance' => $request->user()->wallet->balance,
    ]);
  }

  public function deposit(DepositRequest $request): JsonResponse
  {
    $transaction = $this->walletService->deposit(
      $request->user()->wallet,
      $request->validated()['amount']
    );

    return response()->json([
      'message' => 'Depósito realizado com sucesso.',
      'transaction' => $transaction,
      'balance' => $transaction->balance_after,
    ], 201);
  }

  public function withdraw(WithdrawRequest $request): JsonResponse
  {
    $transaction = $this->walletService->withdraw(
      $request->user()->wallet,
      $request->validated()['amount']
    );

    return response()->json([
      'message' => 'Saque realizado com sucesso.',
      'transaction' => $transaction,
      'balance' => $transaction->balance_after,
    ], 201);
  }

  public function dashboard(Request $request): JsonResponse
  {
    return response()->json(
      $this->walletService->overview($request->user()->wallet)
    );
  }
}
