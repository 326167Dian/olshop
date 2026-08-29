@extends('inventory.layouts.app')

@section('header', 'Hasil Akhir Ujian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Akhir Ujian</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Ujian</label>
                    <select name="ujian_id" class="form-control">
                        <option value="">Semua Ujian</option>
                        @foreach ($daftarUjian as $u)
                            <option value="{{ $u->id_soal }}" {{ $selectedId === $u->id_soal ? 'selected' : '' }}>{{ $u->nm_ujian }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('inventory.ujian.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>

            @if ($daftarHasil->isEmpty())
                <div class="alert alert-info">Belum ada hasil ujian.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped w-100" id="example1">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Nama Ujian</th>
                                <th>Total Soal</th>
                                <th>Jawaban Benar</th>
                                <th>Jawaban Salah</th>
                                <th>Tidak dijawab</th>
                                <th>Nilai akhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarHasil as $h)
                                <tr>
                                    <td>{{ $h->nama_lengkap }}</td>
                                    <td>{{ $h->nama_ujian }}</td>
                                    <td>{{ $h->total_soal }}</td>
                                    <td>{{ $h->jawaban_benar }}</td>
                                    <td>{{ $h->jawaban_salah }}</td>
                                    <td>{{ $h->tidak_dijawab }}</td>
                                    <td>{{ $h->nilai_akhir }}</td>
                                    <td><a href="{{ route('inventory.ujian.hasil-detail', $h->id_hasil) }}" class="btn btn-info btn-xs">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($daftarProgress->isNotEmpty())
                <h4 class="mt-4">Belum Submit (Sedang/Meninggalkan Ujian)</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Nama Ujian</th>
                                <th>Terjawab</th>
                                <th>Waktu Mulai</th>
                                <th>Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarProgress as $p)
                                <tr>
                                    <td>{{ $p['nama_lengkap'] }}</td>
                                    <td>{{ $p['nama_ujian'] }}</td>
                                    <td>{{ $p['terjawab'] }} / {{ $p['total_soal'] }}</td>
                                    <td>{{ $p['waktu_mulai'] }}</td>
                                    <td>{{ $p['waktu_update'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
