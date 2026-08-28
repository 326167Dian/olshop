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
                                    {{ $row->tgl_followup->format('d-m-Y H:i') }}
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
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn-followup')) {
            return;
        }
        var id = e.target.dataset.id;
        var cell = document.querySelector('.tgl-followup-cell[data-id="' + id + '"]');
        fetch('{{ url('inventory/swamedikasi') }}/' + id + '/followup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    cell.textContent = data.tgl_followup;
                }
            });
    });
</script>
@endpush
