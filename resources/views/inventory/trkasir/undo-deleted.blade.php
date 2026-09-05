@extends('inventory.layouts.app')

@section('header', 'Undo Transaksi Terhapus')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Undo Transaksi Terhapus</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar transaksi yang sudah dihapus total. Restore akan menghidupkan kembali
                transaksi ini (header + semua baris item) dan mengurangi stok barang lagi sesuai qty transaksi.</p>

            <div class="table-responsive">
                <table id="tabel-undo-deleted" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Jumlah Item</th>
                            <th>Tanggal Delete</th>
                            <th>Waktu Transaksi</th>
                            <th>Nilai Transaksi</th>
                            <th>Delete By</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $d)
                            <tr>
                                <td>{{ $d->kd_trkasir }}</td>
                                <td class="text-center">{{ $d->jumlah_item }}</td>
                                <td>{{ $d->tgl_delete }}</td>
                                <td>{{ $d->waktu_trx }}</td>
                                <td class="text-end">Rp {{ number_format($d->nilai_transaksi, 0, ',', '.') }}</td>
                                <td>{{ $adminNames[$d->id_admin_hapus] ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('inventory.trkasir.undo-deleted.restore') }}" method="POST" id="restore-{{ $loop->index }}">
                                        @csrf
                                        <input type="hidden" name="kd_trkasir" value="{{ $d->kd_trkasir }}">
                                        <button type="button" class="btn btn-success btn-xs" onclick="confirmRestore('restore-{{ $loop->index }}', '{{ $d->kd_trkasir }}')">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada transaksi yang dihapus.</td>
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
        $('#tabel-undo-deleted').DataTable({
            order: [],
            pageLength: 25,
            autoWidth: false,
        });
    });
</script>
@endpush
