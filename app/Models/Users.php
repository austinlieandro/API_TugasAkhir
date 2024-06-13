<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'user_bengkel',
    ];
}
