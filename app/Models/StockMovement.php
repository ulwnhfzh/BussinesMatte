<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    /*
     * Jenis-jenis perubahan stok.
     * Constant dipakai agar tidak menulis string berbeda-beda
     * di controller atau service.
     */
    public const TYPE_INITIAL = 'initial';
    public const TYPE_SALE = 'sale';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RETURN = 'return';

    /**
     * Kolom yang boleh diisi menggunakan StockMovement::create().
     */
    protected $fillable = [
        'business_id',
        'product_id',
        'user_id',
        'product_code',
        'product_name',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'note',
    ];

    /**
     * Memastikan nilai stok dikembalikan sebagai integer.
     */
    protected $casts = [
        'business_id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'reference_id' => 'integer',
    ];

    /**
     * Produk yang mengalami perubahan stok.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * User yang melakukan perubahan stok.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sumber perubahan stok.
     *
     * Contohnya perubahan stok dapat berasal dari Transaction,
     * StockAdjustment, atau Purchase Order.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Membatasi query berdasarkan bisnis yang sedang aktif.
     *
     * Contoh:
     * StockMovement::forBusiness($businessId)->get();
     */
    public function scopeForBusiness(
        Builder $query,
        int $businessId
    ): Builder {
        return $query->where('business_id', $businessId);
    }

    /**
     * Menentukan apakah stok bertambah.
     */
    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Menentukan apakah stok berkurang.
     */
    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }

    /**
     * Label jenis aktivitas untuk ditampilkan di halaman.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_INITIAL => 'Stok Awal',
            self::TYPE_SALE => 'Penjualan',
            self::TYPE_PURCHASE => 'Stok Masuk',
            self::TYPE_ADJUSTMENT => 'Penyesuaian Stok',
            self::TYPE_RETURN => 'Retur',
            default => 'Aktivitas Stok',
        };
    }
}