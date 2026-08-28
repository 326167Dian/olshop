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
}
