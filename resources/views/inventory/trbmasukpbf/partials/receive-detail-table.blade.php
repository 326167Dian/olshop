<div class="table-responsive">
    <table id="tabel-receive-detail-pbf" class="table table-sm table-bordered table-striped w-100">
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
                <th>HNA</th>
                <th>Harga Jual</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($migrated as $row)
                <tr data-kd-barang="{{ $row->kd_barang }}" data-no-batch-asal="{{ $row->no_batch }}">
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
                            data-field="hna" value="{{ (int) $row->hnasat_dtrbmasuk }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgjual" value="{{ (int) $row->hrgjual_dtrbmasuk }}">
                    </td>
                    <td class="text-end col-total">{{ number_format(round($row->hnasat_dtrbmasuk * (1 - $row->diskon / 100) * $row->qty_grosir), 0, ',', '.') }}</td>
                    <td class="text-center" data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}">
                        <button type="button" class="btn btn-xs btn-danger btn-hapus-detail"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            @endforeach
            @foreach ($pending as $row)
                <tr data-kd-barang="{{ $row->kd_barang }}" data-no-batch-asal="{{ $row->no_batch }}">
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
                            data-field="diskon" value="{{ $row->diskon }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hna" value="{{ (int) $row->hrgsat_dtrbmasuk }}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm edit-field" style="width:100px;"
                            data-field="hrgjual" value="{{ (int) $row->hrgjual_dtrbmasuk }}">
                    </td>
                    <td class="text-end col-total">{{ number_format(round($row->hrgsat_dtrbmasuk * (1 - $row->diskon / 100) * $row->qtygrosir_dtrbmasuk), 0, ',', '.') }}</td>
                    <td class="text-center" data-id-dtrbmasuk="{{ $row->id_dtrbmasuk }}">
                        <button type="button" class="btn btn-xs btn-warning btn-batalkan"><i class="fa fa-ban"></i> Batalkan</button>
                    </td>
                </tr>
            @endforeach
            @foreach ($dibatalkan as $row)
                <tr class="text-muted">
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->kd_barang }}</td>
                    <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                    <td class="text-center">{{ $row->konversi }}</td>
                    <td class="text-center">{{ $row->qtygrosir_dtrbmasuk }}</td>
                    <td class="text-center">{{ $row->satgrosir_dtrbmasuk }}</td>
                    <td colspan="5" class="text-center"><em>Dibatalkan</em></td>
                    <td class="text-end col-total">-</td>
                    <td class="text-center">-</td>
                </tr>
            @endforeach
            @if ($migrated->isEmpty() && $pending->isEmpty() && $dibatalkan->isEmpty())
                <tr>
                    <td colspan="13" class="text-center">Tidak ada item pada pesanan ini.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="row justify-content-end">
    <div class="col-md-5">
        <div class="mb-2">
            <label class="form-label fw-bold">Total Harga (tanpa PPN)</label>
            <input type="text" id="ttl-subtotal" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
            <small class="text-muted">Hanya menjumlahkan item yang sudah diterima; item pending pesanan tidak ikut dihitung
                (sesuai perilaku aslinya).</small>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Total Harga + PPN</label>
            <input type="text" id="ttl_trkasir" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format(round($subtotal * 1.11), 0, ',', '.') }}" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Diskon Faktur (pilih salah satu, lalu Enter)</label>
            <div class="input-group">
                <span class="input-group-text">%</span>
                <input type="text" id="diskon_faktur_persen" class="form-control text-end" placeholder="Diskon %">
                <span class="input-group-text">Rp</span>
                <input type="text" id="diskon_faktur_nominal" class="form-control text-end" placeholder="Nominal">
                <button type="button" class="btn btn-primary" id="btn-diskon-faktur-enter">Enter</button>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Total Tagihan</label>
            <input type="text" id="sisa_bayar" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format(round($subtotal * 1.11), 0, ',', '.') }}" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Cara Bayar</label>
            <select class="form-control" id="carabayar">
                <option value="KREDIT">KREDIT</option>
                <option value="LUNAS">TUNAI</option>
                <option value="KONSINYASI">KONSINYASI</option>
            </select>
        </div>
    </div>
</div>

<script>
    (function() {
        var kdTrbmasuk = document.getElementById('kd_trbmasuk').value;
        var kdOrders = document.getElementById('kd_orders').value;

        $(document).off('focus', '#tabel-receive-detail-pbf .edit-field').on('focus', '#tabel-receive-detail-pbf .edit-field', function() {
            $(this).data('original-value', $(this).val());
        });

        // Hitung ulang kolom Total secara langsung (di browser saja, tanpa AJAX) begitu
        // Qty Grosir/Diskon/HNA diketik, supaya pengguna langsung lihat perubahannya --
        // nilai final tetap disimpan lewat AJAX saat blur/Enter seperti biasa.
        $(document).off('input', '#tabel-receive-detail-pbf .edit-field[data-field="qtygrosir"], #tabel-receive-detail-pbf .edit-field[data-field="diskon"], #tabel-receive-detail-pbf .edit-field[data-field="hna"]')
            .on('input', '#tabel-receive-detail-pbf .edit-field[data-field="qtygrosir"], #tabel-receive-detail-pbf .edit-field[data-field="diskon"], #tabel-receive-detail-pbf .edit-field[data-field="hna"]', function() {
                var $row = $(this).closest('tr');
                var qtyGrosir = parseFloat($row.find('.edit-field[data-field="qtygrosir"]').val()) || 0;
                var diskon = parseFloat($row.find('.edit-field[data-field="diskon"]').val()) || 0;
                var hna = parseFloat($row.find('.edit-field[data-field="hna"]').val().replace(/\./g, '').replace(',', '.')) || 0;
                var total = Math.round(hna * (1 - diskon / 100) * qtyGrosir);
                $row.find('.col-total').text(total.toLocaleString('id-ID'));
            });

        $(document).off('keydown', '#tabel-receive-detail-pbf .edit-field').on('keydown', '#tabel-receive-detail-pbf .edit-field', function(e) {
            if (e.which === 13) { e.preventDefault(); $(this).trigger('blur'); }
        });

        $(document).off('change', '#tabel-receive-detail-pbf .edit-field').on('change', '#tabel-receive-detail-pbf .edit-field', function() {
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

            var numericFields = ['diskon', 'hna', 'konversi', 'qtygrosir'];
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
            form.append('no_batch_asal', $row.data('no-batch-asal'));
            form.append('value', val);
            form.append('_method', 'PUT');

            fetch("{{ route('inventory.trbmasukpbf.receive.detail.update') }}", {
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
                    $row.data('no-batch-asal', resp.no_batch);
                    $row.attr('data-no-batch-asal', resp.no_batch);
                    $row.find('.col-total').text(resp.total_text);
                    var $aksiTd = $row.find('td').last();
                    $aksiTd.attr('data-id-dtrbmasuk', resp.id_dtrbmasuk);
                    $aksiTd.html('<button type="button" class="btn btn-xs btn-danger btn-hapus-detail"><i class="fa fa-times"></i></button>');
                    document.getElementById('ttl-subtotal').value = resp.subtotal;
                    recomputeFooterTotalsPbf();
                });
        });

        $(document).off('click', '#tabel-receive-detail-pbf .btn-hapus-detail').on('click', '#tabel-receive-detail-pbf .btn-hapus-detail', function() {
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var $aksiTd = $row.find('td').last();

            var form = new FormData();
            form.append('id_dtrbmasuk', $aksiTd.data('id-dtrbmasuk'));
            form.append('kd_orders', kdOrders);
            form.append('kd_trbmasuk', kdTrbmasuk);
            form.append('_method', 'DELETE');

            fetch("{{ route('inventory.trbmasukpbf.receive.detail.destroy') }}", {
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
                    recomputeFooterTotalsPbf();
                });
        });

        $(document).off('click', '#tabel-receive-detail-pbf .btn-batalkan').on('click', '#tabel-receive-detail-pbf .btn-batalkan', function() {
            if (!confirm('Batalkan item ini? Item yang dibatalkan tidak bisa diterima lagi.')) return;

            var $btn = $(this);
            var $row = $btn.closest('tr');

            var form = new FormData();
            form.append('kd_barang', $row.data('kd-barang'));
            form.append('kd_orders', kdOrders);
            form.append('kd_trbmasuk', kdTrbmasuk);

            fetch("{{ route('inventory.trbmasukpbf.receive.detail.cancel') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                })
                .then(function(res) { return res.json(); })
                .then(function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal membatalkan item');
                        return;
                    }
                    if (typeof loadTabelDetail === 'function') loadTabelDetail();
                });
        });

        // Total Harga + PPN = Total Harga (tanpa PPN) x 1.11, mengikuti tbl_detail1.php:
        // $grandnya = format_rupiah($grandtotal * 1.11). Setiap kali Total Harga berubah
        // (item diedit/dihapus), Total Tagihan direset mengikuti Total Harga+PPN dan opsi
        // diskon faktur dibuka lagi -- diskon faktur yang sudah dikunci sebelumnya jadi basi
        // begitu isi transaksinya berubah.
        window.recomputeFooterTotalsPbf = function() {
            var subtotal = parseFloat(document.getElementById('ttl-subtotal').value.replace(/\./g, '')) || 0;
            var totalPpn = Math.round(subtotal * 1.11);
            document.getElementById('ttl_trkasir').value = totalPpn.toLocaleString('id-ID');
            document.getElementById('sisa_bayar').value = totalPpn.toLocaleString('id-ID');
            document.getElementById('diskon_faktur_persen').value = '';
            document.getElementById('diskon_faktur_nominal').value = '';
            document.getElementById('diskon_faktur_persen').disabled = false;
            document.getElementById('diskon_faktur_nominal').disabled = false;
        };

        // Cara Bayar dipindah ke sini (footer tabel), sama seperti tata letak aslinya --
        // Jatuh Tempo sendiri tetap di form header (elemen #groupJatuhTempo ada di halaman induk).
        document.getElementById('carabayar').addEventListener('change', function() {
            var groupJatuhTempo = document.getElementById('groupJatuhTempo');
            if (groupJatuhTempo) {
                groupJatuhTempo.style.display = this.value === 'LUNAS' ? 'none' : '';
            }
        });
        document.getElementById('carabayar').dispatchEvent(new Event('change'));

        // Diskon Faktur: HANYA salah satu (persen ATAU nominal) boleh diisi, mengikuti
        // legacy's #diskon_enter -- begitu di-Enter, dua field dikunci supaya tidak dobel.
        document.getElementById('btn-diskon-faktur-enter').addEventListener('click', function() {
            var totalPpn = parseFloat(document.getElementById('ttl_trkasir').value.replace(/\./g, '')) || 0;
            var persen = parseFloat(document.getElementById('diskon_faktur_persen').value) || 0;
            var nominal = parseFloat(document.getElementById('diskon_faktur_nominal').value.replace(/\./g, '')) || 0;

            if (persen > 0 && nominal > 0) {
                alert('Hanya dibolehkan 1 opsi diskon faktur !!!');
                return;
            }

            var totalTagihan = totalPpn;
            if (persen > 0) {
                totalTagihan = Math.ceil(totalPpn * (1 - persen / 100));
            } else if (nominal > 0) {
                totalTagihan = totalPpn - nominal;
            }

            document.getElementById('sisa_bayar').value = totalTagihan.toLocaleString('id-ID');
            document.getElementById('diskon_faktur_persen').disabled = true;
            document.getElementById('diskon_faktur_nominal').disabled = true;
        });
    })();
</script>
