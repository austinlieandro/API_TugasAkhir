<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prioritas extends Model
{
    public $timestamps = false;
    protected $table = 'prioritas';

    protected $fillable = [
        'jenis_kendaraan',
        'jenis_kerusakan',
        'bobot_nilai',
        'bobot_estimasi',
        'bobot_urgensi',
        'bobot_harga',
        'bengkels_id'
    ];
}
