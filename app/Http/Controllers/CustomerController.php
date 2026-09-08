<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;


class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::get();
        return view('backend.customer.index', [
            'judul' => 'Customer',
            'subJudul' => 'Data Customer',
            'customers' => $customers
        ]);
    }

    public function data(Request $request)
    {
        $isPemilik = optional(Auth::guard('admin')->user())->isPemilik() ?? false;

        $query = User::query()
            ->leftJoin('admin', 'admin.id_admin', '=', 'users.referal_admin_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.referal_admin_id',
                'users.komisi_status',
                'users.waktu_komisi_lunas',
                'admin.nama_lengkap as referal_nama',
            ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('referal_nama', fn ($row) => $row->referal_nama ?? '-')
            ->addColumn('komisi', function ($row) use ($isPemilik) {
                if (!$row->referal_admin_id) {
                    return '-';
                }

                if (!$isPemilik) {
                    return $row->komisi_status === 'lunas'
                        ? '<span class="badge bg-success">Lunas</span>'
                        : '<span class="badge bg-warning text-dark">Belum</span>';
                }

                $belumSelected = $row->komisi_status === 'belum' ? 'selected' : '';
                $lunasSelected = $row->komisi_status === 'lunas' ? 'selected' : '';

                return '<select class="form-select form-select-sm komisi-select" data-id="' . $row->id . '">
                        <option value="belum" ' . $belumSelected . '>Belum</option>
                        <option value="lunas" ' . $lunasSelected . '>Lunas</option>
                    </select>';
            })
            ->addColumn('waktu_komisi', function ($row) {
                $text = ($row->referal_admin_id && $row->komisi_status === 'lunas' && $row->waktu_komisi_lunas)
                    ? \Illuminate\Support\Carbon::parse($row->waktu_komisi_lunas)->format('d-m-Y H:i')
                    : '-';

                return '<span class="waktu-komisi-display" data-id="' . $row->id . '">' . $text . '</span>';
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="dropdown position-relative d-inline-block">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Action
                </button>
                <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                    <a href="' . route('customer.show', $row->id) . '" class="btn btn-info btn-sm w-100 mb-1">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                    <button onclick="deleteData(\'' . route('customer.destroy', $row->id) . '\', this)" class="btn btn-danger btn-sm w-100" data-konf-delete="' . e($row->name) . '">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </div>
            </div>';
                return $btn;
            })
            ->rawColumns(['komisi', 'waktu_komisi', 'aksi'])
            ->make(true);
    }

    public function show(User $customer)
    {
        return view('backend.customer.show', compact('customer'));
    }

    public function destroy(User $customer)
    {
        if (Order::where('user_id', $customer->id)->exists()) {
            return response()->json([
                'message' => 'Customer tidak dapat dihapus karena masih memiliki riwayat pesanan.'
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer berhasil dihapus.'
        ]);
    }

    /**
     * Ubah status komisi (belum/lunas) untuk petugas yang merekrut customer ini
     * (users.referal_admin_id) -- AJAX, dipanggil langsung saat <select> berubah
     * (lihat backend/customer/index.blade.php), bukan lewat reload halaman.
     * Hanya pemilik yang boleh mengubah (dicek server-side di sini, bukan cuma
     * disembunyikan di tampilan) -- waktu_komisi_lunas dicatat otomatis saat
     * berpindah ke 'lunas', dikosongkan lagi kalau dikembalikan ke 'belum'.
     */
    public function updateKomisi(Request $request, User $customer)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->isPemilik(), 403, 'Hanya pemilik yang dapat mengubah status komisi.');

        abort_if(!$customer->referal_admin_id, 422, 'Customer ini tidak memiliki referal petugas.');

        $validated = $request->validate([
            'komisi_status' => 'required|in:belum,lunas',
        ]);

        $customer->komisi_status = $validated['komisi_status'];
        $customer->waktu_komisi_lunas = $validated['komisi_status'] === 'lunas' ? now() : null;
        $customer->save();

        return response()->json([
            'message' => 'Status komisi berhasil diperbarui.',
            'waktu_komisi_lunas' => optional($customer->waktu_komisi_lunas)->format('d-m-Y H:i'),
        ]);
    }

    public function akun($id)
    {
        $loggedInCustomerId = Auth::user()->id;
        // Cek apakah ID yang diberikan sama dengan ID customer yang sedang login
        if ($id != $loggedInCustomerId) {
            // Redirect atau tampilkan pesan error
            return redirect()->route('customer.akun', ['id' => $loggedInCustomerId])->with('msgError', 'Anda tidak berhak mengakses akun ini.');
        }
        $customer = User::where('id', $id)->firstOrFail();
        return view('frontend.v_customer.edit', [
            'judul' => 'Customer',
            'subJudul' => 'Akun Customer',
            'edit' => $customer,
            'petugasList' => Admin::where('akses_level', 'petugas')->orderBy('nama_lengkap')->get(['id_admin', 'nama_lengkap']),
        ]);
    }

    public function updateAkun(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'alamat' => 'nullable|max:255',
            'no_tlp' => 'nullable|max:255',
            'referal_admin_id' => [
                'nullable',
                'integer',
                Rule::exists('admin', 'id_admin')->where('akses_level', 'petugas'),
            ],
        ];

        $validatedData = $request->validate($rules);

        $user->update($validatedData);

        return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil diperbarui');
    }
}
