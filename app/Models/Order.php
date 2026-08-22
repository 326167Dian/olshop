<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = true;
    protected $table = "order_online";
    protected $fillable = [
        'user_id',
        'total_harga',
        'status',
        'tipe_layanan',
        'layanan_pengiriman',
        'tipe_pembayaran',
        'total_berat',
        'alamat',
        'midtrans_order_id'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class); // sesuaikan jika nama foreign key ber
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function mapMidtransStatus($transactionStatus, $paymentType = null, $fraudStatus = null)
    {
        switch ($transactionStatus) {
            case 'capture':
                if ($paymentType == 'credit_card') {
                    return $fraudStatus == 'challenge' ? 'Challenge' : 'Paid';
                }
                return 'Paid';

            case 'settlement':
                return 'Paid';

            case 'pending':
                return 'Pending';

            case 'deny':
            case 'cancel':
            case 'expire':
                return 'Failed';

            default:
                return 'Unknown';
        }
    }
}
