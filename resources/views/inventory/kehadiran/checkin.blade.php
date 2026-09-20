@extends('inventory.layouts.app')

@section('header', 'Check-In')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Check-In</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small"><i class="fas fa-map-marker-alt"></i> Check-in akan meminta izin akses lokasi HP Anda untuk memverifikasi Anda berada di area apotek.</p>

            @if ($jadwalBelumAbsen->isEmpty())
                <p>Tidak ada jadwal shift disetujui untuk hari ini yang belum diabsen. Anda tetap bisa check-in tanpa jadwal (ad-hoc).</p>
                <form method="POST" action="{{ route('inventory.kehadiran.checkin.store') }}" class="js-geo-form">
                    @csrf
                    <input type="hidden" name="id_jadwal" value="">
                    <button type="submit" class="btn btn-success">Check-In Sekarang (Tanpa Jadwal)</button>
                </form>
            @else
                <p>Pilih shift yang ingin Anda check-in:</p>
                @foreach ($jadwalBelumAbsen as $jadwal)
                    <form method="POST" action="{{ route('inventory.kehadiran.checkin.store') }}" class="js-geo-form mb-2">
                        @csrf
                        <input type="hidden" name="id_jadwal" value="{{ $jadwal->id_jadwal }}">
                        <button type="submit" class="btn btn-outline-success">
                            {{ $jadwal->shift->nama_shift ?? '-' }}
                            ({{ $jadwal->shift->jam_masuk ?? '-' }} - {{ $jadwal->shift->jam_pulang ?? '-' }})
                        </button>
                    </form>
                @endforeach
            @endif

            <a href="{{ route('inventory.kehadiran.index') }}" class="btn btn-secondary mt-2">Kembali</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.js-geo-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = form.querySelector('button[type=submit]');
            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Mengambil lokasi...';

            if (!navigator.geolocation) {
                alert('Browser/HP Anda tidak mendukung akses lokasi. Check-in tidak bisa dilanjutkan.');
                btn.disabled = false;
                btn.innerHTML = originalText;
                return;
            }

            navigator.geolocation.getCurrentPosition(function (pos) {
                var latInput = document.createElement('input');
                latInput.type = 'hidden';
                latInput.name = 'lat';
                latInput.value = pos.coords.latitude;
                form.appendChild(latInput);

                var lngInput = document.createElement('input');
                lngInput.type = 'hidden';
                lngInput.name = 'lng';
                lngInput.value = pos.coords.longitude;
                form.appendChild(lngInput);

                form.submit();
            }, function (err) {
                alert('Gagal mengambil lokasi Anda. Aktifkan izin lokasi di HP/browser lalu coba lagi. (' + err.message + ')');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            });
        });
    });
</script>
@endpush
