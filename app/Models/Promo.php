<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = ['nama_promo', 'tanggal_awal', 'tanggal_akhir', 'nilai_diskon', 'status'];
}
