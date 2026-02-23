<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use App\Jobs\ProcessDepositJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    /**
     * Обработка входящего вебхука о зачислении.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'currency' => 'required|string|exists:currencies,code',
            'amount' => 'required|numeric|min:0',
            'tx_hash' => 'required|string|unique:transactions,external_id',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
        ]);

        // Сохраняем лог
        $log = WebhookLog::create([
            'event' => 'deposit',
            'payload' => $validated,
            'status' => 'pending',
        ]);

        // Диспатчим задачу
        ProcessDepositJob::dispatch($log->id)->onQueue('balance_operations');

        return response()->json(['message' => 'Webhook accepted'], 202);
    }
}
