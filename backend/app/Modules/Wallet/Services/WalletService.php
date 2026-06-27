<?php

namespace App\Modules\Wallet\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Modules\Wallet\Exceptions\InsufficientBalanceException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Modules\Wallet\Enums;

class WalletService
{

  public function deposit(Wallet $wallet, string $amount): Transaction
  {
    return DB::transaction(function () use ($wallet, $amount) {
      $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

      $balanceAfter = bcadd($wallet->balance, $amount, 2);

      $wallet->update(['balance' => $balanceAfter]);

      return Transaction::create([
        'wallet_id' => $wallet->id,
        'type' => Enums\TransactionTypeEnum::CREDIT,
        'amount' => $amount,
        'balance_after' => $balanceAfter,
        'status' => Enums\TransactionStatusEnum::COMPLETED,
      ]);
    });
  }

  public function withdraw(Wallet $wallet, string $amount): Transaction
  {
    return DB::transaction(function () use ($wallet, $amount) {
      $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

      if (bccomp($wallet->balance, $amount, 2) < 0) {
        throw new InsufficientBalanceException();
      }

      $balanceAfter = bcsub($wallet->balance, $amount, 2);

      $wallet->update(['balance' => $balanceAfter]);

      return Transaction::create([
        'wallet_id' => $wallet->id,
        'type' => Enums\TransactionTypeEnum::DEBIT,
        'amount' => $amount,
        'balance_after' => $balanceAfter,
        'status' => Enums\TransactionStatusEnum::COMPLETED,
      ]);
    });
  }

  public function listTransactions(Wallet $wallet, array $filters): LengthAwarePaginator
  {
    return Transaction::query()
      ->where('wallet_id', $wallet->id)
      ->when($filters['type'] ?? null, fn($q, $type) => $q->where('type', $type))
      ->when($filters['start_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
      ->when($filters['end_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
      ->latest()
      ->paginate($filters['per_page'] ?? 15);
  }

  public function overview(Wallet $wallet): array
  {
    $startOfMonth = now()->startOfMonth();

    $totalDeposited = Transaction::where('wallet_id', $wallet->id)
      ->where('type', 'credit')
      ->where('created_at', '>=', $startOfMonth)
      ->sum('amount');

    $totalWithdrawn = Transaction::where('wallet_id', $wallet->id)
      ->where('type', 'debit')
      ->where('created_at', '>=', $startOfMonth)
      ->sum('amount');

    $recent = Transaction::where('wallet_id', $wallet->id)
      ->latest()
      ->limit(5)
      ->get();

    return [
      'balance' => $wallet->balance,
      'month_deposited' => number_format((float) $totalDeposited, 2, '.', ''),
      'month_withdrawn' => number_format((float) $totalWithdrawn, 2, '.', ''),
      'recent_transactions' => $recent,
    ];
  }
}
