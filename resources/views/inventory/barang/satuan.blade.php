@extends('inventory.layouts.app')

@section('header', 'Satuan Bertingkat')

@section('content')
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Satuan Bertingkat: {{ $barang->nm_barang }} ({{ $barang->kd_barang }})</h3>
            <a href="{{ route('inventory.barang.edit', $barang->id_barang) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Barang
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <p class="text-muted">
                Urutan 1 = satuan dasar (dipakai untuk menghitung stok). "Rasio ke bawah" = berapa satuan di
                tingkat sebelumnya ada dalam 1 satuan ini. Hanya tingkat paling atas yang bisa dihapus, supaya
                urutan tidak bolong.
            </p>

            <table class="table table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Satuan</th>
                        <th>Rasio ke Bawah</th>
                        <th>Kumulatif ke Satuan Dasar</th>
                        <th>Harga Jual</th>
                        <th>Harga Beli</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tingkat as $t)
                        <tr>
                            <td>{{ $t->urutan }}</td>
                            <td>{{ $t->satuan->nm_satuan ?? '-' }}</td>
                            <td>{{ $t->urutan === 1 ? '-' : rtrim(rtrim(number_format($t->rasio_ke_bawah, 2, ',', '.'), '0'), ',') }}</td>
                            <td>{{ rtrim(rtrim(number_format($kumulatif[$t->urutan] ?? 1, 2, ',', '.'), '0'), ',') }}</td>
                            <td>Rp {{ number_format($t->harga_jual, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($t->harga_beli, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $t->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($t->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEdit{{ $t->id }}">Edit</button>

                                @if ($t->urutan === ($tingkat->max('urutan')))
                                    <form action="{{ route('inventory.barang.satuan.destroy', [$barang->id_barang, $t->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus tingkat satuan {{ $t->satuan->nm_satuan ?? '' }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit{{ $t->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('inventory.barang.satuan.update', [$barang->id_barang, $t->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Tingkat {{ $t->urutan }} ({{ $t->satuan->nm_satuan ?? '-' }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if ($t->urutan !== 1)
                                                <div class="mb-3">
                                                    <label class="form-label">Rasio ke Bawah</label>
                                                    <input type="number" step="0.01" min="0.01" name="rasio_ke_bawah"
                                                        class="form-control" value="{{ $t->rasio_ke_bawah }}" required>
                                                </div>
                                            @endif
                                            <div class="mb-3">
                                                <label class="form-label">Harga Jual</label>
                                                <input type="number" step="1" min="0" name="harga_jual"
                                                    class="form-control" value="{{ $t->harga_jual }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Harga Beli</label>
                                                <input type="number" step="1" min="0" name="harga_beli"
                                                    class="form-control" value="{{ $t->harga_beli }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="aktif" {{ $t->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="nonaktif" {{ $t->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada satuan bertingkat untuk item ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <hr>

            <h5 class="fw-bold">Tambah Tingkat {{ $urutanBerikutnya }}</h5>
            <form action="{{ route('inventory.barang.satuan.store', $barang->id_barang) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Satuan</label>
                    <select name="id_satuan" class="form-control @error('id_satuan') is-invalid @enderror" required>
                        <option value="">-- Pilih Satuan --</option>
                        @foreach ($satuanList as $s)
                            <option value="{{ $s->id_satuan }}" {{ old('id_satuan') == $s->id_satuan ? 'selected' : '' }}>
                                {{ $s->nm_satuan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_satuan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                @if ($urutanBerikutnya !== 1)
                    <div class="col-md-2">
                        <label class="form-label">Rasio ke Bawah</label>
                        <input type="number" step="0.01" min="0.01" name="rasio_ke_bawah"
                            class="form-control @error('rasio_ke_bawah') is-invalid @enderror" value="{{ old('rasio_ke_bawah') }}" required>
                        @error('rasio_ke_bawah') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" step="1" min="0" name="harga_jual"
                        class="form-control @error('harga_jual') is-invalid @enderror" value="{{ old('harga_jual') }}" required>
                    @error('harga_jual') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" step="1" min="0" name="harga_beli" class="form-control" value="{{ old('harga_beli') }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-info">Tambah Tingkat</button>
                </div>
            </form>
        </div>
    </div>
@endsection
