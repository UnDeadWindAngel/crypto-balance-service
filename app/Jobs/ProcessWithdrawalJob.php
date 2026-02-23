<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWithdrawalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public $tries = 3;
    public $backoff = [5, 15, 60];

    protected int $transactionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = Transaction::with('account')->find($this->transactionId);

        if (!$transaction) {
            Log::error("Transaction not found for withdrawal job", ['id' => $this->transactionId]);
            return;
        }

        // Если уже обработана (completed или failed), выходим
        if ($transaction->status !== 'pending') {
            return;
        }

        $account = $transaction->account;
        $amountTotal = $transaction->amount_total;

        // Блокировка на аккаунт
        $lockKey = "account_lock:{$account->id}";
        $lock = Redis::set($lockKey, true, 'EX', 10, 'NX');
        if (!$lock) {
            // Не удалось получить блокировку, повторим позже
            $this->release(5);
            return;
        }

        try {
            DB::transaction(function () use ($transaction, $account, $amountTotal) {
                // Пессимистичная блокировка строки счёта
                $account = Account::where('id', $account->id)->lockForUpdate()->first();

                // Повторная проверка баланса (мог измениться после создания транзакции)
                if ($account->balance < $amountTotal) {
                    // Недостаточно средств -> помечаем транзакцию как failed
                    $transaction->status = 'failed';
                    $transaction->metadata = array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'insufficient_funds'
                    ]);
                    $transaction->save();

                    // Освобождаем блокировку и выходим без повторного запуска
                    Redis::del("account_lock:{$account->id}");
                    return;
                }

                // Уменьшаем баланс
                $account->balance -= $amountTotal;
                $account->save();

                // Обновляем статус транзакции
                $transaction->status = 'completed';
                $transaction->save();

                // Здесь можно добавить вызов внешнего сервиса для отправки в блокчейн
                // или диспатч события WithdrawalApproved
            });

            Redis::del($lockKey);
        } catch (\Exception $e) {
            Redis::del($lockKey);
            Log::error("Withdrawal job failed", [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage()
            ]);

            // Помечаем транзакцию как failed (если не была изменена)
            $transaction->status = 'failed';
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'failure_reason' => 'exception',
                'exception' => $e->getMessage()
            ]);
            $transaction->save();

            // Повторно выбрасываем исключение, чтобы job попал в failed_jobs
            throw $e;
        }
    }
}
