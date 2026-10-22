@extends('frontend.layouts.index')

@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title d-flex justify-content-between align-items-center flex-wrap"
                    style="gap: 10px;">
                    <h2 class="title mb-0">Testimoni Pelanggan</h2>
                    <a href="{{ route('customer.testimoni.form') }}" class="primary-btn btn-sm" style="border-radius: 6px;">
                        <i class="fa fa-pen me-1"></i> Tulis Testimoni
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif

                @if ($testimonis->isEmpty())
                    <p class="mt-3">Belum ada testimoni dari pelanggan.</p>
                @endif

                <div class="row mt-3">
                    @foreach ($testimonis as $testimoni)
                    <div class="col-md-4 col-sm-6 col-xs-12" data-aos="fade-up">
                        <div class="d-flex flex-column shadow"
                            style="height: 100%; border-radius: 8px; padding: 20px; background-color: #F6F7F8;">
                            <div style="color: #FFC107; margin-bottom: 8px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa {{ $i <= $testimoni->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                @endfor
                            </div>
                            <i class="fa fa-quote-left" style="font-size: 20px; color: #2EAD67; margin-bottom: 10px;"></i>
                            <p style="flex: 1;">{{ $testimoni->isi }}</p>
                            <div class="mt-3">
                                <strong>{{ $testimoni->user->name ?? 'Pelanggan' }}</strong>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="text-center mt-4">
                    {{ $testimonis->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /SECTION -->
@endsection
