<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $appends = ['balance', 'bonus_balance'];

    public function getCountryDetailsAttribute()
    {
        return Countries::where('cca2', $this->country);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function canWithdraw($amount): bool
    {
        return $this->balance >= $amount;
    }

    public function getBalanceAttribute(): float
    {
        return $this->transactions()->sharedLock()->sum('amount') ?? 0;
    }

    public function getBonusBalanceAttribute(): float
    {
        $transactions = $this->transactions()->sharedLock()->whereType('deposit')->get();

        return $transactions->filter(function ($transaction, $index) {
                return ($index + 1) % 3 === 0;
            })->sum('amount') * $this->bonus;
    }
}
