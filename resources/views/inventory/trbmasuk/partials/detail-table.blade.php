@php
    $mode = $mode ?? 'tbm';
    $baseUrl = $mode === 'byrkredit' ? url('inventory/byrkredit/detail') : url('inventory/trbmasuk/detail');
    $subtotal = $detail->sum('hrgttl_dtrbmasuk');
@endphp
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

<div class="row justify-content-end">
    <div class="col-md-5">
        <div class="mb-2">
            <label class="form-label fw-bold">Sub Total</label>
            <input type="text" id="ttl_trkasir" class="form-control text-end fw-bold" style="background:#000; color:#fff;"
                value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
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
                value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
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

            fetch('{{ $baseUrl }}/' + id + '/qty', {
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

            fetch('{{ $baseUrl }}/' + id, {
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

        // Cara Bayar (footer) mengendalikan tampilnya Jatuh Tempo di form header --
        // elemen #groupJatuhTempo ada di halaman induk (create/byrkredit-edit), bukan di partial ini.
        document.getElementById('carabayar').addEventListener('change', function() {
            var groupJatuhTempo = document.getElementById('groupJatuhTempo');
            if (groupJatuhTempo) {
                groupJatuhTempo.style.display = this.value === 'LUNAS' ? 'none' : '';
            }
        });
        document.getElementById('carabayar').dispatchEvent(new Event('change'));

        // Diskon Faktur: HANYA salah satu (persen ATAU nominal) boleh diisi -- begitu
        // di-Enter, dua field dikunci supaya tidak dobel (mengikuti legacy's #diskon_enter).
        document.getElementById('btn-diskon-faktur-enter').addEventListener('click', function() {
            var subTotal = parseFloat(document.getElementById('ttl_trkasir').value.replace(/\./g, '')) || 0;
            var persen = parseFloat(document.getElementById('diskon_faktur_persen').value) || 0;
            var nominal = parseFloat(document.getElementById('diskon_faktur_nominal').value.replace(/\./g, '')) || 0;

            if (persen > 0 && nominal > 0) {
                alert('Hanya dibolehkan 1 opsi diskon faktur !!!');
                return;
            }

            var totalTagihan = subTotal;
            if (persen > 0) {
                totalTagihan = Math.ceil(subTotal * (1 - persen / 100));
            } else if (nominal > 0) {
                totalTagihan = subTotal - nominal;
            }

            document.getElementById('sisa_bayar').value = totalTagihan.toLocaleString('id-ID');
            document.getElementById('diskon_faktur_persen').disabled = true;
            document.getElementById('diskon_faktur_nominal').disabled = true;
        });
    })();
</script>
