<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $query = User::select([
            'id',
            'name',
            'email',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="dropdown position-relative d-inline-block">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    Action
                </button>
                <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                    <a href="' . route('customer.show', $row->id) . '" class="btn btn-info btn-sm w-100 mb-1">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                </div>
            </div>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(User $customer)
    {
        return view('backend.customer.show', compact('customer'));
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
            'edit' => $customer
        ]);
    }

    public function updateAkun(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'alamat' => 'nullable|max:255',
            'no_tlp' => 'nullable|max:255' // tambah validasi password
        ];

        $validatedData = $request->validate($rules);

        // Hanya update password jika diisi
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // jangan update password kalau kosong
        }

        $user->update($validatedData);

        return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil diperbarui');
    }
}
