<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WebhookController;

// Маршруты для получения информации (пока без аутентификации, позже добавим middleware)
Route::get('/accounts/{user}', [BalanceController::class, 'index'])->name('accounts.index');
Route::get('/transactions/{user}', [TransactionController::class, 'index'])->name('transactions.index');

// Маршруты для операций (будем добавлять позже)
Route::post('/webhook/deposit', [WebhookController::class, 'deposit'])->name('webhook.deposit');
// Route::post('/withdrawals', [WithdrawalController::class, 'store']);
