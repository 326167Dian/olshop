<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_hp',
        'alamat',
        'nama_bank',
        'no_rekening',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referredCustomers()
    {
        return $this->hasMany(User::class, 'referred_by_reseller_id');
    }
}
