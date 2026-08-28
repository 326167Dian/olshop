<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangSupplier extends Model
{
    protected $table = 'barang_supplier';
    protected $primaryKey = 'id_brgsup';
    public $timestamps = false;

    protected $fillable = [
        'id_supplier',
        'id_barang',
        'hrgsat_brgsupplier',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function barang()
    {
        return $this->belongsTo(Product::class, 'id_barang', 'id_barang');
    }
}
