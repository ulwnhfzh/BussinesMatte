<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'total_cost',
        'total_profit',
        'payment_method',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}