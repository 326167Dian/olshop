<table id="tabel-grid-so-harian" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Kode Barang</th>
            <th class="text-center">Nama Obat</th>
            <th class="text-center">Satuan</th>
            <th class="text-center">Rak Obat</th>
            <th class="text-center">Qty Terjual</th>
            <th class="text-center">Stok Sistem</th>
            <th class="text-center">Stok Fisik</th>
            <th class="text-center">Submit</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $i => $r)
            @php($no = $i + 1)
            <tr>
                <td class="text-center">{{ $no }}</td>
                <td class="text-center">{{ $r->kd_barang }}</td>
                <td>{{ $r->nm_barang }}</td>
                <td class="text-center">{{ $r->sat_barang }}</td>
                <td class="text-center">{{ $r->jenisobat }}</td>
                <td class="text-center">{{ $r->ttlqty }}</td>
                <td class="text-center">{{ $r->stok_sistem }}</td>
                <td class="text-center">
                    <input type="number" min="0" class="form-control text-center" id="stok_fisik_{{ $no }}" value="0">
                </td>
                <td class="text-center">
                    <button type="button" id="pilih_{{ $no }}" class="btn btn-primary btn-sm"
                        onclick="simpanStokOpname('{{ $no }}')"
                        data-id_barang="{{ $r->id_barang }}"
                        data-kd_barang="{{ $r->kd_barang }}"
                        data-hrgsat_barang="{{ $r->hrgsat_barang }}"
                        data-shift="{{ request('shift') }}">
                        <i class="fa fa-fw fa-check"></i> SIMPAN
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada item terjual pada shift &amp; tanggal ini yang perlu diopname.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
    $(function () {
        $('#tabel-grid-so-harian').DataTable({
            aLengthMenu: [[5, 25, 50, 75, -1], [5, 25, 50, 75, 'All']],
            iDisplayLength: 5
        });
    });
</script>
