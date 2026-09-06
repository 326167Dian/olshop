<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalKas extends Model
{
    protected $table = 'jurnal';
    protected $primaryKey = 'id_jurnal';
    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'ket',
        'petugas',
        'idjenis',
        'debit',
        'kredit',
        'carabayar',
        'current',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'current' => 'datetime',
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisJurnal::class, 'idjenis', 'idjenis');
    }
}
