<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiAntar extends Model
{
    protected $table = 'lokasi_antar';

    protected $fillable = [
        'jarak_min',
        'jarak_max',
        'biaya_antar',
    ];

    /**
     * Tier tarif (reguler) yang mencakup jarak $km. Express dihitung on-the-fly
     * (2x biaya_antar), tidak disimpan sebagai baris terpisah.
     */
    public static function tierUntukJarak(float $km): ?self
    {
        return static::where('jarak_min', '<=', $km)
            ->where('jarak_max', '>=', $km)
            ->orderBy('jarak_min')
            ->first();
    }
}
