<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const TRANSACTION_TYPE_DEPOSIT = 'DEPOSIT';
    const TRANSACTION_TYPE_WITHDRAWAL = 'WITHDRAWAL';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
        'fee',
        'date',
    ];
}
