<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bengkels extends Model
{
    public $timestamps = false;
    protected $table = 'bengkels';
    protected $fillable = [
        'nama_bengkel',
        'lokasi_bengkel',
        'number_bengkel',
        'alamat_bengkel',
        'gmaps_bengkel',
        'jenis_kendaraan',
        'hari_operasional',
        'jam_buka',
        'jam_tutup',
        'users_id',
    ];

    protected $casts = [
        'jenis_kendaraan' => 'json',
        'hari_operasional' => 'json',
    ];
}
