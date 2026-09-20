<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiAntar extends Model
{
    protected $table = 'lokasi_antar';

    protected $fillable = [
        'nama_kelurahan',
        'biaya_antar',
    ];
}
