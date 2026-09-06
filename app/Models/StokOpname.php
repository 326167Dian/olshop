<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    protected $table = 'stok_opname';
    protected $primaryKey = 'id_stok_opname';
    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'kd_barang',
        'stok_sistem',
        'stok_fisik',
        'exp_date',
        'jml',
        'selisih',
        'hrgsat_barang',
        'ttl_hrgbrg',
        'tgl_current',
        'tgl_stokopname',
        'shift',
        'id_admin',
    ];

    /**
     * `exp_date` SENGAJA TIDAK di-cast ke 'date' -- nilai kosong disimpan sebagai
     * sentinel '0000-00-00' (lihat simpan_stokopname.php legacy & InventoryStokopnameController),
     * dan Carbon/Eloquent men-parse zero-date itu jadi tanggal tak masuk akal
     * ("-0001-11-30 00:00:00") alih-alih menampilkannya apa adanya. Dibiarkan
     * string mentah, sama seperti PDO legacy menampilkannya.
     */
    protected $casts = [
        'tgl_current' => 'datetime',
        'tgl_stokopname' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Product::class, 'id_barang', 'id_barang');
    }
}
