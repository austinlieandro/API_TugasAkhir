<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    public $timestamps = true;
    protected $table = 'reservasi';
    protected $fillable = [
        'status_reservasi',
        'tanggal_reservasi',
        'jam_reservasi',
        'jeniskendala_reservasi',
        'detail_reservasi',
        'bengkels_id',
        'users_id',
        'karyawan_id',
    ];
}
