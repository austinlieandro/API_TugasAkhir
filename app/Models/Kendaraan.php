<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    public $timestamps = false;
    protected $table = 'kendaraan';
    protected $fillable = [
        'jenis_kendaraan',
        'plat_kendaraan',
        'users_id',
        'merek_kendaraan_id'
    ];
}
