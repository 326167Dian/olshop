<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meso extends Model
{
    protected $table = 'meso';
    protected $primaryKey = 'id_meso';

    protected $fillable = [
        'id_pelanggan',
        'kode_sumber_data',
        'nama_singkat',
        'umur',
        'suku',
        'berat_badan',
        'pekerjaan',
        'jenis_kelamin',
        'status_hamil',
        'penyakit_utama',
        'gangguan_ginjal',
        'gangguan_hati',
        'alergi',
        'kondisi_medis_lain',
        'kondisi_medis_lain_ket',
        'kesudahan_penyakit',
        'manifestasi_eso',
        'masalah_mutu_produk',
        'tanggal_mula_eso',
        'kesudahan_eso',
        'riwayat_eso',
        'data_obat',
        'keterangan_tambahan',
        'data_laboratorium',
        'tanggal_pemeriksaan_lab',
        'tanggal_laporan',
        'nama_pelapor',
        'created_by',
    ];

    protected $casts = [
        'gangguan_ginjal' => 'boolean',
        'gangguan_hati' => 'boolean',
        'alergi' => 'boolean',
        'kondisi_medis_lain' => 'boolean',
        'data_obat' => 'array',
        'tanggal_mula_eso' => 'date',
        'tanggal_pemeriksaan_lab' => 'date',
        'tanggal_laporan' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
