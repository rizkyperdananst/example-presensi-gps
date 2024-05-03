<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tgl_presensi',
        'jam_in',
        'jam_out',
        'foto_in',
        'foto_out',
        'location_in',
        'location_out'
    ];
}
