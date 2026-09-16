@extends('inventory.layouts.app')

@section('header', 'Check-In')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Check-In</h3>
        </div>
        <div class="card-body">
            @if ($jadwalBelumAbsen->isEmpty())
                <p>Tidak ada jadwal shift disetujui untuk hari ini yang belum diabsen. Anda tetap bisa check-in tanpa jadwal (ad-hoc).</p>
                <form method="POST" action="{{ route('inventory.kehadiran.checkin.store') }}">
                    @csrf
                    <input type="hidden" name="id_jadwal" value="">
                    <button type="submit" class="btn btn-success">Check-In Sekarang (Tanpa Jadwal)</button>
                </form>
            @else
                <p>Pilih shift yang ingin Anda check-in:</p>
                @foreach ($jadwalBelumAbsen as $jadwal)
                    <form method="POST" action="{{ route('inventory.kehadiran.checkin.store') }}" class="mb-2">
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
