@extends('inventory.layouts.app')

@section('header', 'Kelola Soal Ujian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kelola Soal Ujian</h3>
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ $headerEdit ? route('inventory.ujian.header-update', $headerEdit->id_soal) : route('inventory.ujian.header-store') }}"
                class="row g-2 align-items-end mb-3">
                @csrf
                @if ($headerEdit)
                    @method('PUT')
                @endif
                <div class="col-md-4">
                    <label class="form-label">Nama Ujian</label>
                    <input type="text" name="nm_ujian" class="form-control" required
                        value="{{ old('nm_ujian', $headerEdit->nm_ujian ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durasi (menit)</label>
                    <input type="number" name="durasi" min="1" class="form-control" required
                        value="{{ old('durasi', $headerEdit->durasi ?? '') }}">
                </div>
                <div class="col-md-3">
                    @if ($headerEdit)
                        <button type="submit" class="btn btn-warning">Update Ujian</button>
                        <a href="{{ route('inventory.ujian.kelola') }}" class="btn btn-secondary">Batal</a>
                    @else
                        <button type="submit" class="btn btn-primary">Simpan Ujian</button>
                    @endif
                </div>
            </form>

            @if ($daftarUjian->isNotEmpty())
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>Nama Ujian</th>
                                <th width="120">Durasi (menit)</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarUjian as $u)
                                <tr>
                                    <td>{{ $u->id_soal }}</td>
                                    <td>{{ $u->nm_ujian }}</td>
                                    <td>{{ $u->durasi }}</td>
                                    <td>
                                        <a href="{{ route('inventory.ujian.kelola', ['edit_header_id' => $u->id_soal]) }}"
                                            class="btn btn-warning btn-xs">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @php $defaultTambahId = $selectedId > 0 ? $selectedId : ($daftarUjian->first()->id_soal ?? 0); @endphp
            <a href="{{ route('inventory.ujian.soal.create', $defaultTambahId > 0 ? ['ujian_id' => $defaultTambahId] : []) }}"
                class="btn btn-success">Tambah Soal</a>
            <a href="{{ route('inventory.ujian.index') }}" class="btn btn-secondary">Kembali ke Ujian</a>
            <br><br>

            @if ($daftarSoal->isEmpty())
                <div class="alert alert-info">Belum ada soal.</div>
            @else
                <table id="example1" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th width="45">No</th>
                            <th>Nama Ujian</th>
                            <th width="85">Durasi</th>
                            <th>Soal</th>
                            <th width="110">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarSoal as $i => $soal)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $soal->header->nm_ujian ?? '-' }}</td>
                                <td>{{ $soal->header->durasi ?? '-' }} menit</td>
                                <td>
                                    <div>{!! $soal->pertanyaan_html !!}</div>
                                    <div class="mt-2">
                                        A. {{ $soal->opsi_a }}<br>
                                        B. {{ $soal->opsi_b }}<br>
                                        C. {{ $soal->opsi_c }}<br>
                                        <strong>Jawaban: {{ strtoupper($soal->jawaban_benar) }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('inventory.ujian.soal.edit', $soal->id) }}"
                                        class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('inventory.ujian.soal.destroy', $soal->id) }}" method="POST"
                                        class="d-inline" id="delete-soal-{{ $soal->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDelete('delete-soal-{{ $soal->id }}', 'soal ini')"
                                            class="btn btn-danger btn-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
