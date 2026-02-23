<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Currency;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public $tries = 3;
    public $backoff = [5, 15, 60]; // экспоненциальная задержка

    protected int $webhookLogId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $webhookLogId)
    {
        $this->webhookLogId = $webhookLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookLog = WebhookLog::find($this->webhookLogId);

        if (!$webhookLog || $webhookLog->status !== 'pending') {
            // Уже обработано или не существует
            return;
        }

        $payload = $webhookLog->payload;
        $user_id = $payload['user_id'];
        $currencyCode = $payload['currency'];
        $amount = $payload['amount'];
        $txHash = $payload['tx_hash'];
        $metadata = [
            'from' => $payload['from'] ?? null,
            'to' => $payload['to'] ?? null,
        ];

        // Получаем валюту
        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            $this->fail($webhookLog, "Currency not found: {$currencyCode}");
            return;
        }

        // Проверяем, существует ли уже транзакция с таким tx_hash
        $existingTransaction = Transaction::where('external_id', $txHash)->first();
        if ($existingTransaction) {
            // Если уже есть completed, просто помечаем лог как обработанный
            if ($existingTransaction->status === 'completed') {
                $webhookLog->update(['status' => 'processed']);
                return;
            }
            // Если pending, возможно, ждём, но для простоты пропустим повтор
            $this->fail($webhookLog, "Transaction already exists with status: {$existingTransaction->status}");
            return;
        }

        // Находим или создаём счёт пользователя для данной валюты
        $account = Account::firstOrCreate(
            ['user_id' => $user_id, 'currency_id' => $currency->id],
            ['balance' => 0]
        );

        // Блокировка на аккаунт через Redis
        $lock = Redis::set("account_lock:{$account->id}", true, 'EX', 10, 'NX');
        if (!$lock) {
            // Не удалось получить блокировку, перезапустим задачу позже
            $this->release(5);
            return;
        }

        try {
            // Транзакция БД
            DB::transaction(function () use ($account, $amount, $txHash, $metadata, $currency, $user_id, $webhookLog) {
                // Пессимистичная блокировка строки счёта
                $account = Account::where('id', $account->id)->lockForUpdate()->first();

                // Создаём транзакцию
                $transaction = Transaction::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $user_id,
                    'account_id' => $account->id,
                    'currency_id' => $currency->id,
                    'type' => 'credit',
                    'amount_total' => $amount,
                    'amount_principal' => $amount,
                    'amount_fee_network' => 0,
                    'amount_fee_service' => 0,
                    'status' => 'pending',
                    'external_id' => $txHash,
                    'metadata' => $metadata,
                ]);

                // Увеличиваем баланс
                $account->balance += $amount;
                $account->save();

                // Обновляем статус транзакции
                $transaction->status = 'completed';
                $transaction->save();

                // Обновляем лог
                $webhookLog->status = 'processed';
                $webhookLog->save();
            });

            Redis::del("account_lock:{$account->id}"); // освобождаем блокировку
        } catch (\Exception $e) {
            Redis::del("account_lock:{$account->id}");
            $this->fail($webhookLog, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Пометить лог как ошибочный и записать ошибку.
     */
    protected function fail(WebhookLog $webhookLog, string $error): void
    {
        $webhookLog->status = 'failed';
        $webhookLog->error = $error;
        $webhookLog->save();
        Log::error("Deposit job failed: {$error}", ['webhook_log_id' => $webhookLog->id]);
    }
}
