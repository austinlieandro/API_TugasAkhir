<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerekKendaraan extends Model
{
    public $timestamps = false;
    protected $table = 'merek_kendaraan';
    protected $fillable = [
        'jenis_kendaraan',
        'merek_kendaraan',
        'users_id',
    ];
}
