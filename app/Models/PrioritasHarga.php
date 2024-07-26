<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrioritasHarga extends Model
{
    public $timestamps = false;
    protected $table = 'prioritas_harga';

    protected $fillable = [
        'harga',
        'bobot_nilai',
        'bengkels_id'
    ];
}
