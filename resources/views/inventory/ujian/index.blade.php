@extends('inventory.layouts.app')

@section('header', 'Ujian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengerjaan Ujian</h3>
        </div>
        <div class="card-body">
            @if ($isPemilik)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>Anda login sebagai <strong>pemilik</strong>. Kelola soal melalui tombol berikut.</div>
                    <a href="{{ route('inventory.ujian.kelola') }}" class="btn btn-success btn-sm">Kelola Soal</a>
                </div>
            @endif

            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Ujian</label>
                    <select name="ujian_id" id="ujian_id_filter" class="form-control" required onchange="document.getElementById('durasi_filter').value = this.options[this.selectedIndex].dataset.durasi || ''">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach ($daftarUjian as $u)
                            <option value="{{ $u->id_soal }}" data-durasi="{{ $u->durasi }}" {{ $selectedId === $u->id_soal ? 'selected' : '' }}>{{ $u->nm_ujian }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durasi (menit)</label>
                    <input type="text" id="durasi_filter" class="form-control" value="{{ $durasiMenit }}" readonly>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Tampilkan Soal</button>
                    @if ($isPemilik)
                        <a href="{{ route('inventory.ujian.hasil') }}" class="btn btn-success">Hasil Ujian</a>
                    @endif
                </div>
            </form>

            @if ($selectedId <= 0)
                <div class="alert alert-info">Silakan pilih Nama Ujian terlebih dahulu.</div>
            @elseif (count($daftarSoal) === 0)
                <div class="alert alert-info">Belum ada soal ujian yang bisa dikerjakan.</div>
            @else
                <div class="alert alert-danger">
                    Nama Ujian: <strong>{{ $ujianAktif->nm_ujian }}</strong><br>
                    Batas Waktu Ujian: <strong>{{ $durasiMenit }} menit</strong><br>
                    Sisa Waktu: <strong><span id="timer-countdown">--:--</span></strong>
                </div>

                <form method="POST" action="{{ route('inventory.ujian.submit') }}" id="form-ujian">
                    @csrf
                    <input type="hidden" name="ujian_id" value="{{ $selectedId }}">
                    <input type="hidden" name="exam_started_at" value="{{ now()->timestamp }}">
                    <input type="hidden" name="exam_duration_seconds" value="{{ $durasiMenit * 60 }}">

                    @foreach ($daftarSoal as $index => $soal)
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="mb-2"><strong>{{ $index + 1 }}. </strong>{!! $soal->pertanyaan_html !!}</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jawaban[{{ $soal->id }}]" value="a" id="soal{{ $soal->id }}a" required>
                                    <label class="form-check-label" for="soal{{ $soal->id }}a">{{ $soal->opsi_a }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jawaban[{{ $soal->id }}]" value="b" id="soal{{ $soal->id }}b">
                                    <label class="form-check-label" for="soal{{ $soal->id }}b">{{ $soal->opsi_b }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jawaban[{{ $soal->id }}]" value="c" id="soal{{ $soal->id }}c">
                                    <label class="form-check-label" for="soal{{ $soal->id }}c">{{ $soal->opsi_c }}</label>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary">Kirim Jawaban</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Kembali</a>
                </form>

                @push('scripts')
                <script>
                    (function() {
                        var durationSeconds = {{ (int) ($durasiMenit * 60) }};
                        var endAt = Math.floor(Date.now() / 1000) + durationSeconds;
                        var countdownEl = document.getElementById('timer-countdown');
                        var form = document.getElementById('form-ujian');

                        function formatTime(seconds) {
                            var m = Math.floor(seconds / 60);
                            var s = seconds % 60;
                            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                        }

                        function tick() {
                            var now = Math.floor(Date.now() / 1000);
                            var remain = endAt - now;

                            if (remain <= 0) {
                                countdownEl.textContent = '00:00';
                                alert('Waktu ujian habis. Jawaban akan dikirim otomatis.');
                                form.submit();
                                return;
                            }

                            countdownEl.textContent = formatTime(remain);
                        }

                        tick();
                        var tickInterval = setInterval(tick, 1000);
                        form.addEventListener('submit', function() { clearInterval(tickInterval); });

                        var autosaveTimer = null;

                        function autosave() {
                            var formData = new FormData(form);
                            fetch('{{ route('inventory.ujian.autosave') }}', {
                                method: 'POST',
                                body: formData
                            }).catch(function() {});
                        }

                        form.addEventListener('change', function(e) {
                            if (e.target && e.target.name && e.target.name.indexOf('jawaban[') === 0) {
                                clearTimeout(autosaveTimer);
                                autosaveTimer = setTimeout(autosave, 500);
                            }
                        });

                        var autosaveInterval = setInterval(autosave, 20000);
                        form.addEventListener('submit', function() { clearInterval(autosaveInterval); });
                    })();
                </script>
                @endpush
            @endif
        </div>
    </div>
@endsection
