<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QrisPayment extends Model
{
    protected $fillable = [
        'source',
        'reference_no',
        'hub_reference_no',
        'order_id',
        'kd_trkasir',
        'amount',
        'qr_content',
        'status',
        'paid_at',
        'expires_at',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isPast();
    }

    /**
     * Idempoten -- poll (OrderController::statusGdcQris()/Kasir) & webhook hub
     * (HubQrisWebhookController) bisa balapan memanggil ini untuk reference yang sama.
     */
    public function markAsPaid(): void
    {
        if ($this->status === 'paid') {
            return;
        }

        DB::transaction(function () {
            $this->refresh();

            if ($this->status === 'paid') {
                return;
            }

            if ($this->source === 'marketplace' && $this->order_id) {
                $order = $this->order;

                if ($order && $order->status !== 'Paid') {
                    $order->status = 'Paid';
                    $order->petugas_approval = 'Sistem (QRIS GDC Otomatis)';
                    $order->waktu_approval = now();
                    $order->save();
                }
            }

            $this->update(['status' => 'paid', 'paid_at' => now()]);
        });
    }

    /**
     * Stok yang dikurangi di muka saat QR dibuat (lihat
     * OrderController::createGdcQris()) dikembalikan di sini -- kebalikan dari
     * markAsPaid(), dan idempoten dengan alasan yang sama.
     */
    public function markAsFailed(string $status = 'failed'): void
    {
        if ($this->status !== 'pending') {
            return;
        }

        DB::transaction(function () use ($status) {
            $this->refresh();

            if ($this->status !== 'pending') {
                return;
            }

            if ($this->source === 'marketplace' && $this->order_id) {
                $order = $this->order;

                if ($order) {
                    $order->load('orderItems.produk');
                    foreach ($order->orderItems as $item) {
                        $produk = $item->produk;
                        if ($produk) {
                            $produk->stok_barang += $item->quantity;
                            $produk->save();
                        }
                    }
                }
            }

            $this->update(['status' => $status]);
        });
    }
}
