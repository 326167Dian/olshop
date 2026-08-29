<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    protected $table = 'hasil_ujian';
    protected $primaryKey = 'id_hasil';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_admin',
        'username',
        'nama_lengkap',
        'ujian_id',
        'nama_ujian',
        'total_soal',
        'jawaban_benar',
        'jawaban_salah',
        'tidak_dijawab',
        'soal_tidak_valid',
        'nilai_akhir',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_detik',
        'durasi_batas_detik',
        'status_waktu',
        'jawaban_json',
    ];

    protected $casts = [
        'jawaban_json' => 'array',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'nilai_akhir' => 'float',
    ];
}
