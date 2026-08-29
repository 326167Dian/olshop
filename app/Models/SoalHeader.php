<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalHeader extends Model
{
    protected $table = 'soal_header';
    protected $primaryKey = 'id_soal';
    public $timestamps = false;

    protected $fillable = [
        'nm_ujian',
        'durasi',
    ];

    public function soal()
    {
        return $this->hasMany(Soal::class, 'id_soal', 'id_soal')->orderBy('id');
    }
}
