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
        'unit',
        'description',
        'image'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}