@extends('inventory.layouts.app')

@section('header', 'Hasil Ujian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Ujian</h3>
        </div>
        <div class="card-body">
            @if ($hasil->nama_ujian)
                <p>Nama Ujian: <strong>{{ $hasil->nama_ujian }}</strong></p>
            @endif
            <p>Total Soal: <strong>{{ $hasil->total_soal }}</strong></p>
            <p>Jawaban Benar: <strong>{{ $hasil->jawaban_benar }}</strong></p>
            <p>Jawaban Salah: <strong>{{ $hasil->jawaban_salah }}</strong></p>
            <p>Tidak dijawab: <strong>{{ $hasil->tidak_dijawab }}</strong></p>
            @if ($tidakValid > 0)
                <p>Soal Tidak Valid: <strong>{{ $tidakValid }}</strong></p>
            @endif
            <p>Durasi Pengerjaan: <strong>{{ $hasil->durasi_detik }} detik</strong></p>
            @if ($hasil->durasi_batas_detik)
                <p>Status Waktu: <strong>{{ $hasil->status_waktu === 'timeout' ? 'Melebihi Batas Waktu' : 'Tepat Waktu' }}</strong></p>
            @endif
            <hr>
            <h4>Nilai Akhir: <strong>{{ $hasil->nilai_akhir }}</strong> / 100</h4>
            <br>
            <a href="{{ route('inventory.ujian.index') }}" class="btn btn-primary">Kembali ke Ujian</a>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
@endsection
