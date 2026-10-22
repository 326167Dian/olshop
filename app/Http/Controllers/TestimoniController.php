<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Testimoni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TestimoniController extends Controller
{
    /**
     * Daftar testimoni publik (storefront, bisa diakses tanpa login).
     */
    public function indexFrontend()
    {
        $testimonis = Testimoni::with('user')->latest()->paginate(9);

        return view('frontend.v_testimoni.index', compact('testimonis'));
    }

    /**
     * Form tulis/edit testimoni milik pelanggan yang sedang login.
     * Hanya pelanggan yang pernah punya pesanan berstatus "Selesai" yang boleh menulis.
     */
    public function form()
    {
        $eligible = Order::where('user_id', Auth::id())->where('status', 'Selesai')->exists();
        $testimoni = Testimoni::where('user_id', Auth::id())->first();

        return view('frontend.v_testimoni.form', compact('eligible', 'testimoni'));
    }

    /**
     * Simpan/perbarui testimoni milik pelanggan (satu testimoni per pelanggan).
     */
    public function store(Request $request)
    {
        $eligible = Order::where('user_id', Auth::id())->where('status', 'Selesai')->exists();

        if (!$eligible) {
            return back()->with('error', 'Testimoni hanya bisa ditulis oleh pelanggan yang pernah berbelanja.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'isi' => 'required|string|max:1000',
        ]);

        Testimoni::updateOrCreate(
            ['user_id' => Auth::id()],
            ['rating' => $validated['rating'], 'isi' => $validated['isi']]
        );

        return redirect()->route('testimoni.all')->with('success', 'Terima kasih, testimoni Anda berhasil disimpan.');
    }

    /**
     * Daftar testimoni untuk admin (dengan aksi edit/hapus).
     */
    public function adminIndex()
    {
        return view('backend.testimoni.index');
    }

    public function data(Request $request)
    {
        $query = Testimoni::with('user')->select(['id', 'user_id', 'rating', 'isi', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama', function ($row) {
                return e($row->user->name ?? '-');
            })
            ->editColumn('rating', function ($row) {
                return str_repeat('★', $row->rating) . str_repeat('☆', 5 - $row->rating);
            })
            ->editColumn('isi', function ($row) {
                return e(\Illuminate\Support\Str::limit($row->isi, 100));
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d-m-Y H:i');
            })
            ->addColumn('aksi', function ($row) {
                return '<div class="d-flex flex-wrap gap-1">
                    <a href="' . route('backend.testimoni.edit', $row->id) . '" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button onclick="deleteData(\'' . route('backend.testimoni.destroy', $row->id) . '\', this)" class="btn btn-danger btn-sm" data-konf-delete="testimoni dari ' . e($row->user->name ?? '-') . '">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function edit(Testimoni $testimoni)
    {
        return view('backend.testimoni.edit', compact('testimoni'));
    }

    public function update(Request $request, Testimoni $testimoni)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'isi' => 'required|string|max:1000',
        ]);

        $testimoni->update(['rating' => $validated['rating'], 'isi' => $validated['isi']]);

        return redirect()->route('backend.testimoni.index')->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimoni $testimoni)
    {
        $testimoni->delete();

        return redirect()->route('backend.testimoni.index')->with('success', 'Testimoni berhasil dihapus.');
    }
}
