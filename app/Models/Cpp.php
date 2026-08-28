<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpp extends Model
{
    protected $table = 'cpp';
    protected $primaryKey = 'id_cpp';
    public $timestamps = true;

    protected $fillable = [
        'id_pelanggan',
        'no_cpp',
        'nama_pasien',
        'jk',
        'umur',
        'alamat',
        'telp',
        'tgl_ttd',
        'thn_ttd',
        'nama_apoteker',
        'sipa_apoteker',
        'created_by',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function detail()
    {
        return $this->hasMany(CppDetail::class, 'id_cpp', 'id_cpp')->orderBy('no_urut');
    }
}
