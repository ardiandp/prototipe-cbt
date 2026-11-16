@extends('layouts.app')

@section('title', 'Ruang Persiapan - ' . $exam->title . ' - CBT School')
@section('page_title', 'Ruang Persiapan Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $exam->code }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <!-- Status Banner -->
        @if(isset($latestAttempt) && $latestAttempt->isActive())
            <div class="alert alert-warning border-warning d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#9201;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Sesi Ujian Sedang Berjalan!</h5>
                    <p class="mb-0 small">
                        Anda memiliki sesi attempt aktif yang sedang berlangsung. Sesuai aturan CBT, waktu server tetap berjalan. Silakan lanjutkan pengerjaan Anda.
                    </p>
                </div>
            </div>
        @elseif(isset($latestAttempt) && $latestAttempt->isCompleted())
            <div class="alert alert-success border-success d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#10003;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Ujian Telah Diselesaikan</h5>
                    <p class="mb-0 small">
                        Anda telah menyelesaikan seluruh proses pengerjaan untuk jadwal asesmen ini.
                    </p>
                </div>
            </div>
        @elseif(isset($latestAttempt) && $latestAttempt->isExpired())
            <div class="alert alert-danger border-danger d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#9888;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Sesi Ujian Kedaluwarsa</h5>
                    <p class="mb-0 small">
                        Batas waktu pengerjaan sesi ujian Anda telah berakhir dan telah diselesaikan oleh sistem server.
                    </p>
                </div>
            </div>
        @elseif($exam->isOpen())
            <div class="alert alert-success border-success d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#9654;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Sesi Ujian Sedang Dibuka!</h5>
                    <p class="mb-0 small">
                        Waktu pengerjaan saat ini aktif. Pastikan perangkat Anda terhubung stabil ke jaringan LAN dan baca petunjuk sebelum memulai pengerjaan.
                    </p>
                </div>
            </div>
        @elseif($exam->isUpcoming())
            <div class="alert alert-info border-info d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#128336;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Ujian Belum Dimulai</h5>
                    <p class="mb-0 small">
                        Ujian dijadwalkan dibuka pada <strong>{{ $exam->start_at->translatedFormat('l, d F Y H:i') }} WIB</strong>. Silakan kembali saat jam pelaksanaan dimulai.
                    </p>
                </div>
            </div>
        @else
            <div class="alert alert-secondary border d-flex align-items-center mb-4">
                <span class="fs-2 me-3">&#9989;</span>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Waktu Pelaksanaan Telah Berakhir</h5>
                    <p class="mb-0 small">
                        Jadwal ujian ini telah ditutup pada <strong>{{ $exam->end_at->translatedFormat('l, d F Y H:i') }} WIB</strong>. Anda tidak dapat lagi memulai pengerjaan.
                    </p>
                </div>
            </div>
        @endif

        <!-- Card Informasi Ujian & Peserta -->
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">&#128221; Informasi Asesmen</span>
                <span class="badge text-bg-light font-monospace">{{ $exam->code }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">Mata Pelajaran</label>
                        <span class="fw-bold fs-6 text-dark">{{ $exam->subject?->name }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">Guru Pengampu</label>
                        <span class="fw-bold fs-6 text-dark">{{ $exam->teacher?->user?->name ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">Durasi Pengerjaan</label>
                        <span class="badge text-bg-primary fs-7">{{ $exam->duration }} Menit</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">KKM / Nilai Minimal</label>
                        <span class="badge text-bg-warning text-dark fs-7">{{ number_format($exam->passing_grade, 2) }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">Jadwal Mulai</label>
                        <span class="small fw-semibold text-dark">{{ $exam->start_at->translatedFormat('d F Y H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary small d-block">Batas Selesai</label>
                        <span class="small fw-semibold text-danger">{{ $exam->end_at->translatedFormat('d F Y H:i') }} WIB</span>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Verifikasi Identitas Peserta -->
                <div class="bg-light p-3 rounded border">
                    <div class="fw-bold small text-secondary mb-2">&#128100; IDENTITAS PESERTA TERDAFTAR:</div>
                    <div class="row g-2 small">
                        <div class="col-6">
                            Nama: <strong>{{ auth()->user()->studentProfile?->name }}</strong>
                        </div>
                        <div class="col-6">
                            NIS: <strong class="font-monospace">{{ auth()->user()->studentProfile?->nis }}</strong>
                        </div>
                        <div class="col-6">
                            Kelas/Rombel: <strong>{{ $participant->classroom?->name ?? auth()->user()->studentProfile?->classroom?->name }}</strong>
                        </div>
                        <div class="col-6">
                            Status: <span class="badge text-bg-info text-white">{{ ucfirst($participant->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk & Tata Tertib -->
                <div class="mt-4">
                    <h6 class="fw-bold text-dark mb-2">&#128712; Petunjuk & Tata Tertib Ujian:</h6>
                    <div class="p-3 bg-body-tertiary rounded border small">
                        @if($exam->description)
                            <div class="mb-3 text-dark">
                                {!! nl2br(e($exam->description)) !!}
                            </div>
                            <hr class="my-2">
                        @endif
                        <ol class="mb-0 ps-3 text-secondary">
                            <li class="mb-1">Waktu server adalah <strong>sumber kebenaran</strong>. Durasi pengerjaan Anda maksimal {{ $exam->duration }} menit atau berakhir saat jadwal ditutup.</li>
                            <li class="mb-1">Setiap jawaban yang Anda pilih akan <strong>tersimpan otomatis</strong> ke sistem server.</li>
                            <li class="mb-1">Jika browser ter-refresh atau listrik padam sementara, Anda dapat melanjutkan kembali selama batas waktu belum habis.</li>
                            <li class="mb-1">Dilarang membuka tab/aplikasi lain, mencontek, atau melakukan tindakan kecurangan dalam bentuk apapun.</li>
                            <li>Tekan tombol Selesai hanya jika Anda telah selesai memeriksa seluruh jawaban.</li>
                        </ol>
                    </div>
                </div>

                @if(isset($latestAttempt) && $latestAttempt->isActive())
                    <!-- Sesi Aktif Berjalan -->
                    <div class="mt-4 p-3 border border-warning rounded bg-warning-subtle text-center">
                        <div class="fw-bold text-dark fs-6 mb-1">&#9201; Sesi Attempt Anda Sedang Berlangsung</div>
                        <p class="small text-secondary mb-3">Dimulai pada: <strong>{{ $latestAttempt->started_at->format('H:i:s') }} WIB</strong> | Batas Selesai: <strong>{{ $latestAttempt->expires_at->format('H:i:s') }} WIB</strong></p>
                        <a href="{{ route('student.attempts.show', $latestAttempt) }}" class="btn btn-warning btn-lg fw-bold px-4">
                            &#9654; Lanjutkan Pengerjaan Ujian (Resume)
                        </a>
                    </div>
                @elseif(!isset($latestAttempt) && $exam->isOpen())
                    <!-- Form Mulai Ujian Baru -->
                    <form method="POST" action="{{ route('student.exams.start', $exam) }}" id="startExamForm">
                        @csrf
                        @if($exam->token)
                            <div class="mt-4 p-3 border border-warning rounded bg-warning-subtle">
                                <label class="form-label fw-bold text-dark small mb-1">&#128273; Token Masuk Ujian:</label>
                                <p class="small text-secondary mb-2">Jadwal ujian ini memerlukan token yang diberikan oleh pengawas ruangan.</p>
                                <input type="text" name="token" class="form-control text-uppercase font-monospace fw-bold text-center" style="letter-spacing: 4px; max-width: 250px;" placeholder="MASUKKAN TOKEN" required autocomplete="off">
                            </div>
                        @endif
                    </form>
                @endif
            </div>

            <!-- Card Footer: Aksi Mulai / Status -->
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('student.exams.index') }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Jadwal
                </a>

                @if(isset($latestAttempt) && $latestAttempt->isActive())
                    <a href="{{ route('student.attempts.show', $latestAttempt) }}" class="btn btn-warning btn-lg fw-bold px-4">
                        &#9654; Resume Ujian
                    </a>
                @elseif(isset($latestAttempt) && $latestAttempt->isCompleted())
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        &#10003; Telah Diselesaikan
                    </button>
                @elseif(isset($latestAttempt) && $latestAttempt->isExpired())
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        &#9888; Waktu Kedaluwarsa
                    </button>
                @elseif($exam->isOpen())
                    <button type="submit" form="startExamForm" class="btn btn-success btn-lg fw-bold px-4" title="Mulai Sesi Ujian" onclick="return confirm('Apakah Anda yakin ingin memulai ujian sekarang? Timer server akan langsung berjalan.')">
                        &#9654; Mulai Pengerjaan Ujian
                    </button>
                @elseif($exam->isUpcoming())
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        &#128336; Ujian Belum Dibuka
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        &#128683; Waktu Ujian Berakhir
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

