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

        try {
            $transaction = $this->handleTransaction($user, array_merge($data, ['type' => 'deposit']));
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        }

        return $transaction;
    }

    public function withdraw(User $user, CreateTransactionRequest $request)
    {
        $data = $request->validated();

        if (!$user->canWithdraw($data['amount'])) {
            return response()->json(['message' => 'Insufficient funds to finish the operation.'], 422);
        }

        try {
            $transaction = $this->handleTransaction($user, array_merge($data, ['type' => 'withdraw', 'amount' => -$data['amount']]));
        } catch (\Exception $exception) {
            return response([
                'message' => $exception->getMessage()
            ], 500);
        }

        return $transaction;
    }

    protected function handleTransaction(User $user, array $data)
    {
        DB::beginTransaction();

        try {
            $transaction = $user->transactions()->sharedLock()->create(array_merge($data, ['country' => $user->country]));

            DB::commit();

            return $transaction->fresh();
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
