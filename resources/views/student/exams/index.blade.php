@extends('layouts.app')

@section('title', 'Jadwal Ujian Saya - CBT School')
@section('page_title', 'Jadwal Asesmen & Ujian')
@section('page_subtitle', 'Daftar jadwal ujian yang ditugaskan kepada Anda')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Siswa</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jadwal Ujian</li>
@endsection

@section('content')
<!-- Nav Tabs Filter Status Ujian -->
<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="studentExamTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $ongoingExams->isNotEmpty() || ($upcomingExams->isEmpty() && $pastExams->isEmpty()) ? 'active fw-bold' : '' }}" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing-pane" type="button" role="tab" aria-controls="ongoing-pane" aria-selected="true">
                    &#9654; Sedang Berlangsung <span class="badge text-bg-success ms-1">{{ $ongoingExams->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $ongoingExams->isEmpty() && $upcomingExams->isNotEmpty() ? 'active fw-bold' : '' }}" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-pane" type="button" role="tab" aria-controls="upcoming-pane" aria-selected="false">
                    &#128336; Akan Datang <span class="badge text-bg-info text-white ms-1">{{ $upcomingExams->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $ongoingExams->isEmpty() && $upcomingExams->isEmpty() && $pastExams->isNotEmpty() ? 'active fw-bold' : '' }}" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-pane" type="button" role="tab" aria-controls="past-pane" aria-selected="false">
                    &#9989; Telah Berakhir <span class="badge text-bg-secondary ms-1">{{ $pastExams->count() }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="studentExamTabContent">
            <!-- PANE 1: Sedang Berlangsung -->
            <div class="tab-pane fade {{ $ongoingExams->isNotEmpty() || ($upcomingExams->isEmpty() && $pastExams->isEmpty()) ? 'show active' : '' }}" id="ongoing-pane" role="tabpanel" aria-labelledby="ongoing-tab" tabindex="0">
                @if($ongoingExams->isEmpty())
                    <div class="text-center py-5 text-secondary">
                        <div class="fs-1 mb-2">&#127891;</div>
                        <h6 class="fw-bold">Tidak Ada Ujian yang Sedang Berlangsung</h6>
                        <p class="small mb-0">Saat ini tidak ada sesi ujian aktif yang dapat dikerjakan.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($ongoingExams as $exam)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-success shadow-sm">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <span class="badge text-bg-light text-dark font-monospace">{{ $exam->code }}</span>
                                        <span class="badge text-bg-warning text-dark fw-bold">Sedang Berlangsung</span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                                        <div class="text-primary small fw-semibold mb-3">{{ $exam->subject?->name }}</div>

                                        <ul class="list-unstyled small mb-3">
                                            <li class="mb-1 text-secondary">
                                                &#128100; Guru: <strong class="text-dark">{{ $exam->teacher?->user?->name ?? '-' }}</strong>
                                            </li>
                                            <li class="mb-1 text-secondary">
                                                &#128337; Durasi: <strong class="text-dark">{{ $exam->duration }} Menit</strong>
                                            </li>
                                            <li class="mb-1 text-secondary">
                                                &#128197; Batas Waktu: <strong class="text-danger">{{ $exam->end_at->translatedFormat('d M H:i') }} WIB</strong>
                                            </li>
                                            <li class="text-secondary">
                                                &#128221; KKM: <strong class="text-dark">{{ number_format($exam->passing_grade, 2) }}</strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 pt-0">
                                        <a href="{{ route('student.exams.show', $exam) }}" class="btn btn-success w-100 fw-bold">
                                            Masuk Halaman Ujian &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- PANE 2: Akan Datang -->
            <div class="tab-pane fade {{ $ongoingExams->isEmpty() && $upcomingExams->isNotEmpty() ? 'show active' : '' }}" id="upcoming-pane" role="tabpanel" aria-labelledby="upcoming-tab" tabindex="0">
                @if($upcomingExams->isEmpty())
                    <div class="text-center py-5 text-secondary">
                        <div class="fs-1 mb-2">&#128197;</div>
                        <h6 class="fw-bold">Tidak Ada Jadwal Ujian Akan Datang</h6>
                        <p class="small mb-0">Belum ada jadwal ujian baru yang akan dibuka.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($upcomingExams as $exam)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-info shadow-sm">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <span class="badge text-bg-light text-dark font-monospace">{{ $exam->code }}</span>
                                        <span class="badge text-bg-light text-primary">Akan Datang</span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                                        <div class="text-primary small fw-semibold mb-3">{{ $exam->subject?->name }}</div>

                                        <ul class="list-unstyled small mb-3">
                                            <li class="mb-1 text-secondary">
                                                &#128100; Guru: <strong class="text-dark">{{ $exam->teacher?->user?->name ?? '-' }}</strong>
                                            </li>
                                            <li class="mb-1 text-secondary">
                                                &#128337; Durasi: <strong class="text-dark">{{ $exam->duration }} Menit</strong>
                                            </li>
                                            <li class="mb-1 text-secondary">
                                                &#128197; Mulai: <strong class="text-primary">{{ $exam->start_at->translatedFormat('d M Y H:i') }} WIB</strong>
                                            </li>
                                            <li class="text-secondary">
                                                &#128221; KKM: <strong class="text-dark">{{ number_format($exam->passing_grade, 2) }}</strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 pt-0">
                                        <a href="{{ route('student.exams.show', $exam) }}" class="btn btn-outline-info w-100">
                                            Lihat Petunjuk & Persiapan &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- PANE 3: Telah Berakhir -->
            <div class="tab-pane fade {{ $ongoingExams->isEmpty() && $upcomingExams->isEmpty() && $pastExams->isNotEmpty() ? 'show active' : '' }}" id="past-pane" role="tabpanel" aria-labelledby="past-tab" tabindex="0">
                @if($pastExams->isEmpty())
                    <div class="text-center py-5 text-secondary">
                        <div class="fs-1 mb-2">&#9989;</div>
                        <h6 class="fw-bold">Belum Ada Riwayat Ujian yang Berakhir</h6>
                        <p class="small mb-0">Ujian yang telah selesai atau kedaluwarsa akan tercatat di sini.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul Ujian</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Waktu Berakhir</th>
                                    <th>Durasi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastExams as $exam)
                                    <tr>
                                        <td><span class="badge text-bg-light border font-monospace">{{ $exam->code }}</span></td>
                                        <td class="fw-semibold">{{ $exam->title }}</td>
                                        <td>{{ $exam->subject?->name }}</td>
                                        <td class="small text-secondary">{{ $exam->end_at->translatedFormat('d M Y H:i') }} WIB</td>
                                        <td>{{ $exam->duration }} Menit</td>
                                        <td class="text-center">
                                            <a href="{{ route('student.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
