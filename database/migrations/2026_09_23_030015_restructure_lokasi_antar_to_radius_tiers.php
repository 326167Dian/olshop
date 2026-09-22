<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti skema tarif kelurahan (nama_kelurahan) jadi tingkatan jarak (jarak_min/jarak_max
     * dalam km) -- tarif pengantaran sekarang berdasar radius dari apotek, bukan nama wilayah.
     * Baris lama sudah sempat diisi manual sebagai workaround ("Radius sd 2 km", "Radius 2 sd
     * 5 km") -- di-parse best-effort di sini; kalau gagal parse dibiarkan null, ditandai di
     * halaman admin (lihat LokasiAntarController/index.blade.php) supaya gampang dilengkapi
     * manual (cuma ada beberapa baris).
     */
    public function up(): void
    {
        Schema::table('lokasi_antar', function (Blueprint $table) {
            $table->double('jarak_min')->nullable()->after('id');
            $table->double('jarak_max')->nullable()->after('jarak_min');
        });

        foreach (DB::table('lokasi_antar')->get() as $row) {
            preg_match_all('/\d+(?:[.,]\d+)?/', (string) $row->nama_kelurahan, $matches);
            $numbers = array_map(fn ($n) => (float) str_replace(',', '.', $n), $matches[0] ?? []);

            $jarakMin = null;
            $jarakMax = null;

            if (count($numbers) >= 2) {
                $jarakMin = min($numbers[0], $numbers[1]);
                $jarakMax = max($numbers[0], $numbers[1]);
            } elseif (count($numbers) === 1) {
                $jarakMin = 0;
                $jarakMax = $numbers[0];
            }

            DB::table('lokasi_antar')->where('id', $row->id)->update([
                'jarak_min' => $jarakMin,
                'jarak_max' => $jarakMax,
            ]);
        }

        Schema::table('lokasi_antar', function (Blueprint $table) {
            $table->dropColumn('nama_kelurahan');
        });
    }

    public function down(): void
    {
        Schema::table('lokasi_antar', function (Blueprint $table) {
            $table->string('nama_kelurahan')->nullable()->after('id');
        });

        DB::table('lokasi_antar')->update([
            'nama_kelurahan' => DB::raw("CONCAT('Radius ', jarak_min, ' sd ', jarak_max, ' km')"),
        ]);

        Schema::table('lokasi_antar', function (Blueprint $table) {
            $table->dropColumn(['jarak_min', 'jarak_max']);
        });
    }
};
