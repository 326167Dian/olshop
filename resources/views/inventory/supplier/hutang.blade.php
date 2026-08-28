@extends('inventory.layouts.app')

@section('header', 'Hutang Supplier')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hutang Supplier: {{ $supplier->nm_supplier }}</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.supplier.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nilai Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hutang as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->kd_trbmasuk }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_trbmasuk)->format('d-m-Y') }}</td>
                            <td class="text-end">{{ number_format($row->ttl_trbmasuk, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada hutang.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-center">Total Hutang</td>
                        <td class="text-end">Rp {{ number_format($totalHutang, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
