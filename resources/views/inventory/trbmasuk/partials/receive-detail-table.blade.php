<div class="table-responsive">
    <table id="tabel-receive-detail" class="table table-sm table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Konversi</th>
                <th>Qty Grosir</th>
                <th>Sat Grosir</th>
                <th>No. Batch</th>
                <th>Kadaluarsa</th>
                <th>Diskon (%)</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($migrated as $row)
                <tr data-kd-barang="{{ $row->kd_barang }}" data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}">
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->kd_barang }}</td>
                    <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:70px;"
                            data-field="konversi" value="{{ $row->konversi }}">
                    </td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:80px;"
                            data-field="qtygrosir" value="{{ $row->qty_grosir }}">
                    </td>
                    <td class="text-center">{{ $row->satgrosir_dtrbmasuk }}</td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:90px;"
                            data-field="batch" value="{{ $row->no_batch }}">
                    </td>
                    <td class="text-center">
                        <input type="date" class="form-control form-control-sm edit-field" style="width:135px;"
                            data-field="expdate" value="{{ $row->exp_date?->format('Y-m-d') }}">
                    </td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:70px;"
                            data-field="diskon" value="{{ $row->diskon }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgbeli" value="{{ (int) $row->hrgsat_dtrbmasuk }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgjual" value="{{ (int) $row->hrgjual_dtrbmasuk }}">
                    </td>
                    <td class="text-end col-total">{{ number_format($row->hrgttl_dtrbmasuk, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-hapus-detail"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            @endforeach
            @foreach ($pending as $row)
                <tr data-kd-barang="{{ $row->kd_barang }}" data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}">
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->kd_barang }}</td>
                    <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:70px;"
                            data-field="konversi" value="{{ $row->konversi }}">
                    </td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:80px;"
                            data-field="qtygrosir" value="{{ $row->qtygrosir_dtrbmasuk }}">
                    </td>
                    <td class="text-center">{{ $row->satgrosir_dtrbmasuk }}</td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:90px;"
                            data-field="batch" value="{{ $row->no_batch }}">
                    </td>
                    <td class="text-center">
                        <input type="date" class="form-control form-control-sm edit-field" style="width:135px;"
                            data-field="expdate" value="{{ \Illuminate\Support\Carbon::parse($row->exp_date)->format('Y-m-d') }}">
                    </td>
                    <td class="text-center">
                        <input type="number" step="any" class="form-control form-control-sm edit-field" style="width:70px;"
                            data-field="diskon" value="0">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgbeli" value="{{ (int) $row->hrgsat_dtrbmasuk }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgjual" value="{{ (int) $row->hrgjual_dtrbmasuk }}">
                    </td>
                    <td class="text-end col-total">{{ number_format(round($row->hrgsat_dtrbmasuk * $row->konversi * $row->qtygrosir_dtrbmasuk), 0, ',', '.') }}</td>
                    <td class="text-center text-muted small">Belum diterima</td>
                </tr>
            @endforeach
            @if ($migrated->isEmpty() && $pending->isEmpty())
                <tr>
                    <td colspan="13" class="text-center">Tidak ada item pada pesanan ini.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="row justify-content-end">
    <div class="col-md-4">
        <div class="mb-2">
            <label class="form-label fw-bold">Sub Total</label>
            <input type="text" id="ttl-subtotal" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
        </div>
    </div>
</div>

<script>
    (function() {
        var kdTrbmasuk = document.getElementById('kd_trbmasuk').value;
        var kdOrders = document.getElementById('kd_orders').value;

        $(document).off('focus', '#tabel-receive-detail .edit-field').on('focus', '#tabel-receive-detail .edit-field', function() {
            $(this).data('original-value', $(this).val());
        });

        // Hitung ulang kolom Total secara langsung (di browser saja, tanpa AJAX) begitu
        // Konversi/Qty Grosir/Diskon/Harga Beli diketik, supaya pengguna langsung lihat
        // perubahannya -- nilai final tetap disimpan lewat AJAX saat blur/Enter seperti biasa.
        $(document).off('input', '#tabel-receive-detail .edit-field[data-field="konversi"], #tabel-receive-detail .edit-field[data-field="qtygrosir"], #tabel-receive-detail .edit-field[data-field="diskon"], #tabel-receive-detail .edit-field[data-field="hrgbeli"]')
            .on('input', '#tabel-receive-detail .edit-field[data-field="konversi"], #tabel-receive-detail .edit-field[data-field="qtygrosir"], #tabel-receive-detail .edit-field[data-field="diskon"], #tabel-receive-detail .edit-field[data-field="hrgbeli"]', function() {
                var $row = $(this).closest('tr');
                var konversi = parseFloat($row.find('.edit-field[data-field="konversi"]').val()) || 0;
                var qtyGrosir = parseFloat($row.find('.edit-field[data-field="qtygrosir"]').val()) || 0;
                var diskon = parseFloat($row.find('.edit-field[data-field="diskon"]').val()) || 0;
                var hrgbeli = parseFloat($row.find('.edit-field[data-field="hrgbeli"]').val().replace(/\./g, '').replace(',', '.')) || 0;
                var total = Math.round(hrgbeli * (1 - diskon / 100) * konversi * qtyGrosir);
                $row.find('.col-total').text(total.toLocaleString('id-ID'));
            });

        $(document).off('keydown', '#tabel-receive-detail .edit-field').on('keydown', '#tabel-receive-detail .edit-field', function(e) {
            if (e.which === 13) { e.preventDefault(); $(this).trigger('blur'); }
        });

        $(document).off('change', '#tabel-receive-detail .edit-field').on('change', '#tabel-receive-detail .edit-field', function() {
            var $input = $(this);
            var $row = $input.closest('tr');
            var field = $input.data('field');
            var val = $input.val();
            var original = $input.data('original-value');

            // Input tanggal native kadang memicu 'change' dengan value kosong sesaat saat
            // masih diketik (misalnya baru sebagian segmen tanggal terisi) -- di sini cukup
            // dilewati diam-diam (bukan dianggap error) supaya pengguna bisa lanjut mengetik.
            if (field === 'expdate' && val === '') {
                return;
            }

            var numericFields = ['diskon', 'hrgbeli', 'konversi', 'qtygrosir'];
            if (val === '' || (numericFields.indexOf(field) !== -1 && isNaN(val))) {
                alert('Nilai tidak valid');
                $input.val(original);
                return;
            }

            var form = new FormData();
            form.append('field', field);
            form.append('kd_trbmasuk', kdTrbmasuk);
            form.append('kd_orders', kdOrders);
            form.append('kd_barang', $row.data('kd-barang'));
            form.append('id_dtrbmasuk', $row.data('id-dtrbmasuk'));
            form.append('value', val);
            form.append('_method', 'PUT');

            fetch("{{ route('inventory.trbmasuk.receive.detail.update') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal menyimpan perubahan');
                        $input.val(original);
                        return;
                    }
                    $input.data('original-value', val);
                    $row.data('id-dtrbmasuk', resp.id_dtrbmasuk);
                    $row.attr('data-id-dtrbmasuk', resp.id_dtrbmasuk);
                    $row.find('.col-total').text(resp.total_text);
                    $row.find('td').eq(12).html('<button type="button" class="btn btn-xs btn-danger btn-hapus-detail"><i class="fa fa-times"></i></button>');
                    document.getElementById('ttl-subtotal').value = resp.subtotal;
                });
        });

        $(document).off('click', '#tabel-receive-detail .btn-hapus-detail').on('click', '#tabel-receive-detail .btn-hapus-detail', function() {
            var $btn = $(this);
            var $row = $btn.closest('tr');

            var form = new FormData();
            form.append('id_dtrbmasuk', $row.data('id-dtrbmasuk'));
            form.append('kd_orders', kdOrders);
            form.append('kd_trbmasuk', kdTrbmasuk);
            form.append('_method', 'DELETE');

            fetch("{{ route('inventory.trbmasuk.receive.detail.destroy') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal menghapus data');
                        return;
                    }
                    $row.remove();
                    document.getElementById('ttl-subtotal').value = resp.subtotal;
                });
        });
    })();
</script>
