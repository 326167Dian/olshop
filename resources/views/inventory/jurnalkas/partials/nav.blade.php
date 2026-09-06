@php($isPemilik = Auth::guard('admin')->user()->isPemilik())

<a class="btn btn-danger btn-flat" href="{{ route('inventory.jurnalkas.create') }}">Input Pengeluaran</a>
<a class="btn btn-info btn-flat" href="{{ route('inventory.jurnalkas.create-income') }}">Input Pemasukan</a>
@if ($isPemilik)
    <a class="btn btn-warning btn-flat" href="{{ route('inventory.jurnalkas.jenis.index') }}">Jenis Transaksi</a>
    <a class="btn btn-success btn-flat" href="{{ route('inventory.jurnalkas.pilih-hari') }}">Pilih Hari</a>
    <a class="btn btn-primary btn-flat" href="{{ route('inventory.jurnalkas.kemarin') }}">Catatan Kemarin</a>
    <a class="btn btn-success btn-flat" href="{{ route('inventory.jurnalkas.rekap') }}">Rekapitulasi</a>
@endif
