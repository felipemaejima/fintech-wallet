<?php

namespace App\Modules\Wallet\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Requests\ListTransactionsRequest;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
  public function __construct(
    private readonly WalletService $walletService
  ) {
  }

  public function index(ListTransactionsRequest $request): JsonResponse
  {
    $transactions = $this->walletService->listTransactions(
      $request->user()->wallet,
      $request->validated()
    );

    return response()->json($transactions);
  }
}
