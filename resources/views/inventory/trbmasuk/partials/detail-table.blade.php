<div class="table-responsive">
    <table id="tabel-detail-trbmasuk" class="table table-sm table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>No. Batch</th>
                <th>Kadaluarsa</th>
                <th>Hrg Beli</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detail as $index => $row)
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
                    <td class="text-end">{{ number_format($row->hrgsat_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->hrgttl_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-hapus-detail"
                            data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Belum ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    (function() {
        var table = $('#tabel-detail-trbmasuk').DataTable();

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

            fetch('{{ url('inventory/trbmasuk/detail') }}/' + id + '/qty', {
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
                });
        });

        $(document).off('click', '.btn-hapus-detail').on('click', '.btn-hapus-detail', function() {
            var $btn = $(this);
            var id = $btn.data('id-dtrbmasuk');

            fetch('{{ url('inventory/trbmasuk/detail') }}/' + id, {
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
                    table.row($btn.closest('tr')).remove().draw(false);
                });
        });
    })();
</script>
