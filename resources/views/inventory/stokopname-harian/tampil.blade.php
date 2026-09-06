@extends('inventory.layouts.app')

@section('header', 'Stok Opname Harian')

@section('content')
    <input type="hidden" id="tgl_awal" value="{{ $tglAwal }}">
    <input type="hidden" id="shift" value="{{ $shift }}">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stok Opname Harian</h3>
        </div>
        <div class="card-body table-responsive">
            <div class="row">
                <div class="col-sm-6"><b>PETUGAS</b> : {{ Auth::guard('admin')->user()->nama_lengkap }}</div>
                <div class="col-sm-6"><b>TIME</b> : {{ now()->format('d M Y - H:i:s') }}</div>
            </div>

            @php
                $shiftLabel = [1 => 'PAGI', 2 => 'SORE', 3 => 'MALAM'][$shift] ?? $shift;
            @endphp
            <center><strong>STOK OPNAME SHIFT {{ $shiftLabel }}<br>Tanggal : {{ $tglAwal }}</strong></center>
            <hr>
            <div id="tabel_stokopname">Memuat...</div>

            <hr>
            <center><strong>REKAP STOK OPNAME</strong></center>
            <hr>
            <div id="tabel_stokopname_rekap">Memuat...</div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        tabelStokopname();
        tabelStokopnameRekap();
    });

    function simpanStokOpname(param) {
        var stokFisik = $('#stok_fisik_' + param).val();
        var idBarang = $('#pilih_' + param).data('id_barang');
        var kdBarang = $('#pilih_' + param).data('kd_barang');
        var hrgsatBarang = $('#pilih_' + param).data('hrgsat_barang');
        var shift = $('#pilih_' + param).data('shift');
        var tglAwal = $('#tgl_awal').val();

        $.ajax({
            type: 'POST',
            url: "{{ route('inventory.soharian.store') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id_barang: idBarang,
                kd_barang: kdBarang,
                stok_fisik: stokFisik,
                hrgsat_barang: hrgsatBarang,
                shift: shift,
                tgl_awal: tglAwal
            },
            success: function () {
                tabelStokopname();
                tabelStokopnameRekap();
            }
        });
    }

    function hapusStokOpname(id) {
        if (!confirm('Hapus data stok opname ini?')) { return; }
        $.ajax({
            type: 'POST',
            url: "{{ url('inventory/soharian') }}/" + id,
            data: {
                _token: "{{ csrf_token() }}",
                _method: 'DELETE'
            },
            success: function () {
                tabelStokopname();
                tabelStokopnameRekap();
            }
        });
    }

    function tabelStokopname() {
        var tglAwal = $('#tgl_awal').val();
        var shift = $('#shift').val();

        $.ajax({
            url: "{{ route('inventory.soharian.grid') }}?" + $.param({ tgl_awal: tglAwal, shift: shift }),
            type: 'GET',
            success: function (data) {
                $('#tabel_stokopname').html(data);
            }
        });
    }

    function tabelStokopnameRekap() {
        var tglAwal = $('#tgl_awal').val();
        var shift = $('#shift').val();

        $.ajax({
            url: "{{ route('inventory.soharian.rekap') }}?" + $.param({ tgl_awal: tglAwal, shift: shift }),
            type: 'GET',
            success: function (data) {
                $('#tabel_stokopname_rekap').html(data);
            }
        });
    }
</script>
@endpush
