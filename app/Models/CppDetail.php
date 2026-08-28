<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CppDetail extends Model
{
    protected $table = 'cpp_detail';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_cpp',
        'no_urut',
        'tanggal',
        'nama_dokter',
        'nama_obat_dosis',
        'catatan',
    ];
}
