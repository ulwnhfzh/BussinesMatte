<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'business_id',
        'product_code',
        'name',
        'category',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'maximum_stock',   // <-- WAJIB ditambahkan
        'unit',
        'description',
        'image'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Accessor untuk menentukan status stok
     */
    public function getStatusAttribute()
    {
        if ($this->stock >= $this->minimum_stock && $this->stock <= $this->maximum_stock) {
            return 'optimal';
        } elseif ($this->stock < $this->minimum_stock) {
            return 'kritis';
        } else {
            return 'peringatan'; // stok melebihi maksimum
        }
    }
}