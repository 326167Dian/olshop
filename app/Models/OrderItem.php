<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = true;
    protected $table = "order_online_item";
    protected $fillable = ['order_id', 'produk_id', 'quantity', 'harga'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id', 'id_barang');
    }

    public function kategori()
    {
        return $this->belongsTo(Category::class);
    }
}
