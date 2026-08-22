@extends('frontend.layouts.index')

@section('content')
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Masukan Alamat</h4>
                        <hr>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('order.update-ongkir') }}" method="POST">
                            @csrf

                            {{-- Alamat --}}
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Anda</label>
                                <input type="text" name="alamat" id="search" class="form-control"
                                    placeholder="Alamat Lengkap" value="{{ $customer->alamat }}" readonly>
                                <ul id="suggestions" class="list-group mt-1"></ul>
                            </div>

                            <div class="mb-3">
                                <label for="no_tlp" class="form-label">No Telepon Anda</label>
                                <input type="text" name="no_tlp" id="no_tlp" class="form-control"
                                    placeholder="No Telepon" value="{{ $customer->no_tlp }}" readonly>
                                <ul id="suggestions" class="list-group mt-1"></ul>
                            </div>

                            {{-- Tipe Layanan Pengiriman --}}
                            <div class="mb-3">
                                <label class="form-label">Tipe Layanan Pengiriman : <strong>{{ $jenisLayanan
                                        }}</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="layanan_ongkir" id="reguler"
                                        value="Reguler" {{ $jenisLayanan==='Reguler' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="reguler">
                                        Reguler (sesuai jam pengiriman)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="layanan_ongkir" id="instan"
                                        value="Instan" {{ $jenisLayanan==='Instan' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="instan">
                                        Instan (langsung dikirim setelah pembayaran)
                                    </label>
                                </div>

                                <strong>Note</strong>
                                <div class="alert alert-info mt-2" role="alert">
                                    {!! $companySetting->catatan ?? 'Tidak ada catatan pengiriman dari perusahaan.'
                                    !!}
                                </div>

                                {{-- Kirim data tersembunyi tetap ke controller --}}
                                <input type="hidden" name="layanan_pengiriman" value="{{ $jenisLayanan }}">
                            </div>

                            <br><br>
                            <button type="submit" class="primary-btn">Select Payment</button>
                        </form>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>
@endsection