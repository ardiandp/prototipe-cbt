@extends('layouts.app')

@section('title', 'Detail Jadwal Ujian - ' . $exam->title . ' - CBT School')
@section('page_title', $exam->title)
@section('page_subtitle', 'Kode Ujian: ' . $exam->code)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $exam->code }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Kolom Kiri: Ringkasan Status & Parameter Ujian -->
    <div class="col-md-8">
        <x-card title="Ringkasan Parameter Pelaksanaan Ujian">
            <x-slot:tools>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-outline-primary">
                        &#9998; Edit Jadwal
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteExamModal">
                        &#128465; Hapus
                    </button>
                </div>
            </x-slot:tools>

            <!-- Banner Jadwal Operasional -->
            <div class="alert alert-{{ $exam->isOpen() ? 'success' : 'light border' }} d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="badge text-bg-{{ $exam->computed_status_badge_class }} fs-6 me-2">
                        {{ $exam->computed_status_label }}
                    </span>
                    <span class="fw-semibold">
                        @if($exam->isOpen())
                            &#9679; Sesi ujian sedang dibuka untuk peserta didik!
                        @elseif($exam->isDraft())
                            Ujian masih berupa konsep draft (belum dapat diakses siswa).
                        @elseif($exam->isCancelled())
                            Pelaksanaan ujian ini telah dibatalkan.
                        @elseif(now()->lessThan($exam->start_at))
                            Ujian belum dimulai. Dibuka pada {{ $exam->start_at->translatedFormat('d F Y, H:i') }}.
                        @else
                            Ujian telah selesai pada {{ $exam->end_at->translatedFormat('d F Y, H:i') }}.
                        @endif
                    </span>
                </div>
                <span class="badge text-bg-dark font-monospace">{{ $exam->code }}</span>
            </div>

            <!-- Grid Detail Metrik -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 border rounded text-center bg-body-tertiary">
                        <div class="text-secondary small">Durasi Timer</div>
                        <div class="fs-4 fw-bold text-primary">{{ $exam->duration }} <span class="fs-7 fw-normal">Menit</span></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 border rounded text-center bg-body-tertiary">
                        <div class="text-secondary small">Batas KKM</div>
                        <div class="fs-4 fw-bold text-success">{{ $exam->passing_grade }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 border rounded text-center bg-body-tertiary">
                        <div class="text-secondary small">Total Soal Terpilih</div>
                        <div class="fs-4 fw-bold text-dark">{{ $exam->total_questions }} <span class="fs-7 fw-normal">Butir</span></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 border rounded text-center bg-body-tertiary">
                        <div class="text-secondary small">Token Akses</div>
                        <div class="fs-4 fw-bold font-monospace text-danger">{{ $exam->token ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Rincian Parameter Tabel -->
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr>
                        <th style="width: 25%;" class="bg-light">Mata Pelajaran</th>
                        <td>{{ $exam->subject?->code }} - {{ $exam->subject?->name }} (Tingkat {{ $exam->subject?->level }})</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Guru Pembuat</th>
                        <td>{{ $exam->teacher?->name ?? 'Admin Sekolah' }} (NIP: {{ $exam->teacher?->nip ?? '-' }})</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Waktu Pelaksanaan</th>
                        <td>
                            <strong>{{ $exam->start_at->translatedFormat('l, d F Y, H:i') }}</strong>
                            &mdash; s.d. &mdash;
                            <strong>{{ $exam->end_at->translatedFormat('l, d F Y, H:i') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Parameter Acak</th>
                        <td>
                            <span class="badge text-bg-{{ $exam->random_question ? 'success' : 'secondary' }} me-1">
                                Acak Soal: {{ $exam->random_question ? 'Ya' : 'Tidak' }}
                            </span>
                            <span class="badge text-bg-{{ $exam->random_answer ? 'success' : 'secondary' }}">
                                Acak Opsi: {{ $exam->random_answer ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Petunjuk Ujian</th>
                        <td>{{ $exam->description ?? 'Tidak ada petunjuk khusus.' }}</td>
                    </tr>
                </tbody>
            </table>
        </x-card>

        <!-- Modul Butir Soal & Snapshot Asesmen (P5-T02) -->
        <div class="mt-4">
            <x-card title="Butir Soal & Snapshot Asesmen">
                <x-slot:tools>
                    <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                        &#43; Pilih Soal dari Bank
                    </a>
                </x-slot:tools>

                @if($exam->total_questions === 0)
                    <div class="text-center py-4 text-secondary">
                        <div class="fs-2 mb-2">&#128221;</div>
                        <h6 class="fw-bold">Belum Ada Butir Soal Terpilih</h6>
                        <p class="small mb-3">Jadwal ujian ini belum memiliki butir soal. Silakan pilih dan snapshot soal dari bank soal mata pelajaran {{ $exam->subject?->name }}.</p>
                        <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                            Pilih Soal dari Bank Sekarang
                        </a>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge text-bg-light border fs-7 me-2">
                                Total Soal: <strong class="text-primary">{{ $exam->total_questions }}</strong>
                            </span>
                            <span class="badge text-bg-light border fs-7">
                                Total Bobot: <strong class="text-success">{{ number_format($exam->total_weight, 2) }}</strong>
                            </span>
                        </div>
                        <a href="{{ route('teacher.exams.questions.index', $exam) }}" class="btn btn-outline-primary btn-sm fw-bold">
                            Kelola Seluruh Butir Soal ({{ $exam->total_questions }}) &rarr;
                        </a>
                    </div>

                    <!-- Cuplikan 3 Soal Pertama -->
                    <div class="list-group list-group-flush mb-2">
                        @foreach($exam->questions()->with('options')->take(3)->get() as $snapQ)
                            <div class="list-group-item p-2 border rounded mb-2 bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge text-bg-dark font-monospace">#{{ $snapQ->order }}</span>
                                    <span class="badge text-bg-secondary font-monospace">Bobot: {{ $snapQ->weight }}</span>
                                </div>
                                <div class="small fw-semibold text-truncate mb-1">
                                    {{ Str::limit($snapQ->question_text, 120) }}
                                </div>
                                <div class="text-success small">
                                    Kunci Jawaban: <strong>Pilihan {{ $snapQ->getCorrectAnswerLabel() }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($exam->total_questions > 3)
                        <div class="text-center mt-2">
                            <a href="{{ route('teacher.exams.questions.index', $exam) }}" class="small text-decoration-none">
                                dan {{ $exam->total_questions - 3 }} butir soal lainnya...
                            </a>
                        </div>
                    @endif
                @endif
            </x-card>
        </div>

        <!-- Modul Penetapan Peserta Ujian (P5-T03) -->
        <div class="mt-4">
            <x-card title="Penetapan Peserta Ujian">
                <x-slot:tools>
                    <a href="{{ route('teacher.exams.participants.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                        &#43; Tambah Peserta
                    </a>
                </x-slot:tools>

                @if($exam->total_participants === 0)
                    <div class="text-center py-4 text-secondary">
                        <div class="fs-2 mb-2">&#128101;</div>
                        <h6 class="fw-bold">Belum Ada Peserta Terdaftar</h6>
                        <p class="small mb-3">Jadwal ujian ini belum memiliki peserta. Silakan tetapkan peserta berdasarkan rombongan belajar (kelas) atau pilih secara individual.</p>
                        <a href="{{ route('teacher.exams.participants.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                            Tambah Peserta Ujian Sekarang
                        </a>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge text-bg-light border fs-7 me-2">
                                Total Peserta: <strong class="text-primary">{{ $exam->total_participants }}</strong> Siswa
                            </span>
                        </div>
                        <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="btn btn-outline-primary btn-sm fw-bold">
                            Kelola Seluruh Peserta ({{ $exam->total_participants }}) &rarr;
                        </a>
                    </div>

                    <!-- Cuplikan 4 Peserta Pertama -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">NIS</th>
                                    <th>Nama Siswa</th>
                                    <th style="width: 140px;">Kelas</th>
                                    <th style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exam->participants()->with(['student', 'classroom'])->take(4)->get() as $p)
                                    <tr>
                                        <td class="font-monospace small fw-bold">{{ $p->student?->nis ?? '-' }}</td>
                                        <td class="small fw-semibold">{{ $p->student?->name ?? 'Siswa' }}</td>
                                        <td class="small">
                                            <span class="badge text-bg-light border text-dark">
                                                {{ $p->classroom?->name ?? ($p->student?->classroom?->name ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-info text-white small">
                                                {{ $p->status === 'registered' ? 'Terdaftar' : ucfirst($p->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($exam->total_participants > 4)
                        <div class="text-center mt-2">
                            <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="small text-decoration-none">
                                dan {{ $exam->total_participants - 4 }} peserta lainnya...
                            </a>
                        </div>
                    @endif
                @endif
            </x-card>
        </div>
    </div>

    <!-- Kolom Kanan: Navigasi & Aksi -->
    <div class="col-md-4">
        <x-card title="Status & Publikasi">
            <div class="mb-3">
                <label class="form-label text-secondary small">Status Publikasi</label>
                <div>
                    <span class="badge text-bg-{{ $exam->isPublished() ? 'success' : ($exam->isCancelled() ? 'danger' : 'secondary') }} fs-6">
                        {{ ucfirst($exam->status) }}
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small">Status Operasional Jadwal</label>
                <div>
                    <span class="badge text-bg-{{ $exam->computed_status_badge_class }} fs-6">
                        {{ $exam->computed_status_label }}
                    </span>
                </div>
            </div>

            <!-- Kesiapan Publikasi (Checklist) -->
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="fw-bold small mb-2 text-dark">Kesiapan Publikasi:</div>
                <div class="d-flex align-items-center justify-content-between small mb-1">
                    <span>1. Butir Soal Snapshot</span>
                    @if($exam->total_questions > 0)
                        <span class="badge text-bg-success">&#10003; {{ $exam->total_questions }} Soal</span>
                    @else
                        <span class="badge text-bg-danger">&#10007; Kosong</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between small mb-1">
                    <span>2. Peserta Terdaftar</span>
                    @if($exam->total_participants > 0)
                        <span class="badge text-bg-success">&#10003; {{ $exam->total_participants }} Siswa</span>
                    @else
                        <span class="badge text-bg-danger">&#10007; Kosong</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between small">
                    <span>3. Waktu Pelaksanaan</span>
                    @if(!$exam->end_at->isPast())
                        <span class="badge text-bg-success">&#10003; Valid</span>
                    @else
                        <span class="badge text-bg-danger">&#10007; Kedaluwarsa</span>
                    @endif
                </div>
            </div>

            <hr class="my-3">

            <!-- Tombol Aksi Lifecycle -->
            <div class="d-grid gap-2">
                @if($exam->isDraft())
                    <form method="POST" action="{{ route('teacher.exams.publish', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold w-100" {{ $exam->canBePublished() ? '' : 'disabled' }} onclick="return confirm('Publikasikan jadwal ujian ini? Siswa terdaftar akan dapat melihat jadwal ini.')">
                            &#128640; Publikasikan Ujian
                        </button>
                    </form>
                    @if(!$exam->canBePublished())
                        <div class="small text-danger text-center">
                            Lengkapi butir soal dan peserta terlebih dahulu untuk mempublikasikan.
                        </div>
                    @endif
                @elseif($exam->isPublished())
                    <form method="POST" action="{{ route('teacher.exams.unpublish', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100" onclick="return confirm('Kembalikan status ujian ke Draft? Ujian tidak akan dapat diakses siswa sampai dipublikasikan kembali.')">
                            &#9100; Kembalikan ke Draft
                        </button>
                    </form>
                @endif

                @if(!$exam->isCancelled())
                    <form method="POST" action="{{ route('teacher.exams.cancel', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Apakah Anda yakin ingin membatalkan jadwal ujian ini?')">
                            &#128683; Batalkan Ujian
                        </button>
                    </form>
                @endif

                <a href="{{ route('teacher.exams.preview', $exam) }}" class="btn btn-info text-white">
                    &#128065; Pratinjau Ujian (Simulasi Siswa)
                </a>

                <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-outline-primary">
                    &#9998; Edit Jadwal & Pengaturan
                </a>
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Daftar Jadwal
                </a>
            </div>
        </x-card>
    </div>
</div>

<!-- Modal Hapus Jadwal Ujian -->
<x-modal id="deleteExamModal" title="Konfirmasi Hapus Jadwal Ujian">
    <div class="text-start">
        <p>Apakah Anda yakin ingin menghapus jadwal ujian <strong>{{ $exam->title }}</strong> ({{ $exam->code }})?</p>
        <div class="alert alert-danger small">
            Perhatian: Tindakan ini akan menghapus seluruh data jadwal ujian ini secara permanen.
        </div>
    </div>
    <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}">
        @csrf
        @method('DELETE')
        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus Jadwal Ujian</button>
        </div>
    </form>
</x-modal>
@endsection

