<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Currency;
use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $currencyIds = Currency::pluck('id')->toArray();

        foreach ($users as $user) {
            // Случайное количество счетов от 1 до 3
            $numAccounts = rand(1, 3);
            // Выбираем случайные валюты без повторений для пользователя
            $selectedCurrencies = (array) array_rand(array_flip($currencyIds), $numAccounts);
            foreach ($selectedCurrencies as $currencyId) {
                Account::create([
                    'user_id' => $user->id,
                    'currency_id' => $currencyId,
                    'balance' => rand(100, 10000) / 100, // небольшой баланс
                    'version' => 1,
                ]);
            }
        }
    }
}
