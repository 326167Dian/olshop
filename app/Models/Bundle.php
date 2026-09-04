<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $table = 'bundle';
    protected $primaryKey = 'id_bundle';
    public $timestamps = false;

    protected $fillable = [
        'kd_bundle',
        'nm_bundle',
        'sat_bundle',
        'qty_bundle',
        'hrgjual_bundle',
        'petugas',
        'created_at',
        'update_at',
    ];

    public function detail()
    {
        return $this->hasMany(BundleDetail::class, 'kd_bundle', 'kd_bundle');
    }
}
