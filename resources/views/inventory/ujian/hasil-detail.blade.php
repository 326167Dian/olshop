@extends('inventory.layouts.app')

@section('header', 'Detail Hasil Ujian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Hasil Ujian</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('inventory.ujian.hasil', ['ujian_id' => $hasil->ujian_id]) }}" class="btn btn-secondary mb-3">&laquo; Kembali ke Hasil Ujian</a>

            <p><strong>Nama Peserta Ujian</strong>: {{ $hasil->nama_lengkap }}</p>
            <p><strong>Nama Ujian</strong>: {{ $hasil->nama_ujian }}</p>
            <hr>

            @foreach ($daftarSoal as $index => $soal)
                @php
                    $userAnswer = isset($jawabanUser[$soal->id]) ? strtolower(trim((string) $jawabanUser[$soal->id])) : '';
                    $correctAnswer = strtolower(trim((string) $soal->jawaban_benar));
                    $opsi = ['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c];

                    if ($userAnswer === '') {
                        $statusLabel = 'Tidak Dijawab';
                        $statusColor = '#f0ad4e';
                    } elseif ($userAnswer === $correctAnswer) {
                        $statusLabel = 'Benar';
                        $statusColor = '#5cb85c';
                    } else {
                        $statusLabel = 'Salah';
                        $statusColor = '#d9534f';
                    }
                @endphp
                <div class="card mb-2">
                    <div class="card-body">
                        <div><strong>{{ $index + 1 }}. </strong>{!! $soal->pertanyaan_html !!}</div>
                        <ul class="list-unstyled ps-3 mt-2">
                            @foreach ($opsi as $letter => $teks)
                                <li style="{{ $letter === $userAnswer ? 'font-weight:bold;' : '' }}">
                                    {{ $letter === $userAnswer ? 'X ' : '' }}{{ $teks }}
                                </li>
                            @endforeach
                        </ul>
                        <p class="mb-0">Jawaban: <strong style="color:{{ $statusColor }};">{{ $statusLabel }}</strong>
                            @if ($statusLabel === 'Salah' && isset($opsi[$correctAnswer]))
                                <span class="text-muted"> (Jawaban benar: {{ $opsi[$correctAnswer] }})</span>
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach

            <div class="alert alert-info">
                <strong>Total Soal:</strong> {{ $hasil->total_soal }} &nbsp;|&nbsp;
                <strong>Jawaban Benar:</strong> {{ $hasil->jawaban_benar }} &nbsp;|&nbsp;
                <strong>Jawaban Salah:</strong> {{ $hasil->jawaban_salah }} &nbsp;|&nbsp;
                <strong>Tidak Dijawab:</strong> {{ $hasil->tidak_dijawab }} &nbsp;|&nbsp;
                <strong>Nilai Akhir:</strong> {{ $hasil->nilai_akhir }} / 100
            </div>
        </div>
    </div>
@endsection
