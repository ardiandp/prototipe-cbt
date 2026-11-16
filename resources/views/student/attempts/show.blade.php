@extends('layouts.app')

@section('title', 'Ruang Ujian - ' . $attempt->exam->title . ' - CBT School')
@section('page_title', 'Ruang Pengerjaan Ujian')
@section('page_subtitle', $attempt->exam->title . ' (' . $attempt->exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item active" aria-current="page">Attempt #{{ $attempt->attempt_number }}</li>
@endsection

@section('content')
<!-- Top Bar Informasi & Server Timer -->
<div class="row mb-3">
    <div class="col-12">
        <div class="p-3 border rounded bg-white shadow-xs d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge text-bg-primary me-2 fs-7">
                    Attempt #{{ $attempt->attempt_number }}
                </span>
                <span class="fw-bold fs-6 me-3">{{ $attempt->exam->title }}</span>
                <span class="text-secondary small">Mapel: <strong>{{ $attempt->exam->subject?->name }}</strong></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Server Timer Display -->
                <div class="p-2 px-3 border border-danger rounded bg-danger-subtle d-flex align-items-center gap-2">
                    <span class="fs-5">&#9201;</span>
                    <div>
                        <div class="small text-danger fw-bold lh-1">SISA WAKTU</div>
                        <div class="fs-5 fw-bold font-monospace text-danger" id="timerDisplay">
                            {{ gmdate('H:i:s', $attempt->remaining_seconds) }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('student.exams.show', $attempt->exam) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Petunjuk Ujian
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Ruang Pengerjaan Soal (Shell / Engine Holder) -->
    <div class="col-lg-8 col-md-7 mb-4">
        <x-card title="Lembar Soal Asesmen">
            <div class="alert alert-success border d-flex align-items-center mb-3">
                <span class="fs-3 me-3">&#10003;</span>
                <div class="small">
                    <strong>Sesi Ujian Aktif:</strong> Attempt pengerjaan Anda berhasil dibuat dan tercatat di server. Sesuai aturan CBT, me-refresh halaman tidak akan membuat sesi baru atau mereset sisa waktu pengerjaan.
                </div>
            </div>

            <!-- Detail Sesi Attempt -->
            <div class="border rounded p-3 bg-light mb-4">
                <h6 class="fw-bold text-dark mb-2">&#128203; Informasi Sesi Ujian Anda:</h6>
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <span class="text-secondary">Waktu Mulai:</span>
                        <strong>{{ $attempt->started_at->translatedFormat('d M Y H:i:s') }} WIB</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Batas Akhir:</span>
                        <strong class="text-danger">{{ $attempt->expires_at->translatedFormat('d M Y H:i:s') }} WIB</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Durasi Maksimal:</span>
                        <strong>{{ $attempt->exam->duration }} Menit</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Status Attempt:</span>
                        <span class="badge text-bg-success">In Progress</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Butir Soal & Opsi Terpetakan (P6-T02) -->
            <div class="d-flex flex-column gap-4">
                @foreach($attempt->attemptQuestions as $aq)
                    <div class="border rounded p-3 bg-white shadow-xs" id="question-{{ $aq->order }}">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <span class="badge text-bg-dark font-monospace fs-7 me-1">Soal No. {{ $aq->order }}</span>
                                <span class="badge text-bg-light border text-secondary font-monospace">Bobot: {{ $aq->question?->weight }}</span>
                            </div>
                            <div>
                                @if($aq->is_doubtful)
                                    <span class="badge text-bg-warning text-dark">&#9873; Ragu-ragu</span>
                                @elseif($aq->isAnswered())
                                    <span class="badge text-bg-success">&#10003; Terjawab</span>
                                @else
                                    <span class="badge text-bg-secondary">Belum Dijawab</span>
                                @endif
                            </div>
                        </div>

                        <!-- Teks Pertanyaan -->
                        <div class="fs-6 fw-semibold text-body mb-3">
                            {!! nl2br(e($aq->question?->question_text)) !!}
                        </div>

                        <!-- Gambar Soal Jika Ada -->
                        @if($aq->question?->hasImage())
                            <div class="mb-3">
                                <img src="{{ $aq->question->image_url }}" alt="Gambar Soal #{{ $aq->order }}" class="img-fluid border rounded" style="max-height: 250px;">
                            </div>
                        @endif

                        <!-- Opsi Jawaban Terpetakan Aman -->
                        <div class="list-group list-group-flush border rounded">
                            @foreach($aq->options as $opt)
                                <label class="list-group-item list-group-item-action d-flex align-items-start gap-2 py-2">
                                    <input type="radio" name="answer_{{ $aq->id }}" value="{{ $opt->exam_question_option_id }}" class="form-check-input mt-1" {{ $aq->selected_exam_question_option_id === $opt->exam_question_option_id ? 'checked' : '' }} disabled>
                                    <span class="badge text-bg-light border text-dark font-monospace fw-bold mt-1">
                                        {{ $opt->display_label }}
                                    </span>
                                    <div class="flex-grow-1 text-body">
                                        {{ $opt->option_text }}
                                        @if($opt->hasImage())
                                            <div class="mt-2">
                                                <img src="{{ $opt->image_url }}" alt="Gambar Opsi {{ $opt->display_label }}" class="img-fluid border rounded" style="max-height: 120px;">
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <!-- Kolom Kanan: Identitas Peserta & Aturan Server -->
    <div class="col-lg-4 col-md-5">
        <x-card title="Profil Peserta">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    &#128100;
                </div>
                <div>
                    <div class="fw-bold fs-6">{{ $attempt->student?->name }}</div>
                    <div class="small text-secondary font-monospace">NIS: {{ $attempt->student?->nis }}</div>
                    <span class="badge text-bg-light border text-dark small">
                        {{ $attempt->student?->classroom?->name ?? 'Kelas' }}
                    </span>
                </div>
            </div>

            <hr class="my-3">

            <div class="small text-secondary">
                <div class="fw-bold text-dark mb-1">&#128712; Aturan Server CBT:</div>
                <ul class="ps-3 mb-0">
                    <li class="mb-1">Waktu dihitung berdasarkan jam server, bukan jam pada perangkat lokal Anda.</li>
                    <li class="mb-1">Jika browser tertutup, silakan buka kembali halaman ujian untuk melanjutkan.</li>
                    <li>Sesi ini akan otomatis ditutup jika waktu telah melewati batas akhir.</li>
                </ul>
            </div>
        </x-card>
    </div>
</div>

<script>
// Hitung mundur lokal berbasis selisih waktu server
document.addEventListener('DOMContentLoaded', function () {
    let remaining = {{ $attempt->remaining_seconds }};
    const timerElem = document.getElementById('timerDisplay');

    if (timerElem && remaining > 0) {
        const interval = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                timerElem.textContent = '00:00:00';
                alert('Waktu ujian Anda telah habis!');
                location.reload();
                return;
            }

            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;

            timerElem.textContent = 
                String(h).padStart(2, '0') + ':' + 
                String(m).padStart(2, '0') + ':' + 
                String(s).padStart(2, '0');
        }, 1000);
    }
});
</script>
@endsection

