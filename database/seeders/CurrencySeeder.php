<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'BTC', 'name' => 'Bitcoin', 'decimals' => 8, 'blockchain' => 'Bitcoin'],
            ['code' => 'ETH', 'name' => 'Ethereum', 'decimals' => 18, 'blockchain' => 'Ethereum'],
            ['code' => 'USDT', 'name' => 'Tether', 'decimals' => 6, 'blockchain' => 'Ethereum'],
            ['code' => 'BNB', 'name' => 'Binance Coin', 'decimals' => 8, 'blockchain' => 'BSC'],
            ['code' => 'SOL', 'name' => 'Solana', 'decimals' => 9, 'blockchain' => 'Solana'],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
