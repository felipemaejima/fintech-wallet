<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Wallet\Controllers\TransactionController;
use App\Modules\Wallet\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);

  Route::get('/wallet', [WalletController::class, 'show']);
  Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
  Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
  Route::get('/dashboard', [WalletController::class, 'dashboard']);

  Route::get('/transactions', [TransactionController::class, 'index']);
});
