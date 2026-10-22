<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $fillable = [
        'user_id',
        'rating',
        'isi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
