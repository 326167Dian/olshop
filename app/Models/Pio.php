<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pio extends Model
{
    protected $table = 'pio';
    protected $primaryKey = 'id_pio';

    public const JENIS_PERTANYAAN = [
        'identifikasi_obat' => 'Identifikasi Obat',
        'interaksi_obat' => 'Interaksi Obat',
        'harga_obat' => 'Harga Obat',
        'kontra_indikasi' => 'Kontra Indikasi',
        'cara_pemakaian' => 'Cara Pemakaian',
        'stabilitas' => 'Stabilitas',
        'dosis' => 'Dosis',
        'keracunan' => 'Keracunan',
        'efek_samping' => 'Efek Samping Obat',
        'penggunaan_terapeutik' => 'Penggunaan Terapeutik',
        'farmakokinetika' => 'Farmakokinetika',
        'farmakodinamika' => 'Farmakodinamika',
        'ketersediaan_obat' => 'Ketersediaan Obat',
        'lain_lain' => 'Lain-lain',
    ];

    protected $fillable = [
        'id_pelanggan',
        'no_pio',
        'tanggal',
        'waktu',
        'metode',
        'nama_penanya',
        'no_telp_penanya',
        'status_penanya',
        'status_penanya_ket',
        'umur_pasien',
        'tinggi_pasien',
        'berat_pasien',
        'jenis_kelamin',
        'kehamilan',
        'kehamilan_minggu',
        'menyusui',
        'uraian_pertanyaan',
        'jenis_pertanyaan_identifikasi_obat',
        'jenis_pertanyaan_stabilitas',
        'jenis_pertanyaan_farmakokinetika',
        'jenis_pertanyaan_interaksi_obat',
        'jenis_pertanyaan_dosis',
        'jenis_pertanyaan_farmakodinamika',
        'jenis_pertanyaan_harga_obat',
        'jenis_pertanyaan_keracunan',
        'jenis_pertanyaan_ketersediaan_obat',
        'jenis_pertanyaan_kontra_indikasi',
        'jenis_pertanyaan_efek_samping',
        'jenis_pertanyaan_cara_pemakaian',
        'jenis_pertanyaan_penggunaan_terapeutik',
        'jenis_pertanyaan_lain_lain',
        'jenis_pertanyaan_lain_lain_ket',
        'jawaban',
        'referensi',
        'penyampaian_jawaban',
        'apoteker_penjawab',
        'tanggal_jawab',
        'waktu_jawab',
        'metode_jawab',
        'created_by',
    ];

    protected $casts = [
        'kehamilan' => 'boolean',
        'menyusui' => 'boolean',
        'jenis_pertanyaan_identifikasi_obat' => 'boolean',
        'jenis_pertanyaan_stabilitas' => 'boolean',
        'jenis_pertanyaan_farmakokinetika' => 'boolean',
        'jenis_pertanyaan_interaksi_obat' => 'boolean',
        'jenis_pertanyaan_dosis' => 'boolean',
        'jenis_pertanyaan_farmakodinamika' => 'boolean',
        'jenis_pertanyaan_harga_obat' => 'boolean',
        'jenis_pertanyaan_keracunan' => 'boolean',
        'jenis_pertanyaan_ketersediaan_obat' => 'boolean',
        'jenis_pertanyaan_kontra_indikasi' => 'boolean',
        'jenis_pertanyaan_efek_samping' => 'boolean',
        'jenis_pertanyaan_cara_pemakaian' => 'boolean',
        'jenis_pertanyaan_penggunaan_terapeutik' => 'boolean',
        'jenis_pertanyaan_lain_lain' => 'boolean',
        'tanggal' => 'date',
        'tanggal_jawab' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
