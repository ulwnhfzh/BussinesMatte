<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'logo',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}