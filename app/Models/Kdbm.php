<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kdbm extends Model
{
    protected $table = 'kdbm';
    protected $primaryKey = 'id_kdbm';
    public $timestamps = false;

    protected $fillable = [
        'kd_trbmasuk',
        'id_resto',
        'id_admin',
        'stt_kdbm',
    ];
}
