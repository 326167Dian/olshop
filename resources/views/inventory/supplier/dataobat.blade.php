@extends('inventory.layouts.app')

@section('header', 'Data Obat Supplier')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Data Obat Supplier: {{ $supplier->nm_supplier }}</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.supplier.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <form method="POST" action="{{ route('inventory.supplier.simpan-barang', $supplier->id_supplier) }}"
                class="row g-2 align-items-end mb-4">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Cari Barang</label>
                    <div class="autocomplete-wrapper position-relative">
                        <input type="hidden" name="kd_barang" class="obat-kd">
                        <input type="text" class="form-control obat-nama" placeholder="Ketik nama obat untuk cari"
                            autocomplete="off">
                        <div class="autocomplete-panel list-group position-absolute w-100 shadow"
                            style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info">+ Tambah Data Obat</button>
                </div>
            </form>

            <table class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Hrg Beli</th>
                        <th>Hrg Jual</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplier->barang as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->barang->kd_barang ?? '-' }}</td>
                            <td>{{ $row->barang->nm_barang ?? '-' }}</td>
                            <td>{{ $row->barang->sat_barang ?? '-' }}</td>
                            <td class="text-end">{{ number_format($row->hrgsat_brgsupplier, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->barang->hrgjual_barang ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('inventory.supplier.hapus-barang', $row->id_brgsup) }}"
                                    method="POST" class="d-inline" id="delete-brgsup-{{ $row->id_brgsup }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-brgsup-{{ $row->id_brgsup }}', '{{ $row->barang->nm_barang ?? 'data obat ini' }}')"
                                        class="btn btn-danger btn-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data obat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        var searchUrl = '{{ route('inventory.supplier.obat-search') }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        var delayTimer = null;

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('autocomplete-item')) {
                var wrapper = e.target.closest('.autocomplete-wrapper');
                wrapper.querySelector('.obat-nama').value = e.target.dataset.nama;
                wrapper.querySelector('.obat-kd').value = e.target.dataset.kode;
                wrapper.querySelector('.autocomplete-panel').style.display = 'none';
                return;
            }

            if (!e.target.closest('.autocomplete-wrapper')) {
                document.querySelectorAll('.autocomplete-panel').forEach(function(p) { p.style.display = 'none'; });
            }
        });

        document.addEventListener('input', function(e) {
            if (!e.target.classList.contains('obat-nama')) {
                return;
            }

            var input = e.target;
            var wrapper = input.closest('.autocomplete-wrapper');
            wrapper.querySelector('.obat-kd').value = '';
            var panel = wrapper.querySelector('.autocomplete-panel');
            var keyword = input.value.trim();

            clearTimeout(delayTimer);
            if (keyword.length < 2) {
                panel.style.display = 'none';
                return;
            }

            delayTimer = setTimeout(function() {
                var form = new FormData();
                form.append('query', keyword);

                fetch(searchUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: form,
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        panel.innerHTML = '';
                        if (!data || data.length === 0) {
                            panel.innerHTML = '<div class="list-group-item">Obat tidak ditemukan</div>';
                            panel.style.display = 'block';
                            return;
                        }
                        data.forEach(function(item) {
                            var div = document.createElement('div');
                            div.className = 'list-group-item list-group-item-action autocomplete-item';
                            div.style.cursor = 'pointer';
                            div.dataset.kode = item.kd_barang;
                            div.dataset.nama = item.nm_barang;
                            div.textContent = item.nm_barang;
                            panel.appendChild(div);
                        });
                        panel.style.display = 'block';
                    });
            }, 300);
        });
    })();
</script>
@endpush
