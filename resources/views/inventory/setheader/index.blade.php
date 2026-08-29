@extends('inventory.layouts.app')

@section('header', 'Header Struk')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Setting Header Cetak Struk</h3>
        </div>

        <form action="{{ route('inventory.setheader.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <h5 class="fw-bold">Header Struk</h5>
                <hr>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="satu">Baris 1 (Nama Apotek)</label>
                        <input type="text" name="satu" id="satu"
                            class="form-control @error('satu') is-invalid @enderror"
                            value="{{ old('satu', $setheader->satu) }}">
                        @error('satu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="dua">Baris 2 (Alamat Jalan)</label>
                        <input type="text" name="dua" id="dua"
                            class="form-control @error('dua') is-invalid @enderror"
                            value="{{ old('dua', $setheader->dua) }}">
                        @error('dua') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tiga">Baris 3 (Kelurahan/Kecamatan/Kota)</label>
                        <input type="text" name="tiga" id="tiga"
                            class="form-control @error('tiga') is-invalid @enderror"
                            value="{{ old('tiga', $setheader->tiga) }}">
                        @error('tiga') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="empat">Baris 4 (Nama Apoteker)</label>
                        <input type="text" name="empat" id="empat"
                            class="form-control @error('empat') is-invalid @enderror"
                            value="{{ old('empat', $setheader->empat) }}">
                        @error('empat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="lima">Baris 5 (No. SIA)</label>
                        <input type="text" name="lima" id="lima"
                            class="form-control @error('lima') is-invalid @enderror"
                            value="{{ old('lima', $setheader->lima) }}">
                        @error('lima') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="enam">Baris 6 (No. Telp)</label>
                        <input type="text" name="enam" id="enam"
                            class="form-control @error('enam') is-invalid @enderror"
                            value="{{ old('enam', $setheader->enam) }}">
                        @error('enam') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tujuh">Baris 7 (No. SIPA)</label>
                        <input type="text" name="tujuh" id="tujuh"
                            class="form-control @error('tujuh') is-invalid @enderror"
                            value="{{ old('tujuh', $setheader->tujuh) }}">
                        @error('tujuh') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="duabelas">Alamat Apoteker</label>
                        <input type="text" name="duabelas" id="duabelas"
                            class="form-control @error('duabelas') is-invalid @enderror"
                            value="{{ old('duabelas', $setheader->duabelas) }}">
                        @error('duabelas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tigabelas">Kota</label>
                        <input type="text" name="tigabelas" id="tigabelas"
                            class="form-control @error('tigabelas') is-invalid @enderror"
                            value="{{ old('tigabelas', $setheader->tigabelas) }}">
                        @error('tigabelas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="empatbelas">Minimum Exp. Date (Hari)</label>
                        <input type="number" name="empatbelas" id="empatbelas" min="0"
                            class="form-control @error('empatbelas') is-invalid @enderror"
                            value="{{ old('empatbelas', $setheader->empatbelas) }}">
                        @error('empatbelas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="logo">Logo Header</label>
                        <input type="file" name="logo" id="logo"
                            class="form-control @error('logo') is-invalid @enderror">
                        @error('logo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        @if ($setheader->logo_url)
                            <img src="{{ $setheader->logo_url }}" alt="Logo Header"
                                class="mt-2" style="max-width: 100px; border: 1px solid #ccc;">
                        @endif
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tandatangan">Tanda Tangan</label>
                        <input type="file" name="tandatangan" id="tandatangan"
                            class="form-control @error('tandatangan') is-invalid @enderror">
                        @error('tandatangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        @if ($setheader->tandatangan_url)
                            <img src="{{ $setheader->tandatangan_url }}" alt="Tanda Tangan"
                                class="mt-2" style="max-width: 100px; border: 1px solid #ccc;">
                        @endif
                    </div>
                </div>

                <h5 class="fw-bold mt-3">Footer Struk</h5>
                <hr>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="delapan">Baris 1</label>
                        <input type="text" name="delapan" id="delapan"
                            class="form-control @error('delapan') is-invalid @enderror"
                            value="{{ old('delapan', $setheader->delapan) }}">
                        @error('delapan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="sembilan">Baris 2</label>
                        <input type="text" name="sembilan" id="sembilan"
                            class="form-control @error('sembilan') is-invalid @enderror"
                            value="{{ old('sembilan', $setheader->sembilan) }}">
                        @error('sembilan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="sepuluh">Baris 3</label>
                        <input type="text" name="sepuluh" id="sepuluh"
                            class="form-control @error('sepuluh') is-invalid @enderror"
                            value="{{ old('sepuluh', $setheader->sepuluh) }}">
                        @error('sepuluh') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="sebelas">Baris 4</label>
                        <input type="text" name="sebelas" id="sebelas"
                            class="form-control @error('sebelas') is-invalid @enderror"
                            value="{{ old('sebelas', $setheader->sebelas) }}">
                        @error('sebelas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
