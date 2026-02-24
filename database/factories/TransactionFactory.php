<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Account;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit', 'debit']);
        $status = $this->faker->randomElement(['completed', 'pending', 'failed']);

        // Для реализма: если тип debit, суммы включают комиссии
        $amountPrincipal = $this->faker->randomFloat(8, 0.001, 10);
        $feeNetwork = $this->faker->randomFloat(8, 0, 0.001);
        $feeService = $this->faker->randomFloat(8, 0, 0.0005);
        $amountTotal = $amountPrincipal + $feeNetwork + $feeService;

        return [
            'uuid' => $this->faker->uuid,
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'currency_id' => Currency::factory(),
            'type' => $type,
            'amount_total' => $amountTotal,
            'amount_principal' => $amountPrincipal,
            'amount_fee_network' => $feeNetwork,
            'amount_fee_service' => $feeService,
            'status' => $status,
            'external_id' => $this->faker->optional()->sha256,
            'metadata' => json_encode(['note' => $this->faker->sentence]),
        ];
    }
}
