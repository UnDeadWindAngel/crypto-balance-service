<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cryptos = [
            ['BTC', 'Bitcoin', 8, 'Bitcoin'],
            ['ETH', 'Ethereum', 18, 'Ethereum'],
            ['USDT', 'Tether', 6, 'Ethereum'],
            ['BNB', 'Binance Coin', 8, 'BSC'],
            ['SOL', 'Solana', 9, 'Solana'],
            ['XRP', 'Ripple', 6, 'Ripple'],
            ['ADA', 'Cardano', 6, 'Cardano'],
            ['DOT', 'Polkadot', 10, 'Polkadot'],
        ];
        $crypto = $this->faker->randomElement($cryptos);

        return [
            'code' => $crypto[0],
            'name' => $crypto[1],
            'decimals' => $crypto[2],
            'blockchain' => $crypto[3],
        ];
    }
}
