<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Currency;
use App\Models\Account;
use App\Models\Transaction;
use App\Jobs\ProcessWithdrawalJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    /**
     * Запрос на списание средств.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'currency' => 'required|string|exists:currencies,code',
            'amount_principal' => 'required|numeric|min:0',
            'fee_network' => 'nullable|numeric|min:0',
            'fee_service' => 'nullable|numeric|min:0',
            'to_address' => 'required|string',
        ]);

        // Вычисляем общую сумму списания
        $amountPrincipal = $validated['amount_principal'];
        $feeNetwork = $validated['fee_network'] ?? 0;
        $feeService = $validated['fee_service'] ?? 0;
        $amountTotal = $amountPrincipal + $feeNetwork + $feeService;

        // Проверяем наличие средств (предварительная проверка, окончательная будет в job)
        $user = User::findOrFail($validated['user_id']);
        $currency = Currency::where('code', $validated['currency'])->firstOrFail();
        $account = Account::where('user_id', $user->id)
            ->where('currency_id', $currency->id)
            ->first();

        if (!$account || $account->balance < $amountTotal) {
            return response()->json(['error' => 'Insufficient funds'], 422);
        }

        // Создаём транзакцию со статусом pending
        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'account_id' => $account->id,
            'currency_id' => $currency->id,
            'type' => 'debit',
            'amount_total' => $amountTotal,
            'amount_principal' => $amountPrincipal,
            'amount_fee_network' => $feeNetwork,
            'amount_fee_service' => $feeService,
            'status' => 'pending',
            'metadata' => ['to_address' => $validated['to_address']],
        ]);

        // Диспатчим задачу
        ProcessWithdrawalJob::dispatch($transaction->id)->onQueue('balance_operations');

        return response()->json([
            'message' => 'Withdrawal request accepted',
            'withdrawal_id' => $transaction->uuid,
        ], 202);
    }
}
