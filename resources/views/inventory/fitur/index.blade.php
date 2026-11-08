@extends('inventory.layouts.app')

@section('header', 'Pengaturan Fitur')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Pengaturan Fitur</h3>
        </div>
        <div class="card-body">
            <p>
                Jumlah item barang dengan stok &gt; 0 saat ini: <strong>{{ $jumlahStok }}</strong>
                dari batas tier Starter (<strong>{{ $batasStarter }}</strong> item).
            </p>
            @if ($jumlahStok > $batasStarter)
                <div class="alert alert-warning">
                    Jumlah item sudah melebihi batas Starter. Jika tier Premium belum aktif,
                    input Barang Masuk (PBF &amp; non PBF) akan ditolak sampai tier Premium diaktifkan.
                </div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fitur</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fiturList as $row)
                        @php($aktif = strtoupper($row->acc) === 'Y')
                        @php($independen = !in_array($row->fitur, ['starter', 'premium'], true))
                        <tr>
                            <td class="text-capitalize">{{ $row->fitur }}</td>
                            <td>
                                @if ($aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if (!$aktif)
                                    <form action="{{ route('inventory.fitur.update', $row->id_fitur) }}" method="POST"
                                        onsubmit="return confirm('Aktifkan fitur {{ $row->fitur }}?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-primary">Aktifkan</button>
                                    </form>
                                @elseif ($independen)
                                    <form action="{{ route('inventory.fitur.update', $row->id_fitur) }}" method="POST"
                                        onsubmit="return confirm('Nonaktifkan fitur {{ $row->fitur }}? Tampilan awal domain akan dialihkan ke /inventory dan halaman e-commerce tidak bisa diakses.');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-muted mb-0">
                <strong class="text-capitalize">{{ \App\Models\Fitur::NAMA_ECOMMERCE }}</strong>:
                jika Aktif, tampilan awal domain adalah halaman toko online (<code>/home-page</code>).
                Jika Tidak Aktif, tampilan awal domain adalah <code>/inventory</code> dan seluruh halaman
                e-commerce (toko online, login pelanggan, keranjang, checkout, dll) tidak bisa diakses.
            </p>
        </div>
    </div>
@endsection
