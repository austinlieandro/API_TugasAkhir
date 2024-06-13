<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class karyawan extends Model
{
    public $timestamps = false;
    protected $table = 'karyawan';
    protected $fillable = [
        'nama_karyawan',
        'bengkels_id',
    ];
}
