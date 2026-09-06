<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisJurnal extends Model
{
    protected $table = 'jenis_jurnal';
    protected $primaryKey = 'idjenis';
    public $timestamps = false;

    protected $fillable = [
        'nm_jurnal',
        'tipe',
    ];
}
