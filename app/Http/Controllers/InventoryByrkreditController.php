<?php

namespace App\Http\Controllers;

use App\Models\Trbmasuk;
use Illuminate\Support\Facades\Auth;

class InventoryByrkreditController extends Controller
{
    /**
     * Modul "Edit/Retur/Hapus Pembelian" (module=byrkredit), mengikuti
     * public/apotekberlian/masuk/modul/mod_trbmasuk/byrkredit.php.
     *
     * Bukan modul transaksi baru -- ini satu-satunya jalan masuk yang legacy sediakan
     * untuk MENGEDIT transaksi Barang Masuk non-PBF yang sudah tersimpan (tambah/ubah/hapus
     * baris item, ubah data header). Modul trbmasuk sendiri (module=trbmasuk) sengaja
     * hanya menyediakan review baca-saja (lihat InventoryTrbmasukController::show()) --
     * tombol edit di sana mati/dikomentari di legacy. "Retur" bukan fitur terpisah:
     * dilakukan lewat form edit yang sama (kurangi qty atau hapus baris item untuk
     * merefleksikan barang yang dikembalikan ke supplier).
     *
     * Daftar di sini menampilkan SEMUA transaksi trbmasuk (non-PBF maupun PBF sekaligus,
     * persis seperti legacy), tapi tautan Edit diarahkan sesuai `jenis` masing-masing baris
     * -- non-PBF ke edit() di InventoryTrbmasukController (lewat route byrkredit.*, digerbang
     * flag 'byrkredit'), PBF ke trbmasukpbf.edit yang SUDAH ADA (digerbang flag 'tbmpbf' +
     * pengecekan pemilik di dalam method-nya sendiri). Ini perbaikan dari legacy: legacy
     * menampilkan DUA tautan ("EDIT" dan "EDIT PBF") di setiap baris tanpa peduli jenis
     * transaksinya, dan tautan "EDIT" sendiri memilih modul lewat heuristik yang rapuh
     * (`jatuhtempo == ""`) alih-alih membaca kolom `jenis` langsung.
     *
     * Item add/remove/qty-edit untuk baris non-PBF memakai ULANG method yang sama persis
     * dengan modul trbmasuk (detailStore/detailUpdateQty/detailDestroy di
     * InventoryTrbmasukController) lewat route alias baru yang digerbang 'byrkredit' --
     * tidak menduplikasi logikanya, karena tabel & aturan bisnisnya sama persis, hanya
     * gerbang izinnya yang beda (staf ber-flag 'byrkredit' belum tentu ber-flag 'tbm').
     */
    public function index()
    {
        return view('inventory.byrkredit.index', ['judul' => 'Inventory']);
    }

    public function data()
    {
        $query = Trbmasuk::query()
            ->where('id_resto', 'pusat')
            ->select(['id_trbmasuk', 'kd_trbmasuk', 'jenis', 'petugas', 'tgl_trbmasuk', 'nm_supplier', 'ket_trbmasuk', 'sisa_bayar', 'carabayar']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->editColumn('sisa_bayar', fn ($row) => number_format($row->sisa_bayar, 0, ',', '.'))
            ->editColumn('jenis', fn ($row) => $row->jenis === 'pbf' ? 'PBF' : 'Non-PBF')
            ->addColumn('aksi', function ($row) {
                $editUrl = $row->jenis === 'pbf'
                    ? route('inventory.trbmasukpbf.edit', $row->id_trbmasuk)
                    : route('inventory.byrkredit.edit', $row->id_trbmasuk);

                $btn = '<a href="' . $editUrl . '" class="btn btn-warning btn-xs">Edit</a>';
                $btn .= ' <button type="button" class="btn btn-danger btn-xs btn-hapus-transaksi" data-id="' . $row->id_trbmasuk . '" data-jenis="' . $row->jenis . '">Hapus</button>';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
}
