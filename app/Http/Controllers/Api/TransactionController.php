<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Получить историю транзакций пользователя.
     *
     * @param  Request  $request
     * @param  int  $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $userModel = User::findOrFail($user);

        $transactions = $userModel->transactions()
            ->with(['account.currency', 'currency'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
