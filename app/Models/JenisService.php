<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisService extends Model
{
    public $timestamps = false;
    protected $table = 'jenis_service';

    protected $fillable = [
        'nama_service',
        'users_id'
    ];
}
