<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\AccountResource;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * Получить все счета пользователя с балансами.
     *
     * @param  int  $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($user)
    {
        $userModel = User::findOrFail($user);
        $accounts = $userModel->accounts()->with('currency')->get();

        return AccountResource::collection($accounts);
    }
}
