<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Jika pakai tabel 'users', baris ini bisa dihapus
    // protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'alamat',
        'no_tlp',
        'referal_admin_id',
        'komisi_status',
        'waktu_komisi_lunas',
        'referred_by_reseller_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'waktu_komisi_lunas' => 'datetime',
    ];

    public function referal()
    {
        return $this->belongsTo(Admin::class, 'referal_admin_id', 'id_admin');
    }

    public function reseller()
    {
        return $this->hasOne(Reseller::class);
    }

    /**
     * Reseller yang mengajak user ini belanja (lewat link referral), bukan profil
     * reseller milik user ini sendiri -- lihat reseller() di atas untuk itu.
     */
    public function referredByReseller()
    {
        return $this->belongsTo(Reseller::class, 'referred_by_reseller_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
