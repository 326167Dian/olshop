<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;

/**
 * Setting > Input Pixel: admin apotek cukup menempelkan Pixel ID (atau seluruh
 * kode Pixel dari Meta Events Manager) -- ID-nya diambil otomatis.
 */
class MetaPixelSettingController extends Controller
{
    public function index()
    {
        $setting = CompanySetting::first();

        return view('backend.setting.meta-pixel', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = CompanySetting::first();
        if (!$setting) {
            return redirect()->route('company-setting.index')
                ->with('error', 'Isi Profil Perusahaan terlebih dahulu sebelum memasang Pixel.');
        }

        $request->validate([
            'meta_pixel' => 'nullable|string|max:5000',
        ]);

        $input = trim((string) $request->input('meta_pixel'));
        $pixelId = null;

        if ($input !== '') {
            $pixelId = self::extractPixelId($input);
            if ($pixelId === null) {
                return back()->withInput()->withErrors([
                    'meta_pixel' => 'Pixel ID tidak dikenali. Masukkan angka Pixel ID (15–16 digit) atau tempel seluruh kode Pixel dari Meta Events Manager.',
                ]);
            }
        }

        $setting->meta_pixel_id = $pixelId;
        $setting->meta_pixel_aktif = $request->boolean('meta_pixel_aktif');
        $setting->save();

        return redirect()->route('meta-pixel.index')->with(
            'success',
            $pixelId ? 'Meta Pixel berhasil disimpan.' : 'Meta Pixel dihapus.'
        );
    }

    /**
     * Terima Pixel ID polos ("1234567890123456") atau seluruh base code Pixel
     * (ambil dari fbq('init', '...') atau ?id=... pada tag noscript).
     */
    public static function extractPixelId(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('/^\d{10,20}$/', $input)) {
            return $input;
        }
        if (preg_match('/fbq\(\s*[\'"]init[\'"]\s*,\s*[\'"](\d{10,20})[\'"]/', $input, $m)) {
            return $m[1];
        }
        if (preg_match('/facebook\.com\/tr\?id=(\d{10,20})/', $input, $m)) {
            return $m[1];
        }

        return null;
    }
}
