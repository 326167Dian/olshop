<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SatuanBarang extends Model
{
    protected $table = 'satuan_barang';

    protected $fillable = [
        'kd_barang',
        'urutan',
        'id_satuan',
        'rasio_ke_bawah',
        'harga_jual',
        'harga_beli',
        'status',
    ];

    protected $casts = [
        'rasio_ke_bawah' => 'float',
        'harga_jual' => 'float',
        'harga_beli' => 'float',
    ];

    public function barang()
    {
        return $this->belongsTo(Product::class, 'kd_barang', 'kd_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan', 'id_satuan');
    }

    /**
     * Faktor kumulatif tiap tingkat satuan terhadap satuan dasar (urutan=1),
     * dihitung berantai dari rasio_ke_bawah -- TIDAK disimpan di DB supaya
     * rasio boleh diedit bebas kapan saja tanpa merusak baris yang sudah ada
     * (lihat catatan di migration create_satuan_barang_table).
     *
     * @param  Collection<int, SatuanBarang>  $tingkat  diurutkan/akan diurutkan ASC by urutan
     * @return array<int, float>  [urutan => kumulatif]
     */
    public static function kumulatifMap(Collection $tingkat): array
    {
        $map = [];
        $running = 1.0;

        foreach ($tingkat->sortBy('urutan') as $t) {
            $running = $t->urutan === 1 ? 1.0 : $running * (float) $t->rasio_ke_bawah;
            $map[$t->urutan] = $running;
        }

        return $map;
    }

    /**
     * Konversi qty dari satu tingkat satuan ke tingkat lain pada item yang sama.
     */
    public static function konversi(Collection $tingkat, float $qty, int $dariUrutan, int $keUrutan): float
    {
        $kumulatif = static::kumulatifMap($tingkat);

        if (!isset($kumulatif[$dariUrutan]) || !isset($kumulatif[$keUrutan])) {
            throw new \InvalidArgumentException('Tingkat satuan tidak ditemukan.');
        }

        return $qty * $kumulatif[$dariUrutan] / $kumulatif[$keUrutan];
    }
}
