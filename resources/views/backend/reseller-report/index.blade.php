@extends('backend.layouts.app')

@section('title', 'Reseller')
@section('header', 'Halaman Data Reseller')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Reseller</h3>
                <p class="text-muted mb-0">Daftar reseller beserta omzet grup (total transaksi "Selesai" dari pelanggan yang mereka rekrut) dan estimasi komisi berdasarkan persentase komisi saat ini ({{ rtrim(rtrim(number_format($komisiPersen, 2, ',', '.'), '0'), ',') }}%). Halaman ini hanya untuk dilihat -- tidak bisa diubah atau dihapus dari sini.</p>
            </div>

            <div class="card-body">
                <table class="table table-auto table-sm table-bordered table-striped w-100" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Reseller</th>
                            <th>No HP</th>
                            <th>Jumlah Pelanggan</th>
                            <th>Omzet Grup</th>
                            <th>Estimasi Komisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resellers as $index)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $index->nama_lengkap }}</td>
                            <td>{{ $index->no_hp }}</td>
                            <td>{{ $index->referred_customers_count }}</td>
                            <td>Rp {{ number_format($index->omzet, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($index->komisi, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('reseller-report.show', $index->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#example1').DataTable({
            responsive: true,
            autoWidth: false
        });
    });
</script>
@endpush
