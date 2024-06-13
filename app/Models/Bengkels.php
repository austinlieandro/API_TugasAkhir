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
        'jenis_layanan',
        'hari_operasional',
        'jam_buka',
        'jam_tutup',
        'users_id',
    ];

    protected $casts = [
        'jenis_kendaraan' => 'json',
        'jenis_layanan' => 'json',
        'hari_operasional' => 'json',
    ];
}
