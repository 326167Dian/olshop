<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjianProgress extends Model
{
    protected $table = 'ujian_progress';
    protected $primaryKey = 'id_progress';
    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'username',
        'nama_lengkap',
        'ujian_id',
        'nama_ujian',
        'jawaban_json',
        'waktu_mulai',
        'waktu_update',
    ];

    protected $casts = [
        'jawaban_json' => 'array',
    ];
}
