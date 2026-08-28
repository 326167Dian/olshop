<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaraBayar extends Model
{
    protected $table = 'carabayar';
    protected $primaryKey = 'id_carabayar';
    public $timestamps = false;

    protected $fillable = [
        'nm_carabayar',
        'urutan',
    ];
}
