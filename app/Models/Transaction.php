<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'user_id', 'account_id', 'currency_id',
        'type', 'amount_total', 'amount_principal', 'amount_fee_network', 'amount_fee_service',
        'status', 'external_id', 'metadata'
    ];

    protected $casts = [
        'amount_total' => 'decimal:8',
        'amount_principal' => 'decimal:8',
        'amount_fee_network' => 'decimal:8',
        'amount_fee_service' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
