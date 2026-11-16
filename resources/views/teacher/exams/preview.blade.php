@extends('layouts.app')

@section('title', 'Pratinjau Ujian - ' . $exam->title . ' - CBT School')
@section('page_title', 'Pratinjau & Simulasi Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pratinjau</li>
@endsection

@section('content')
<div class="alert alert-info border d-flex align-items-center mb-3">
    <span class="fs-3 me-3">&#128065;</span>
    <div>
        <strong>Mode Pratinjau Guru:</strong> Halaman ini merepresentasikan simulasi dan verifikasi butir soal serta petunjuk pelaksanaan ujian sebelum atau saat ujian berlangsung. Jawaban dan aksi di halaman ini tidak disimpan ke dalam sesi attempt pengerjaan siswa sesungguhnya.
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Informasi Parameter & Petunjuk Siswa -->
    <div class="col-md-4 mb-4">
        <x-card title="Parameter & Aturan CBT">
            <table class="table table-sm table-borderless mb-0">
                <tbody>
                    <tr>
                        <th class="text-secondary" style="width: 140px;">Mata Pelajaran</th>
                        <td class="fw-bold">{{ $exam->subject?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Guru Pengampu</th>
                        <td>{{ $exam->teacher?->user?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Waktu Mulai</th>
                        <td>{{ $exam->start_at->translatedFormat('d M Y H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Waktu Selesai</th>
                        <td>{{ $exam->end_at->translatedFormat('d M Y H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Durasi Maksimal</th>
                        <td><span class="badge text-bg-primary fs-7">{{ $exam->duration }} Menit</span></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">KKM Asesmen</th>
                        <td><span class="badge text-bg-warning text-dark fs-7">{{ number_format($exam->passing_grade, 2) }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Acak Butir Soal</th>
                        <td>
                            <span class="badge text-bg-{{ $exam->random_question ? 'success' : 'secondary' }}">
                                {{ $exam->random_question ? 'Aktif (Diacak per Siswa)' : 'Nonaktif (Urut)' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Acak Opsi Pilihan</th>
                        <td>
                            <span class="badge text-bg-{{ $exam->random_answer ? 'success' : 'secondary' }}">
                                {{ $exam->random_answer ? 'Aktif (Diacak per Siswa)' : 'Nonaktif (Urut)' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-secondary">Token Masuk</th>
                        <td>
                            @if($exam->token)
                                <span class="badge text-bg-dark font-monospace fs-7 px-2 py-1">{{ $exam->token }}</span>
                            @else
                                <span class="badge text-bg-light border text-secondary">Tanpa Token</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="my-3">

            <div class="p-2 border rounded bg-light small mb-3">
                <div class="fw-bold text-dark mb-1">&#128336; Server Time Rule:</div>
                <div class="text-secondary">
                    Batas kedaluwarsa siswa dihitung oleh server: <code>expires_at = min(started_at + {{ $exam->duration }}m, exam.end_at)</code>.
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Detail Jadwal
                </a>
            </div>
        </x-card>

        <div class="mt-3">
            <x-card title="Petunjuk Pengerjaan untuk Siswa">
                <div class="small text-body">
                    {!! nl2br(e($exam->description ?? 'Tidak ada petunjuk khusus untuk jadwal ujian ini.')) !!}
                </div>
            </x-card>
        </div>
    </div>

    <!-- Kolom Kanan: Butir Soal Terpilih (Snapshot) -->
    <div class="col-md-8">
        <x-card title="Daftar Butir Soal Snapshot ({{ $questions->count() }} Soal)">
            <x-slot:tools>
                <span class="badge text-bg-success">Total Bobot: {{ number_format($exam->total_weight, 2) }}</span>
            </x-slot:tools>

            @if($questions->isEmpty())
                <div class="text-center py-5 text-secondary">
                    <div class="fs-1 mb-2">&#128221;</div>
                    <h6 class="fw-bold">Belum Ada Soal Dipilih</h6>
                    <p class="small mb-3">Silakan pilih soal dari bank soal terlebih dahulu.</p>
                    <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-primary btn-sm">
                        Pilih Butir Soal
                    </a>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($questions as $index => $q)
                        <div class="border rounded p-3 bg-white shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="badge text-bg-dark font-monospace me-1">#{{ $q->order }}</span>
                                    @if($q->code)
                                        <span class="badge text-bg-light border font-monospace me-1">{{ $q->code }}</span>
                                    @endif
                                    <span class="badge text-bg-info text-white">Bobot: {{ $q->weight }}</span>
                                </div>
                                <span class="badge text-bg-light border text-secondary small">
                                    {{ ucfirst($q->difficulty) }}
                                </span>
                            </div>

                            <!-- Soal Teks -->
                            <div class="fw-semibold text-body mb-3">
                                {!! nl2br(e($q->question_text)) !!}
                            </div>

                            <!-- Gambar Soal (Jika Ada) -->
                            @if($q->hasImage())
                                <div class="mb-3">
                                    <img src="{{ $q->image_url }}" alt="Gambar Soal #{{ $q->order }}" class="img-fluid border rounded" style="max-height: 250px;">
                                </div>
                            @endif

                            <!-- Opsi Pilihan A - E -->
                            <div class="list-group list-group-flush border rounded">
                                @foreach($q->options as $opt)
                                    <div class="list-group-item d-flex align-items-start gap-2 {{ $opt->is_correct ? 'bg-success-subtle border-success' : '' }}">
                                        <span class="badge {{ $opt->is_correct ? 'text-bg-success' : 'text-bg-light border text-dark' }} font-monospace mt-1">
                                            {{ $opt->label }}
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="{{ $opt->is_correct ? 'fw-bold text-success' : 'text-body' }}">
                                                {{ $opt->option_text }}
                                            </div>
                                            @if($opt->hasImage())
                                                <div class="mt-2">
                                                    <img src="{{ $opt->image_url }}" alt="Gambar Opsi {{ $opt->label }}" class="img-fluid border rounded" style="max-height: 120px;">
                                                </div>
                                            @endif
                                        </div>
                                        @if($opt->is_correct)
                                            <span class="badge text-bg-success small">Kunci Jawaban</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
