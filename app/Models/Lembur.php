<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    protected $table = 'lembur';
    protected $primaryKey = 'id_lembur';

    protected $fillable = [
        'id_admin',
        'id_absensi',
        'tanggal',
        'jam_lembur',
        'sumber',
        'status_approval',
        'ditarik_ke_gaji',
        'id_gaji_detail',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ditarik_ke_gaji' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }

    public function gajiDetail()
    {
        return $this->belongsTo(GajiDetail::class, 'id_gaji_detail', 'id_gaji_detail');
    }

    public function pencatat()
    {
        return $this->belongsTo(Admin::class, 'dicatat_oleh', 'id_admin');
    }
}
