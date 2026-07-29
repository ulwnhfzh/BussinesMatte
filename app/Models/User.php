<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
    'business_id',
    'name',
    'email',
    'password',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function business()
{
    return $this->belongsTo(Business::class);
}

}


