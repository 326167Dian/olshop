@extends('inventory.layouts.app')

@section('header', 'Perubahan Transaksi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Perubahan Transaksi</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar transaksi yang mengalami perubahan (item ditambah/dihapus/diubah setelah
                transaksi final) atau yang dihapus total.</p>

            <div class="table-responsive">
                <table id="tabel-perubahan" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>Jumlah Revisi</th>
                            <th>Total Sekarang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $i => $d)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $d['kd_trkasir'] }}</td>
                                <td>{{ $d['tgl_trkasir'] }}</td>
                                <td>{{ $d['nm_pelanggan'] }}</td>
                                <td>
                                    @if ($d['status'] === 'AKTIF')
                                        <span class="badge bg-success">AKTIF</span>
                                    @else
                                        <span class="badge bg-danger">DIHAPUS</span>
                                    @endif
                                </td>
                                <td>{{ (int) $d['tipetx'] }}</td>
                                <td>Rp {{ number_format($d['ttl_trkasir'], 0, ',', '.') }}</td>
                                <td>
                                    <a class="btn btn-info btn-xs" href="{{ route('inventory.trkasir.perubahan', ['kd_trkasir' => $d['kd_trkasir']]) }}">Lihat Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada transaksi yang direvisi atau dihapus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#tabel-perubahan').DataTable({
            order: [],
            pageLength: 25,
            autoWidth: false,
        });
    });
</script>
@endpush
