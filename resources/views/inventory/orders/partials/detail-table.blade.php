@php
    $grand = $subtotal * (1 - ($dpBayar / 100));
@endphp
<div class="table-responsive">
    <table id="tabel-detail-order" class="table table-sm table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty Retail</th>
                <th>Sat Retail</th>
                <th>Konversi</th>
                <th>Qty Grosir</th>
                <th>Sat Grosir</th>
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
                    <td class="text-end">{{ $row->qty_dtrbmasuk }}</td>
                    <td class="text-center">{{ $row->sat_dtrbmasuk }}</td>
                    <td class="text-center">{{ $row->konversi }}</td>
                    <td class="text-center">
                        <input type="number" step="any" min="0.01"
                            class="form-control form-control-sm edit-qtygrosir" style="width:80px; display:inline-block;"
                            data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}" value="{{ $row->qtygrosir_dtrbmasuk }}">
                    </td>
                    <td class="text-center">{{ $row->satgrosir_dtrbmasuk }}</td>
                    <td class="text-end">{{ number_format($row->hrgsat_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->hrgttl_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-hapus-detail"
                            data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Belum ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row justify-content-end">
    <div class="col-md-4">
        <div class="mb-2">
            <label class="form-label fw-bold">Sub Total</label>
            <input type="text" id="ttl_trkasir" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Diskon (%)</label>
            <input type="text" id="dp_bayar" class="form-control text-end" value="{{ $dpBayar }}">
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Grand Total</label>
            <input type="text" id="sisa_bayar" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format($grand, 0, ',', '.') }}" readonly>
        </div>
    </div>
</div>

<script>
    (function() {
        var table = $('#tabel-detail-order').DataTable();

        $(document).off('focus', '.edit-qtygrosir').on('focus', '.edit-qtygrosir', function() {
            $(this).data('original-value', $(this).val());
        });

        $(document).off('keydown', '.edit-qtygrosir').on('keydown', '.edit-qtygrosir', function(e) {
            if (e.which === 13) { e.preventDefault(); $(this).trigger('blur'); }
        });

        $(document).off('change', '.edit-qtygrosir').on('change', '.edit-qtygrosir', function() {
            var $input = $(this);
            var id = $input.data('id-dtrbmasuk');
            var val = $input.val();
            var original = $input.data('original-value');

            if (val === '' || isNaN(val) || parseFloat(val) <= 0) {
                alert('Qty Grosir harus diisi angka lebih dari 0');
                $input.val(original);
                return;
            }

            fetch('{{ url('inventory/orders/detail') }}/' + id + '/qty-grosir', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qtygrosir_dtrbmasuk: val }),
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal update data');
                        $input.val(original);
                        return;
                    }
                    $input.data('original-value', val);
                    var $row = $input.closest('tr');
                    $row.find('td').eq(3).text(resp.qty_dtrbmasuk);
                    $row.find('td').eq(9).text(resp.hrgttl_dtrbmasuk);
                    document.getElementById('ttl_trkasir').value = resp.subtotal;
                    hitungDiskon();
                });
        });

        $(document).off('click', '.btn-hapus-detail').on('click', '.btn-hapus-detail', function() {
            var $btn = $(this);
            var id = $btn.data('id-dtrbmasuk');

            fetch('{{ url('inventory/orders/detail') }}/' + id, {
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
                    document.getElementById('ttl_trkasir').value = resp.subtotal;
                    hitungDiskon();
                });
        });

        document.getElementById('dp_bayar').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); hitungDiskon(); }
        });
        document.getElementById('dp_bayar').addEventListener('change', hitungDiskon);
    })();

    function hitungDiskon() {
        var ttl = document.getElementById('ttl_trkasir').value.split('.').join('') || '0';
        var dp = document.getElementById('dp_bayar').value.split('.').join('') || '0';
        var grand = parseInt(ttl) * (1 - (parseInt(dp) / 100));
        document.getElementById('sisa_bayar').value = Math.round(grand).toLocaleString('id-ID');
    }
</script>
