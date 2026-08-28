<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    public $timestamps = false;

    protected $fillable = [
        'nm_supplier',
        'tlp_supplier',
        'alamat_supplier',
        'ket_supplier',
    ];

    public function barang()
    {
        return $this->hasMany(BarangSupplier::class, 'id_supplier')->orderBy('id_barang');
    }
}
