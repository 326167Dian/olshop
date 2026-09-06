<script>
    function formatRupiah(v) { v = Math.round(parseFloat(v) || 0); return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function unformatRupiah(v) { return parseFloat(String(v).replace(/\./g, '')) || 0; }

    var rowIndex = $('#dynamic-field tr.row-obat').length;

    function rowTemplate() {
        rowIndex++;
        return `
            <tr class="row-obat">
                <td class="text-center nomor">${rowIndex}.</td>
                <td>
                    <div class="autocomplete-wrapper" style="position:relative">
                        <input type="hidden" name="obat_id[]" class="obat-id">
                        <input type="hidden" name="obat_kd[]" class="obat-kd">
                        <input type="text" name="obat_nama[]" class="form-control obat-nama" placeholder="Nama obat (ketik lalu Enter)">
                        <div class="autocomplete-panel"></div>
                    </div>
                </td>
                <td><input type="number" name="qty[]" class="form-control qty" min="1" value="1"></td>
                <td><input type="text" name="sat_barang[]" class="form-control satuan" readonly></td>
                <td><input type="number" step="1" name="hrgjual[]" class="form-control hrgjual" style="text-align:right"></td>
                <td><input type="text" name="subtotal[]" class="form-control subtotal" readonly style="text-align:right"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-hapus-row"><i class="fa fa-trash"></i></button>
                </td>
            </tr>`;
    }

    $('#btn-tambah-row').on('click', function () {
        $('#dynamic-field').append(rowTemplate());
    });

    function renumberRows() {
        $('#dynamic-field tr.row-obat').each(function (i) {
            $(this).find('.nomor').text((i + 1) + '.');
        });
    }

    $(document).on('click', '.btn-hapus-row', function () {
        var $row = $(this).closest('tr');
        var idbundleDetail = $(this).data('idbundle_detail');

        if (idbundleDetail) {
            if (!confirm('Hapus item ini dari paket?')) return;
            $.ajax({
                url: '{{ url("inventory/bundle/detail") }}/' + idbundleDetail,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function () {
                    $row.remove();
                    renumberRows();
                    hitungTotal();
                }
            });
        } else {
            $row.remove();
            renumberRows();
            hitungTotal();
        }
    });

    function hitungTotal() {
        var total = 0;
        $('#dynamic-field tr.row-obat').each(function () {
            total += unformatRupiah($(this).find('.subtotal').val());
        });
        $('#total-item-display').text(formatRupiah(total));
        return total;
    }

    function recalcRow($row) {
        var qty = parseFloat($row.find('.qty').val()) || 0;
        var hrg = unformatRupiah($row.find('.hrgjual').val());
        $row.find('.subtotal').val(formatRupiah(qty * hrg));
        hitungTotal();
    }

    $(document).on('change keyup', '.qty, .hrgjual', function () {
        recalcRow($(this).closest('tr'));
    });

    // Autocomplete pencarian item
    var selectedIndex = -1;
    var delayTimer;

    $(document).on('keyup', '.obat-nama', function (e) {
        if (e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13) return;

        clearTimeout(delayTimer);
        var input = this;

        delayTimer = setTimeout(function () {
            var $wrapper = $(input).closest('.autocomplete-wrapper');
            var keyword = $(input).val().trim();
            var panel = $wrapper.find('.autocomplete-panel');

            if (keyword.length < 2) { panel.hide().empty(); return; }

            $.ajax({
                url: '{{ route("inventory.bundle.item-search") }}',
                type: 'POST',
                dataType: 'json',
                data: { _token: '{{ csrf_token() }}', query: keyword },
                success: function (data) {
                    selectedIndex = -1;
                    panel.empty();

                    if (!data || data.length === 0) {
                        panel.append('<div class="autocomplete-empty" style="padding:6px;">Obat tidak ditemukan</div>');
                        panel.show();
                        return;
                    }

                    data.forEach(function (item) {
                        panel.append(`<div class="autocomplete-item" style="padding:6px; cursor:pointer;"
                            data-id="${item.id_barang}" data-kode="${item.kd_barang}" data-nama="${item.nm_barang}"
                            data-satuan="${item.sat_barang}" data-hrgjual="${item.hrgjual_barang}">${item.nm_barang}</div>`);
                    });

                    panel.css({ position: 'absolute', background: '#fff', border: '1px solid #ccc', 'z-index': 999, width: '100%', 'max-height': '220px', 'overflow-y': 'auto' });
                    panel.show();
                }
            });
        }, 300);
    });

    $(document).on('click', '.autocomplete-item', function () {
        var $item = $(this);
        var $row = $item.closest('.row-obat');

        $row.find('.obat-nama').val($item.data('nama'));
        $row.find('.obat-id').val($item.data('id'));
        $row.find('.obat-kd').val($item.data('kode'));
        $row.find('.satuan').val($item.data('satuan'));
        $row.find('.hrgjual').val($item.data('hrgjual'));
        $row.find('.autocomplete-panel').hide();

        recalcRow($row);
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('.autocomplete-wrapper').length) {
            $('.autocomplete-panel').hide();
        }
    });

    $(document).on('keydown', '.obat-nama', function (e) {
        if (e.keyCode == 13) e.preventDefault();
    });

    $('#form-bundle').on('submit', function (e) {
        var totalItem = hitungTotal();
        var hrgjualBundle = unformatRupiah($('#hrgjual_bundle').val());

        if (totalItem !== hrgjualBundle) {
            e.preventDefault();
            alert('Harga Jual Bundling harus sama dengan Total Item (Rp. ' + formatRupiah(totalItem) + ').');
        }
    });

    hitungTotal();
</script>
