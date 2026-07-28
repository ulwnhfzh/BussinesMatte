<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',           // Nama lengkap
        'business_name',  // Nama bisnis (tambahkan)
        'email',          // Email
        'password',       // Password
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}