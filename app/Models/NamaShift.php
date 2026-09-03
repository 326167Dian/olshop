<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NamaShift extends Model
{
    protected $table = 'namashift';
    protected $primaryKey = 'id_shift';
    public $timestamps = false;

    protected $fillable = [
        'shift',
        'nama_shift',
    ];
}
