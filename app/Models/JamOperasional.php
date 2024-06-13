<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamOperasional extends Model
{
    public $timestamps = false;
    protected $table = 'jam_operasional';

    protected $fillable = [
        'jam_operasional',
        'slot',
        'bengkels_id'
    ];

    protected $casts = [
        'jam_operasional' => 'json',
        'slot' => 'json',
    ];
}
