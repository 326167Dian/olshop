@extends('inventory.layouts.app')

@section('header', 'Buka/Tutup Kasir')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Opening dan Closing Transaksi Penjualan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success" href="{{ route('inventory.shiftkerja.buka.form') }}">Open Kasir</a>
            <a class="btn btn-sm btn-danger" href="{{ route('inventory.shiftkerja.tutup.form') }}">Tutup Kasir</a>

            <div class="table-responsive mt-3">
                <table id="tabel-shiftkerja" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas Buka</th>
                            <th>Petugas Tutup</th>
                            <th>Shift</th>
                            <th>Tanggal</th>
                            <th>Buka</th>
                            <th>Tutup</th>
                            <th>Saldo Awal</th>
                            <th>Saldo Akhir</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#tabel-shiftkerja').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.shiftkerja.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'petugasbuka', name: 'petugasbuka' },
                { data: 'petugastutup', name: 'petugastutup' },
                { data: 'nama_shift', name: 'nama_shift', className: 'text-center' },
                { data: 'tanggal', name: 'tanggal', className: 'text-center' },
                { data: 'waktubuka', name: 'waktubuka', className: 'text-center' },
                { data: 'waktututup', name: 'waktututup', className: 'text-center' },
                { data: 'saldoawal', name: 'saldoawal', className: 'text-end' },
                { data: 'saldoakhir', name: 'saldoakhir', className: 'text-end' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });

    function hapusShift(id) {
        if (!confirm('Yakin ingin menghapus data shift ini?')) return;

        fetch("{{ url('inventory/shiftkerja') }}/" + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menghapus');
                $('#tabel-shiftkerja').DataTable().ajax.reload(null, false);
            })
            .catch(function() { alert('Gagal menghapus data shift.'); });
    }
</script>
@endpush
