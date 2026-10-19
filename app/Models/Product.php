<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'barang';

    protected $primaryKey = 'id_barang';

    public $incrementing = true;

    public $timestamps = false; // Karena tidak ada field created_at dan updated_at

    protected $fillable = [
        'kd_barang',
        'category_id',
        'nm_barang',
        'stok_barang',
        'stok_buffer',
        'stok_grosir',
        'sat_barang',
        'sat_grosir',
        'konversi',
        'jenisobat',
        'hna',
        'diskon',
        'hrgsat_barang',
        'hrgsat_grosir',
        'hrgjual_barang',
        'hrgjual_barang1',
        'hrgjual_barang2',
        'hrgjual_barang3',
        'hrgjual_barang4',
        'hrgjual_barang5',
        'margin',
        'komisi',
        'zataktif',
        'indikasi',
        'ket_barang',
        'petugas',
        'dosis',
        'waktu',
        't30',
        't60',
        'gr',
        'q30',
        'status',
        'image',
        'promosi',
        'tgl',
        'updated_by'
    ];

    protected $casts = [
        'stok_barang'     => 'double',
        'stok_buffer'     => 'double',
        'hna'             => 'double',
        'diskon'          => 'double',
        'hrgsat_barang'   => 'double',
        'hrgjual_barang'  => 'double',
        'komisi'          => 'double',
        'waktu'           => 'datetime',
        't30'             => 'integer',
        't60'             => 'integer',
        'gr'              => 'integer',
        'q30'             => 'integer',
        'status'          => 'string',
    ];

    public function jenisObat()
    {
        return $this->belongsTo(JenisObat::class, 'jenisobat', 'jenisobat');
        // (ModelTujuan::class, foreign_key_di_produk, primary_key_di_jenis_obat)
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
        // (ModelTujuan::class, foreign_key_di_produk, primary_key_di_category)
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'id_admin');
    }

    /**
     * ket_barang biasanya HTML dari CKEditor (sudah punya <p>/<br> sendiri),
     * tapi sebagian data lama cuma teks polos dengan baris baru literal yang
     * akan hilang kalau dicetak mentah ke HTML. nl2br() di sini cuma dipakai
     * saat belum ada tag block/br sama sekali, supaya tidak dobel breaknya
     * saat kontennya memang sudah HTML asli dari CKEditor.
     */
    public function getKetBarangHtmlAttribute(): string
    {
        $value = (string) $this->ket_barang;

        if ($value === '') {
            return '';
        }

        if (preg_match('/<(p|br|div|li|ul|ol)\b/i', $value)) {
            return $value;
        }

        return nl2br(e($value));
    }
}
