<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perusahaan',
        'email',
        'website',
        'deskripsi',
        'logo',
        'alamat',
        'telepon',
        'peta_lokasi',
        'catatan',
    ];
}
