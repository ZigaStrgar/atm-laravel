<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

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
        return $this->amount >= $amount;
    }
}
