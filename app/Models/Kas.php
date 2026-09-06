<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';
    protected $primaryKey = 'id_kas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_kas',
        'saldo',
    ];
}
