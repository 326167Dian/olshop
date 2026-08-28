@extends('inventory.layouts.app')

@section('header', $label)

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="mb-2">Modul "{{ $label }}" belum tersedia</h5>
            <p class="mb-0 text-muted">
                Halaman untuk modul <strong>{{ $label }}</strong> ini sedang dalam proses adaptasi dari sistem
                inventory lama ke Laravel. Anda memiliki akses ke modul ini, namun halamannya akan menyusul
                pada tahap pengembangan berikutnya.
            </p>
        </div>
    </div>
@endsection
