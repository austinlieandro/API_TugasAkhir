<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorit extends Model
{
    public $timestamps = false;
    protected $table = 'favorit';
    protected $fillable = [
        'users_id',
        'bengkels_id',
    ];
}
