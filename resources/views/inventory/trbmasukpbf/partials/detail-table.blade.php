<div class="table-responsive">
    <table id="tabel-detail-trbmasukpbf" class="table table-sm table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Qty Retail</th>
                <th>Satuan</th>
                <th>No. Batch</th>
                <th>Kadaluarsa</th>
                <th>HNA</th>
                <th>Disc</th>
                <th>HNA+Disc</th>
                <th>Total</th>
                <th>Tipe</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detail as $index => $row)
                @php $hnadisc = $row->hnasat_dtrbmasuk * (1 - $row->diskon / 100); @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->kd_barang }}</td>
                    <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                    <td class="text-center">
                        <input type="number" step="any" min="0.01"
                            class="form-control form-control-sm edit-qty" style="width:90px; display:inline-block;"
                            data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}" value="{{ $row->qty_dtrbmasuk }}">
                    </td>
                    <td class="text-center">{{ $row->sat_dtrbmasuk }}</td>
                    <td class="text-center">{{ $row->no_batch }}</td>
                    <td class="text-center">{{ $row->exp_date?->format('Y-m-d') }}</td>
                    <td class="text-end">{{ number_format($row->hnasat_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $row->diskon }}%</td>
                    <td class="text-end">{{ number_format($hnadisc, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format(round($hnadisc * $row->qty_grosir), 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row->tipe_barang === 'bonus' ? 'Bonus' : 'Reguler' }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-hapus-detail"
                            data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Belum ada item.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-end">Total Harga</th>
                <th colspan="3" class="text-end">Rp {{ number_format($detail->sum(fn ($r) => round($r->hnasat_dtrbmasuk * (1 - $r->diskon / 100) * $r->qty_grosir)), 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="10" class="text-end">Total Harga + PPN</th>
                <th colspan="3" class="text-end">Rp {{ number_format($detail->sum('hrgttl_dtrbmasuk'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</div>

<script>
    (function() {
        var table = $('#tabel-detail-trbmasukpbf').DataTable();

        $(document).off('focus', '.edit-qty').on('focus', '.edit-qty', function() {
            $(this).data('original-value', $(this).val());
        });

        $(document).off('keydown', '.edit-qty').on('keydown', '.edit-qty', function(e) {
            if (e.which === 13) { e.preventDefault(); $(this).trigger('blur'); }
        });

        $(document).off('change', '.edit-qty').on('change', '.edit-qty', function() {
            var $input = $(this);
            var id = $input.data('id-dtrbmasuk');
            var val = $input.val();
            var original = $input.data('original-value');

            if (val === '' || isNaN(val) || parseFloat(val) <= 0) {
                alert('Qty harus diisi angka lebih dari 0');
                $input.val(original);
                return;
            }

            fetch('{{ url('inventory/trbmasukpbf/detail') }}/' + id + '/qty', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qty_dtrbmasuk: val }),
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal update data');
                        $input.val(original);
                        return;
                    }
                    $input.data('original-value', val);
                    if (typeof loadTabelDetail === 'function') loadTabelDetail();
                });
        });

        $(document).off('click', '.btn-hapus-detail').on('click', '.btn-hapus-detail', function() {
            var $btn = $(this);
            var id = $btn.data('id-dtrbmasuk');

            fetch('{{ url('inventory/trbmasukpbf/detail') }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal menghapus data');
                        return;
                    }
                    if (typeof loadTabelDetail === 'function') loadTabelDetail();
                });
        });
    })();
</script>
