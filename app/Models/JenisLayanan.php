<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    public $timestamps = false;
    protected $table = 'jenis_layanan';

    protected $fillable = [
        'nama_layanan',
        'jenis_layanan',
        'harga_layanan',
        'bengkels_id',
    ];

    protected $casts = [
        'jenis_layanan' => 'json',
    ];
}
