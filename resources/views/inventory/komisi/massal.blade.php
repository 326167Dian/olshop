@extends('inventory.layouts.app')

@section('header', 'Atur Komisi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Atur Komisi (Input Massal)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-danger mb-2" href="{{ route('inventory.komisi.index') }}">Kembali</a>
            <p class="text-muted small">Barang yang tampil: stok &gt; 0 dan margin (Harga Jual - Harga Beli) / Harga Beli
                &gt; 50%. Isi Komisi langsung tersimpan otomatis.</p>

            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th style="text-align:center;">Satuan</th>
                        <th style="text-align:right;">Harga Beli</th>
                        <th style="text-align:right;">Harga Jual</th>
                        <th style="text-align:right;">Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barang as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->kd_barang }}</td>
                            <td>{{ $row->nm_barang }}</td>
                            <td class="text-center">{{ $row->sat_barang }}</td>
                            <td class="text-end">{{ number_format($row->hrgsat_barang, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->hrgjual_barang, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <input type="number" min="0" step="1" class="form-control input-komisi-massal"
                                    style="text-align:right;" data-id-barang="{{ $row->id_barang }}"
                                    value="{{ $row->komisi > 0 ? $row->komisi : '' }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#example1 tbody').on('focus', '.input-komisi-massal', function() {
            $(this).data('original-value', $(this).val());
        });

        $('#example1 tbody').on('change', '.input-komisi-massal', function() {
            var $input = $(this);
            var originalValue = $input.data('original-value');
            var idBarang = $input.data('id-barang');
            var komisi = $input.val().trim();

            if (komisi !== '' && (isNaN(komisi) || parseFloat(komisi) < 0)) {
                alert('Komisi harus berupa angka dan tidak boleh negatif');
                $input.val(originalValue);
                return;
            }

            $.ajax({
                url: '{{ route('inventory.komisi.massal-update') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_barang: idBarang,
                    komisi: komisi === '' ? 0 : komisi
                },
                success: function(resp) {
                    if (resp.status !== 'ok') {
                        alert(resp.message || 'Gagal menyimpan komisi');
                        $input.val(originalValue);
                        return;
                    }
                    $input.val(resp.komisi > 0 ? resp.komisi : '');
                },
                error: function(xhr) {
                    var msg = 'Gagal menyimpan komisi';
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed && parsed.message) {
                            msg = parsed.message;
                        }
                    } catch (e) {}
                    alert(msg);
                    $input.val(originalValue);
                }
            });
        });
    });
</script>
@endpush
