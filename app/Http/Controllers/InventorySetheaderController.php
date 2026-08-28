<?php

namespace App\Http\Controllers;

use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventorySetheaderController extends Controller
{
    /**
     * Modul "Header Struk" (module=setheader). Selalu satu baris data saja,
     * sama seperti public/apotekberlian/masuk/modul/mod_setheader/setheader.php.
     */
    public function index()
    {
        $setheader = Setheader::first() ?? Setheader::create([
            'satu' => '', 'dua' => '', 'tiga' => '', 'empat' => '', 'lima' => '', 'enam' => '',
            'tujuh' => '', 'delapan' => '', 'sembilan' => '', 'sepuluh' => '', 'sebelas' => '',
            'duabelas' => '', 'tigabelas' => '', 'empatbelas' => 0, 'logo' => '', 'tandatangan' => '',
        ]);

        return view('inventory.setheader.index', [
            'judul' => 'Inventory',
            'setheader' => $setheader,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'satu' => 'required|string|max:111',
            'dua' => 'required|string|max:111',
            'tiga' => 'required|string|max:111',
            'empat' => 'required|string|max:111',
            'lima' => 'required|string|max:111',
            'enam' => 'required|string|max:111',
            'tujuh' => 'nullable|string|max:111',
            'duabelas' => 'required|string|max:100',
            'tigabelas' => 'required|string|max:100',
            'empatbelas' => 'required|integer|min:0',
            'delapan' => 'required|string|max:100',
            'sembilan' => 'required|string|max:100',
            'sepuluh' => 'required|string|max:100',
            'sebelas' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:3048',
            'tandatangan' => 'nullable|image|max:3048',
        ]);

        // Kolom 'sebelas' NOT NULL di database, tapi middleware Laravel mengubah
        // input string kosong menjadi null sebelum validasi; kembalikan ke '' di sini.
        $validated['sebelas'] = $validated['sebelas'] ?? '';

        $setheader = Setheader::firstOrFail();

        if ($request->hasFile('logo')) {
            if ($setheader->logo && Storage::disk('public')->exists($setheader->logo)) {
                Storage::disk('public')->delete($setheader->logo);
            }
            $validated['logo'] = $request->file('logo')->store('setheader', 'public');
        }

        if ($request->hasFile('tandatangan')) {
            if ($setheader->tandatangan && Storage::disk('public')->exists($setheader->tandatangan)) {
                Storage::disk('public')->delete($setheader->tandatangan);
            }
            $validated['tandatangan'] = $request->file('tandatangan')->store('setheader', 'public');
        }

        $setheader->update($validated);

        return redirect()->route('inventory.setheader.index')->with('success', 'Header struk berhasil diperbarui.');
    }
}
