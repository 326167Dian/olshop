<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InventoryAdminController extends Controller
{
    /**
     * Modul "Operator" (module=admin pada aplikasi legacy public/apotekberlian).
     * Hanya admin level 'pemilik' dengan akses mpengguna='Y' yang boleh mengelola
     * operator lain, sama seperti public/apotekberlian/masuk/modul/mod_admin/admin.php.
     */
    public function index()
    {
        $this->authorizePemilik();

        $admins = Admin::where('id_admin', '!=', 2)
            ->orderBy('username')
            ->get();

        return view('inventory.admin.index', [
            'judul' => 'Inventory',
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        $this->authorizePemilik();

        return view('inventory.admin.create', [
            'judul' => 'Inventory',
            'groups' => Admin::PERMISSION_GROUPS,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePemilik();

        $validated = $request->validate([
            'username' => ['required', 'regex:/^[a-zA-Z0-9]+$/', 'max:100', 'unique:admin,username'],
            'password' => ['required', 'regex:/^[a-zA-Z0-9]+$/'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'no_telp' => ['required', 'string', 'max:30'],
            'akses_level' => ['required', 'in:pemilik,petugas'],
            'blokir' => ['required', 'in:Y,N'],
        ], [
            'username.regex' => 'Username hanya boleh huruf dan angka, tanpa spasi.',
            'password.regex' => 'Password hanya boleh huruf dan angka, tanpa spasi.',
        ]);

        $data = $validated;
        $data['password'] = Hash::make($validated['password']);
        $data['unit'] = 1;
        // koreksistok tidak lagi dipakai (menu-nya sudah dinonaktifkan di aplikasi legacy)
        // tapi kolomnya di tabel admin NOT NULL tanpa default, jadi tetap perlu diisi saat insert.
        $data['koreksistok'] = 'N';
        $data = array_merge($data, $this->permissionFlagsFromRequest($request));

        Admin::create($data);

        return redirect()->route('inventory.admin.index')->with('success', 'Operator berhasil ditambahkan.');
    }

    public function edit(Admin $admin)
    {
        $this->authorizePemilik();

        return view('inventory.admin.edit', [
            'judul' => 'Inventory',
            'admin' => $admin,
            'groups' => Admin::PERMISSION_GROUPS,
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $this->authorizePemilik();

        $rules = [
            'username' => ['required', 'regex:/^[a-zA-Z0-9]+$/', 'max:100', Rule::unique('admin', 'username')->ignore($admin->id_admin, 'id_admin')],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'no_telp' => ['required', 'string', 'max:30'],
            'akses_level' => ['required', 'in:pemilik,petugas'],
            'blokir' => ['required', 'in:Y,N'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['regex:/^[a-zA-Z0-9]+$/'];
        }

        $validated = $request->validate($rules, [
            'username.regex' => 'Username hanya boleh huruf dan angka, tanpa spasi.',
            'password.regex' => 'Password hanya boleh huruf dan angka, tanpa spasi.',
        ]);

        $data = $validated;
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }
        $data = array_merge($data, $this->permissionFlagsFromRequest($request));

        $admin->update($data);

        return redirect()->route('inventory.admin.index')->with('success', 'Operator berhasil diperbarui.');
    }

    public function destroy(Admin $admin)
    {
        $this->authorizePemilik();

        if ($admin->id_admin === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sedang digunakan.');
        }

        $admin->delete();

        return redirect()->route('inventory.admin.index')->with('success', 'Operator berhasil dihapus.');
    }

    public function loginLogs()
    {
        $this->authorizePemilik();

        $logs = DB::table('user_login_logs')->orderByDesc('login_time')->get();

        return view('inventory.admin.login-logs', [
            'judul' => 'Inventory',
            'logs' => $logs,
        ]);
    }

    private function authorizePemilik(): void
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && $admin->isPemilik() && $admin->hasModuleAccess('mpengguna'), 403, 'Anda tidak berhak mengakses halaman ini.');
    }

    private function permissionFlagsFromRequest(Request $request): array
    {
        $flags = [];

        foreach (Admin::PERMISSION_GROUPS as $group) {
            foreach ($group as $column => $label) {
                $flags[$column] = $request->has($column) ? 'Y' : 'N';
            }
        }

        return $flags;
    }
}
