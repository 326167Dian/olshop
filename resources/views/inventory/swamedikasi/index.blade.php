@extends('inventory.layouts.app')

@section('header', 'Swamedikasi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Swamedikasi</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Foto Riwayat</th>
                        <th>Diagnosa</th>
                        <th>Tindakan</th>
                        <th>Saran Konsultasi</th>
                        <th>Tgl Follow Up</th>
                        <th>Follow Up oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayat as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->pelanggan->nm_pelanggan ?? '-' }}</td>
                            <td>{{ $row->tgl?->format('d-m-Y') }}</td>
                            <td>
                                @if ($row->foto)
                                    <img src="{{ asset('storage/' . $row->foto) }}"
                                        class="img-zoom-thumb" data-full="{{ asset('storage/' . $row->foto) }}"
                                        style="width:45px;height:45px;object-fit:cover;border-radius:4px;cursor:pointer;"
                                        alt="Foto riwayat">
                                @endif
                            </td>
                            <td>{{ $row->diagnosa }}</td>
                            <td>
                                @if ($row->obat->isNotEmpty())
                                    @foreach ($row->obat as $ob)
                                        {{ $ob->nm_barang }}@if ($ob->aturan_pakai) - {{ $ob->aturan_pakai }} @endif<br>
                                    @endforeach
                                @else
                                    {{ $row->tindakan }}
                                @endif
                            </td>
                            <td>{{ $row->followup }}</td>
                            <td class="tgl-followup-cell" data-id="{{ $row->id }}">
                                @if ($row->tgl_followup)
                                    <div>{{ $row->tgl_followup->format('d-m-Y H:i') }}</div>
                                    @if ($row->foto2)
                                        <img src="{{ asset('storage/' . $row->foto2) }}"
                                            class="img-zoom-thumb" data-full="{{ asset('storage/' . $row->foto2) }}"
                                            style="width:45px;height:45px;object-fit:cover;border-radius:4px;cursor:pointer;margin-top:4px;"
                                            alt="Foto follow up">
                                    @endif
                                @else
                                    <button type="button" data-id="{{ $row->id }}" class="btn-followup btn btn-danger btn-xs">
                                        Klik untuk followup
                                    </button>
                                @endif
                            </td>
                            <td>{{ $row->followup_by }}</td>
                            <td>
                                <a href="{{ route('inventory.swamedikasi.edit', $row->id) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('inventory.swamedikasi.destroy', $row->id) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id }}', 'riwayat swamedikasi ini')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Follow Up -->
    <div class="modal fade" id="modalFollowup" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="followup-riwayat-id">
                    <div class="mb-3">
                        <label class="form-label">Foto (opsional, maks 1 MB)</label>
                        <input type="file" id="followup-foto2" class="form-control" accept="image/*">
                        <small class="text-muted">Boleh dikosongkan, follow up tetap bisa disimpan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-simpan-followup">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var modalFollowupEl = document.getElementById('modalFollowup');
    var modalFollowup = new bootstrap.Modal(modalFollowupEl);

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-followup')) {
            document.getElementById('followup-riwayat-id').value = e.target.dataset.id;
            document.getElementById('followup-foto2').value = '';
            modalFollowup.show();
            return;
        }

        if (e.target.classList.contains('img-zoom-thumb')) {
            Swal.fire({
                imageUrl: e.target.dataset.full,
                imageAlt: 'Foto',
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
            });
        }
    });

    document.getElementById('btn-simpan-followup').addEventListener('click', function() {
        var id = document.getElementById('followup-riwayat-id').value;
        var fileInput = document.getElementById('followup-foto2');
        var file = fileInput.files[0];

        if (file && file.size > 1024 * 1024) {
            Swal.fire({ icon: 'error', title: 'Ukuran foto terlalu besar', text: 'Maksimal 1 MB.' });
            return;
        }

        var cell = document.querySelector('.tgl-followup-cell[data-id="' + id + '"]');
        var formData = new FormData();
        if (file) {
            formData.append('foto2', file);
        }

        fetch('{{ url('inventory/swamedikasi') }}/' + id + '/followup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    var html = '<div>' + data.tgl_followup + '</div>';
                    if (data.foto2_url) {
                        html += '<img src="' + data.foto2_url + '" class="img-zoom-thumb" data-full="' +
                            data.foto2_url + '" style="width:45px;height:45px;object-fit:cover;border-radius:4px;cursor:pointer;margin-top:4px;" alt="Foto follow up">';
                    }
                    cell.innerHTML = html;
                    modalFollowup.hide();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Follow up gagal disimpan.' });
                }
            });
    });
</script>
@endpush
