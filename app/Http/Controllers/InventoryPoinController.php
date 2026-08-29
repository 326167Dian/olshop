<?php

namespace App\Http\Controllers;

use App\Models\PoinPelanggan;
use App\Models\Setheader;
use Illuminate\Http\Request;

class InventoryPoinController extends Controller
{
    /**
     * Modul "Poin Member" (module=poin), mengikuti modal #ModalPoin di
     * public/apotekberlian/masuk/modul/mod_pelanggan/pelanggan.php + act=input_poin
     * di aksi_pelanggan.php. Selalu satu baris data saja (seperti Setheader).
     * Tidak digerbang flag admin manapun di legacy -- semua admin yang bisa buka
     * halaman Pelanggan juga bisa buka modal ini.
     */
    public function index()
    {
        $poin = PoinPelanggan::first();

        return view('inventory.poin.index', [
            'judul' => 'Inventory',
            'poin' => $poin,
            'namaOutletDefault' => Setheader::first()->satu ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nm_outlet' => 'required|string|max:100',
            'min_penjualan' => 'required|integer|min:0',
            'poin_member' => 'required|integer|min:0',
        ]);

        $poin = PoinPelanggan::first();

        $data = [
            'nm_outlet' => $validated['nm_outlet'],
            'is_outlet' => $request->boolean('is_outlet') ? 'ya' : 'no',
            'min_penjualan' => $validated['min_penjualan'],
            'is_kelipatan' => $request->boolean('is_kelipatan') ? 'ya' : 'no',
            'poin_pelanggan' => $validated['poin_member'],
            'is_active' => 'ya',
        ];

        if ($poin) {
            $poin->update($data);
        } else {
            PoinPelanggan::create($data);
        }

        return redirect()->route('inventory.poin.index')->with('success', 'Ketentuan poin berhasil disimpan.');
    }
}
