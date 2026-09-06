<table id="tabel-rekap-stokopname" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Kode Barang</th>
            <th class="text-center">Nama Obat</th>
            <th class="text-center">Satuan</th>
            <th class="text-center">Stok Sistem<br>(SS)</th>
            <th class="text-center">Stok Fisik<br>(SF)</th>
            <th class="text-center">Exp Date<br>(SF)</th>
            <th class="text-center">Jml<br>(ED)</th>
            <th class="text-center">Hasil<br>(SF - SS)</th>
            <th class="text-center">Current Time</th>
            @if ($isPemilik)
                <th class="text-center">Delete</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $i => $r)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-center">{{ $r->kd_barang }}</td>
                <td class="text-center">{{ $r->nm_barang }}</td>
                <td class="text-center">{{ $r->sat_barang }}</td>
                <td class="text-center">{{ $r->stok_sistem }}</td>
                <td class="text-center">{{ $r->stok_fisik }}</td>
                <td class="text-center">{{ $r->exp_date }}</td>
                <td class="text-center">{{ $r->jml }}</td>
                <td class="text-center">{{ $r->selisih }}</td>
                <td class="text-center">{{ \Illuminate\Support\Carbon::parse($r->tgl_current)->format('d M Y - H:i:s') }}</td>
                @if ($isPemilik)
                    <td class="text-center">
                        <button type="button" id="hapus_{{ $r->id_stok_opname }}" class="btn btn-danger btn-sm" onclick="hapusStokOpname('{{ $r->id_stok_opname }}')">
                            <i class="fa fa-fw fa-trash"></i> HAPUS
                        </button>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $isPemilik ? 11 : 10 }}" class="text-center">Belum ada data stok opname untuk rak &amp; tanggal ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
    $(function () {
        $('#tabel-rekap-stokopname').DataTable({
            aLengthMenu: [[5, 25, 50, 75, -1], [5, 25, 50, 75, 'All']],
            iDisplayLength: 5
        });
    });
</script>
