<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $with = ['user'];

    protected $guarded = [];

    protected $dates = ['created_at', 'modifed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
