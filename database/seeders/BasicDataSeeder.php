<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Currency;
use App\Models\Account;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём 5 пользователей (если таблица users пуста)
        if (User::count() === 0) {
            User::factory(5)->create();
        }

        // Создаём валюты
        $currencies = [
            ['code' => 'BTC', 'name' => 'Bitcoin', 'decimals' => 8, 'blockchain' => 'Bitcoin'],
            ['code' => 'ETH', 'name' => 'Ethereum', 'decimals' => 18, 'blockchain' => 'Ethereum'],
            ['code' => 'USDT', 'name' => 'Tether', 'decimals' => 6, 'blockchain' => 'Ethereum'],
            ['code' => 'BNB', 'name' => 'Binance Coin', 'decimals' => 8, 'blockchain' => 'BSC'],
            ['code' => 'SOL', 'name' => 'Solana', 'decimals' => 9, 'blockchain' => 'Solana'],
        ];

        foreach ($currencies as $curr) {
            Currency::firstOrCreate(['code' => $curr['code']], $curr);
        }

        // Для каждого пользователя создаём 1-3 счёта в случайных валютах
        $users = User::all();
        $currencyIds = Currency::pluck('id')->toArray();

        foreach ($users as $user) {
            $numAccounts = rand(1, 3);
            $selectedCurrencies = (array) array_rand(array_flip($currencyIds), $numAccounts);
            foreach ($selectedCurrencies as $currencyId) {
                Account::firstOrCreate(
                    ['user_id' => $user->id, 'currency_id' => $currencyId],
                    ['balance' => rand(100, 10000) / 100] // случайный начальный баланс
                );
            }
        }
    }
}
