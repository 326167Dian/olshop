<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kdtk extends Model
{
    protected $table = 'kdtk';
    protected $primaryKey = 'id_kdtk';
    public $timestamps = false;

    protected $fillable = [
        'kd_trkasir',
        'id_admin',
        'stt_kdtk',
    ];
}
