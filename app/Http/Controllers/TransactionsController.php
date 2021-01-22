<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    public function deposit(User $user, CreateTransactionRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $transaction = $user->transactions()->sharedLock()->create(array_merge($data, ['type' => 'deposit', 'country' => $user->country]));

            $balanceUpdate = ['balance' => $data['amount'] + $user->balance];

            $count = $user->transactions()->whereType('deposit')->count();

            if ($count % 3 === 0 && $count !== 0) {
                $balanceUpdate = array_merge($balanceUpdate, ['bonus_balance' => $user->bonus_balance + $data['amount'] * $user->bonus]);
            }

            DB::table('users')->lockForUpdate()->where('id', $user->id)->update($balanceUpdate);

            DB::commit();
        } catch (\Exception $exception) {
            return response([
                'message' => $exception->getMessage()
            ], 500);
        }

        return $transaction->fresh();
    }

    public function withdraw(User $user, CreateTransactionRequest $request)
    {
        $data = $request->validated();

        if (!$user->canWithdraw($data['amount'])) {
            return response(['message' => 'Insufficient funds to finish the operation.'], 422);
        }

        DB::beginTransaction();

        try {
            $transaction = $user->transactions()->sharedLock()->create(array_merge($data, ['amount' => - $data['amount'], 'type' => 'withdraw', 'country' => $user->country]));

            DB::table('users')->lockForUpdate()->where('id', $user->id)->decrement('balance', $data['amount']);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            return response([
                'message' => $exception->getMessage()
            ], 500);
        }

        return $transaction->fresh();
    }
}
