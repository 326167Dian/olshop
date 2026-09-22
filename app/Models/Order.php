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
        'promo_id',
        'nama_promo',
        'nilai_diskon_promo',
        'total_diskon',
        'status',
        'tipe_layanan',
        'lokasi_antar_id',
        'jarak_km',
        'biaya_ongkir',
        'layanan_pengiriman',
        'estimasi_antar',
        'tipe_pembayaran',
        'total_berat',
        'alamat',
        'tipe_alamat',
        'alamat_label',
        'alamat_lat',
        'alamat_lng',
        'no_tlp',
        'midtrans_order_id',
        'bukti_pembayaran',
        'petugas_approval',
        'waktu_approval',
        'image',
        'catatan',
    ];

    protected $casts = [
        'waktu_approval' => 'datetime',
        'estimasi_antar' => 'datetime',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class); // sesuaikan jika nama foreign key ber
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    /**
     * Tier tarif jarak (bukan lagi kelurahan) yang cocok untuk order ini --
     * lihat LokasiAntar::tierUntukJarak().
     */
    public function lokasiAntar()
    {
        return $this->belongsTo(LokasiAntar::class, 'lokasi_antar_id');
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
