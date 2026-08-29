<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setheader extends Model
{
    protected $table = 'setheader';
    protected $primaryKey = 'id_setheader';
    public $timestamps = false;

    protected $fillable = [
        'satu',
        'dua',
        'tiga',
        'empat',
        'lima',
        'enam',
        'tujuh',
        'delapan',
        'sembilan',
        'sepuluh',
        'sebelas',
        'duabelas',
        'tigabelas',
        'empatbelas',
        'logo',
        'tandatangan',
    ];

    /**
     * File logo/tandatangan bisa datang dari dua sumber: yang diunggah lewat form
     * Header Struk Laravel (disimpan Storage::disk('public')->store('setheader', ...),
     * jadi nilainya "setheader/xxxx.ext") atau nilai lama dari legacy (cuma nama file
     * polos, mis. "logo.jpeg", fisiknya ada di public/apotekberlian/masuk/images/).
     * Dibedakan dari ada/tidaknya '/' di nilainya.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->logo);
    }

    public function getTandatanganUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->tandatangan);
    }

    private function resolveFileUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_contains($value, '/')) {
            return asset('storage/' . $value);
        }

        return asset('apotekberlian/masuk/images/' . rawurlencode($value));
    }
}
