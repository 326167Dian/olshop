<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien API Biteship untuk Kurir Instan (GoSend / GrabExpress).
 *
 * Alur:
 *  1. Checkout  -> rates()        : pelanggan melihat & memilih tarif live (berdasar koordinat).
 *  2. updateOngkir -> findRate()  : tarif DICEK ULANG di server (tidak percaya harga dari browser).
 *  3. Admin "Pesan Kurir" -> createOrder() : driver dipanggil ke apotek.
 *  4. Webhook / tombol refresh -> getOrder() : status & data driver diperbarui.
 *
 * Titik jemput = koordinat apotek di Setting Perusahaan (kehadiran_lat/lng), sama
 * dengan titik acuan radius pengantaran (lihat DeliveryPricingService).
 */
class BiteshipService
{
    public function isConfigured(): bool
    {
        return filled(config('biteship.api_key'));
    }

    public function isSandbox(): bool
    {
        return str_starts_with((string) config('biteship.api_key'), 'biteship_test');
    }

    /**
     * Tarif kurir instan dari apotek ke titik tujuan.
     *
     * @return array<int, array{key:string, company:string, type:string, name:string,
     *     service:string, label:string, price:float, duration:string|null}>
     */
    public function rates(float $lat, float $lng, Order $order): array
    {
        $origin = $this->origin();

        $response = $this->client()->post('/v1/rates/couriers', [
            'origin_latitude' => $origin['lat'],
            'origin_longitude' => $origin['lng'],
            'destination_latitude' => $lat,
            'destination_longitude' => $lng,
            'couriers' => config('biteship.couriers'),
            'items' => $this->items($order),
        ]);

        if ($response->failed()) {
            // 40001010 = tidak ada kurir untuk lokasi ini -> bukan error sistem.
            if ((int) $response->json('code') === 40001010) {
                return [];
            }

            throw new RuntimeException('Gagal mengambil tarif Biteship: ' . $response->status() . ' ' . $response->body());
        }

        $allowedTypes = config('biteship.service_types', []);

        return collect($response->json('pricing', []))
            ->filter(fn ($p) => empty($allowedTypes) || in_array($p['courier_service_code'] ?? '', $allowedTypes, true))
            ->map(function ($p) {
                $company = (string) ($p['courier_code'] ?? $p['company'] ?? '');
                $type = (string) ($p['courier_service_code'] ?? $p['type'] ?? '');
                $name = (string) ($p['courier_name'] ?? $company);
                $service = (string) ($p['courier_service_name'] ?? $type);

                return [
                    'key' => $company . '|' . $type,
                    'company' => $company,
                    'type' => $type,
                    'name' => $name,
                    'service' => $service,
                    'label' => trim($name . ' ' . $service),
                    'price' => (float) ($p['price'] ?? 0),
                    'duration' => $p['duration'] ?? $p['shipment_duration_range'] ?? null,
                ];
            })
            ->filter(fn ($r) => $r['company'] !== '' && $r['type'] !== '' && $r['price'] > 0)
            ->sortBy('price')
            ->values()
            ->all();
    }

    /**
     * Ambil ulang tarif untuk layanan yang dipilih pelanggan (company|type).
     */
    public function findRate(float $lat, float $lng, Order $order, string $key): ?array
    {
        foreach ($this->rates($lat, $lng, $order) as $rate) {
            if ($rate['key'] === $key) {
                return $rate;
            }
        }

        return null;
    }

    /**
     * Pesan driver untuk order yang sudah dibayar/diproses.
     */
    public function createOrder(Order $order): array
    {
        if (!$order->kurir_company || !$order->kurir_type) {
            throw new RuntimeException('Pesanan ini tidak memakai Kurir Instan.');
        }

        if ($order->alamat_lat === null || $order->alamat_lng === null) {
            throw new RuntimeException('Koordinat alamat pelanggan kosong.');
        }

        $origin = $this->origin();
        $setting = $this->setting();
        $order->loadMissing('user', 'orderItems.produk');

        $response = $this->client()->post('/v1/orders', [
            'shipper_contact_name' => $setting->nama_perusahaan ?: 'Apotek',
            'shipper_contact_phone' => $this->phone($setting->telepon),
            'shipper_organization' => $setting->nama_perusahaan ?: 'Apotek',
            'origin_contact_name' => $setting->nama_perusahaan ?: 'Apotek',
            'origin_contact_phone' => $this->phone($setting->telepon),
            'origin_address' => $setting->alamat ?: '-',
            'origin_note' => 'Ambil paket di kasir apotek. No. pesanan ' . $order->kode_pesanan,
            'origin_coordinate' => [
                'latitude' => $origin['lat'],
                'longitude' => $origin['lng'],
            ],
            'destination_contact_name' => $order->user->name ?? 'Pelanggan',
            'destination_contact_phone' => $this->phone($order->no_tlp ?: ($order->user->no_tlp ?? '')),
            'destination_contact_email' => $order->user->email ?? null,
            'destination_address' => $order->alamat ?: '-',
            'destination_note' => $order->alamat_label ?: '',
            'destination_coordinate' => [
                'latitude' => (float) $order->alamat_lat,
                'longitude' => (float) $order->alamat_lng,
            ],
            'courier_company' => $order->kurir_company,
            'courier_type' => $order->kurir_type,
            'delivery_type' => 'now',
            'order_note' => 'Pesanan apotek ' . $order->kode_pesanan . '. Mohon hati-hati, berisi obat.',
            'reference_id' => (string) ($order->kode_pesanan ?: $order->id),
            'items' => $this->items($order),
        ]);

        if ($response->failed() || $response->json('success') === false) {
            throw new RuntimeException('Gagal memesan kurir: ' . ($response->json('error') ?: $response->body()));
        }

        return $response->json();
    }

    public function getOrder(string $biteshipOrderId): array
    {
        $response = $this->client()->get('/v1/orders/' . $biteshipOrderId);

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengambil status kurir: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json();
    }

    public function cancelOrder(string $biteshipOrderId, string $reason): array
    {
        $response = $this->client()->post('/v1/orders/' . $biteshipOrderId . '/cancel', [
            'cancellation_reason' => $reason,
        ]);

        if ($response->failed() || $response->json('success') === false) {
            throw new RuntimeException('Gagal membatalkan kurir: ' . ($response->json('error') ?: $response->body()));
        }

        return $response->json();
    }

    /**
     * Salin data dari response Order API / payload webhook ke kolom order.
     * Format keduanya beda: Order API memakai objek "courier", webhook memakai
     * field datar "courier_*".
     */
    public function applyToOrder(Order $order, array $data): void
    {
        $courier = $data['courier'] ?? [];

        $order->biteship_order_id = $data['id'] ?? $data['order_id'] ?? $order->biteship_order_id;
        $order->biteship_status = $data['status'] ?? $order->biteship_status;
        $order->biteship_price = $data['price'] ?? $data['order_price'] ?? $order->biteship_price;
        $order->kurir_tracking_id = $courier['tracking_id'] ?? $data['courier_tracking_id'] ?? $order->kurir_tracking_id;
        $order->kurir_waybill_id = $courier['waybill_id'] ?? $data['courier_waybill_id'] ?? $order->kurir_waybill_id;
        $order->kurir_driver_name = $courier['name'] ?? $courier['driver_name'] ?? $data['courier_driver_name'] ?? $order->kurir_driver_name;
        $order->kurir_driver_phone = $courier['phone'] ?? $courier['driver_phone'] ?? $data['courier_driver_phone'] ?? $order->kurir_driver_phone;
        $order->kurir_driver_plate = $courier['driver_plate_number'] ?? $data['courier_driver_plate_number'] ?? $order->kurir_driver_plate;
        $order->kurir_link = $courier['link'] ?? $data['courier_link'] ?? $order->kurir_link;
    }

    /**
     * Status Biteship yang artinya driver sudah tidak aktif untuk order ini --
     * admin boleh memesan kurir baru.
     */
    public static function isFinalFailed(?string $status): bool
    {
        return in_array($status, ['cancelled', 'rejected', 'courier_not_found', 'returned', 'disposed'], true);
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            null, '' => '-',
            'confirmed' => 'Mencari driver',
            'allocated' => 'Driver ditemukan',
            'picking_up' => 'Driver menuju apotek',
            'picked' => 'Paket sudah diambil',
            'dropping_off' => 'Dalam perjalanan ke pelanggan',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak',
            'courier_not_found' => 'Driver tidak ditemukan',
            'on_hold' => 'Ditahan',
            'return_in_transit' => 'Dikembalikan ke apotek',
            'returned' => 'Kembali ke apotek',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * @return array{lat: float, lng: float}
     */
    private function origin(): array
    {
        $setting = $this->setting();

        if ($setting->kehadiran_lat === null || $setting->kehadiran_lng === null) {
            throw new RuntimeException('Koordinat apotek belum diatur di Setting Perusahaan.');
        }

        return ['lat' => (float) $setting->kehadiran_lat, 'lng' => (float) $setting->kehadiran_lng];
    }

    private function items(Order $order): array
    {
        $order->loadMissing('orderItems.produk');
        $weight = max(1, (int) config('biteship.default_item_weight', 100));

        $items = $order->orderItems->map(fn ($item) => [
            'name' => mb_substr($item->produk->nm_barang ?? 'Obat', 0, 100),
            'value' => (int) round($item->harga),
            'quantity' => (int) max(1, $item->quantity),
            'weight' => $weight,
        ])->values()->all();

        return $items ?: [['name' => 'Paket apotek', 'value' => 0, 'quantity' => 1, 'weight' => $weight]];
    }

    private function phone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    private function setting(): CompanySetting
    {
        return CompanySetting::first() ?? new CompanySetting();
    }

    private function client()
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('BITESHIP_API_KEY belum diisi di .env.');
        }

        return Http::baseUrl(rtrim(config('biteship.base_url'), '/'))
            ->withHeaders(['Authorization' => config('biteship.api_key')])
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('biteship.timeout', 20));
    }
}
