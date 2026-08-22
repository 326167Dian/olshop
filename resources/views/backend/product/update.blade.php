@extends('backend.layouts.app')

@section('title', 'Edit Produk')

@section('header', 'Edit Data Produk')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Form Edit Produk</h3>
            </div>

            <form
                action="{{ route('product.update', ['product' => $product->id_barang, 'start' => $start, 'length' => $length]) }}"
                method="POST" enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="row">
                        {{-- Preview Gambar di Kiri --}}
                        <div class="col-md-4 text-center">
                            <label>Preview Gambar</label>
                            <div class="border p-2">

                                <img src="{{ asset('storage/' . ($product->image ?? 'default.png')) }}"
                                    id="preview-image" class="img-fluid img-thumbnail"
                                    style="height: 270px; width: auto;" alt="Preview Gambar">

                                <h6 class="mt-2 text-center">
                                    Terakhir di update oleh {{ $product->admin ? $product->admin->nama_lengkap : 'Tidak
                                    diketahui' }}
                                </h6>
                            </div>
                        </div>

                        {{-- Form Input di Kanan --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="gambar_produk">Gambar Produk</label>
                                <input type="file" name="gambar_produk" class="form-control-file" id="gambar_produk"
                                    oninput="document.getElementById('preview-image').src = this.value;">
                                @error('gambar_produk')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="nm_barang">Nama Barang</label>
                                <input type="text" name="nm_barang" class="form-control"
                                    value="{{ old('nm_barang', $product->nm_barang) }}" readonly>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' :
                                        '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ?
                                        'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="category_id">Kategori</label>
                                <select name="category_id" class="form-control">
                                    <option value="" disabled {{ old('category_id', $product->category_id) ? '' :
                                        'selected' }}>
                                        Pilih Kategori
                                    </option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) ==
                                        $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="diskon">Diskon</label>
                                <input type="number" name="diskon" class="form-control"
                                    value="{{ old('diskon', $product->diskon) }}">
                                @error('diskon')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="promosi">Produk Promosi</label>
                                <select name="promosi" class="form-control">
                                    <option value="" disabled {{ old('promosi', $product->promosi ?? 'standar') == '' ?
                                        'selected' : '' }}>
                                        Pilih Produk Promosi
                                    </option>
                                    <option value="terlaris" {{ old('promosi', $product->promosi ?? 'standar') ==
                                        'terlaris' ? 'selected' : '' }}>
                                        Terlaris
                                    </option>
                                    <option value="diskon" {{ old('promosi', $product->promosi ?? 'standar') == 'diskon'
                                        ? 'selected' : '' }}>
                                        Diskon
                                    </option>
                                    <option value="standar" {{ old('promosi', $product->promosi ?? 'standar') ==
                                        'standar' ? 'selected' : '' }}>
                                        Standar
                                    </option>
                                </select>

                                @error('promosi')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
        </div>


        <div class="card-footer">
            <a href="{{ route('product.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-warning ">
                <i class="fas fa-save"></i> Update
            </button>
        </div>
        </form>
    </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    document.getElementById('gambar_produk').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush