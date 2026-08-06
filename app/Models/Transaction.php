<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /*
     * Status transaksi.
     * - completed : transaksi selesai.
     * - returned  : seluruh atau sebagian item dikembalikan.
     * - refunded  : pengembalian dana penuh.
     * - voided    : transaksi dibatalkan.
     */
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_VOIDED = 'voided';

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
        'status',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label status untuk ditampilkan di berbagai halaman.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_RETURNED => 'Retur',
            self::STATUS_REFUNDED => 'Refund',
            self::STATUS_VOIDED => 'Void',
            default => 'Selesai',
        };
    }
}
