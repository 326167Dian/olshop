@extends('inventory.layouts.app')

@section('header', 'Item Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Barang</h3>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex flex-wrap gap-1">
                <a class="btn btn-sm btn-success" href="{{ route('inventory.barang.create') }}">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>

            <div class="table-responsive">
                <table id="tabel-barang" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th style="text-align:center;">Rak Obat</th>
                            <th style="text-align:center;">Stok</th>
                            <th style="text-align:right;">Harga Jual</th>
                            <th>Zat Aktif</th>
                            <th>Komposisi dan Indikasi</th>
                            @if ($isPemilik)
                                <th style="white-space:nowrap;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditJenisobat" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rak Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select id="inputJenisobat" class="form-control">
                        <option value="">- Pilih Rak Obat -</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveJenisobat">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditZataktif" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Zat Aktif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea id="inputZataktif" class="form-control" rows="8"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveZataktif">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditIndikasi" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Komposisi dan Indikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea id="inputIndikasi" class="form-control" rows="8"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveIndikasi">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        var table = $('#tabel-barang').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.barang.data') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nm_barang', name: 'nm_barang' },
                { data: 'jenisobat', name: 'jenisobat', className: 'text-center' },
                { data: 'stok_barang', name: 'stok_barang', className: 'text-center' },
                { data: 'hrgjual_barang', name: 'hrgjual_barang' },
                { data: 'zataktif', name: 'zataktif' },
                { data: 'indikasi', name: 'indikasi' },
                @if ($isPemilik)
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                @endif
            ]
        });

        var currentIdBarang = null;

        $(document).on('click', '.btn-edit-jenisobat', function() {
            currentIdBarang = $(this).data('id');
            var currentValue = $(this).data('value') || '';
            var select = $('#inputJenisobat');
            if (select.find('option').length <= 1) {
                @foreach ($jenisObatList ?? [] as $jo)
                    select.append($('<option>', { value: '{{ $jo->jenisobat }}', text: '{{ $jo->jenisobat }}' }));
                @endforeach
            }
            select.val(currentValue);
            new bootstrap.Modal(document.getElementById('modalEditJenisobat')).show();
        });

        $('#btnSaveJenisobat').on('click', function() {
            if (!currentIdBarang) return;
            $.ajax({
                url: '{{ url('inventory/barang') }}/' + currentIdBarang + '/jenisobat',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    jenisobat: $('#inputJenisobat').val()
                },
                success: function() {
                    table.ajax.reload(null, false);
                    bootstrap.Modal.getInstance(document.getElementById('modalEditJenisobat')).hide();
                },
                error: function(xhr) {
                    alert(xhr.responseText || 'Gagal menyimpan perubahan.');
                }
            });
        });

        $(document).on('click', '.btn-edit-zataktif', function() {
            currentIdBarang = $(this).data('id');
            $('#inputZataktif').val($(this).data('value') || '');
            new bootstrap.Modal(document.getElementById('modalEditZataktif')).show();
        });

        $('#btnSaveZataktif').on('click', function() {
            if (!currentIdBarang) return;
            $.ajax({
                url: '{{ url('inventory/barang') }}/' + currentIdBarang + '/zataktif',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    zataktif: $('#inputZataktif').val()
                },
                success: function() {
                    table.ajax.reload(null, false);
                    bootstrap.Modal.getInstance(document.getElementById('modalEditZataktif')).hide();
                },
                error: function() {
                    alert('Gagal menyimpan perubahan.');
                }
            });
        });

        $(document).on('click', '.btn-edit-indikasi', function() {
            currentIdBarang = $(this).data('id');
            $('#inputIndikasi').val($(this).data('value') || '');
            new bootstrap.Modal(document.getElementById('modalEditIndikasi')).show();
        });

        $('#btnSaveIndikasi').on('click', function() {
            if (!currentIdBarang) return;
            $.ajax({
                url: '{{ url('inventory/barang') }}/' + currentIdBarang + '/indikasi',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    indikasi: $('#inputIndikasi').val()
                },
                success: function() {
                    table.ajax.reload(null, false);
                    bootstrap.Modal.getInstance(document.getElementById('modalEditIndikasi')).hide();
                },
                error: function() {
                    alert('Gagal menyimpan perubahan.');
                }
            });
        });
    });
</script>
@endpush
