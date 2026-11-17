@extends('layouts.app')

@section('title', 'Riwayat & Nilai Ujian - CBT School')
@section('page_title', 'Riwayat & Nilai Ujian')
@section('page_subtitle', 'Daftar asesmen yang telah diikuti dan pengumuman hasil nilai')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Riwayat & Nilai</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold mb-0">&#128220; Hasil Ujian Saya</h5>
                    <div class="small text-secondary">Daftar capaian nilai asesmen dan cetak rapor hasil ujian</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-info">Total: {{ count($history) }} Asesmen</span>
                    <a href="{{ route('student.results.raport') }}" target="_blank" class="btn btn-sm btn-primary fw-semibold">
                        &#128424; Cetak Rapor Nilai Siswa
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Kode & Judul Ujian</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center">Status Pengerjaan</th>
                                <th class="text-center">Status Pengumuman</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Status KKM</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $index => $item)
                                @php
                                    $exam = $item->exam;
                                    $attempt = $item->latest_attempt;
                                    $isPublished = $item->is_published;
                                @endphp
                                <tr>
                                    <td class="text-center text-secondary small">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $exam->title }}</div>
                                        <span class="badge text-bg-light border font-monospace text-secondary">{{ $exam->code }}</span>
                                        <span class="text-secondary small ms-1">&bull; KKM: {{ number_format($exam->passing_grade, 0) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $exam->subject->name ?? '-' }}</span>
                                        <div class="text-secondary small">{{ $exam->teacher->name ?? ($exam->teacher->user->name ?? '-') }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($attempt && in_array($attempt->status, ['completed', 'expired']))
                                            <span class="badge text-bg-success">&#10003; Selesai</span>
                                            <div class="text-secondary small mt-1">{{ $attempt->completed_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                        @elseif($attempt && $attempt->isInProgress())
                                            <span class="badge text-bg-warning">&#9201; Berjalan</span>
                                        @else
                                            <span class="badge text-bg-secondary">Belum Dikerjakan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isPublished)
                                            <span class="badge text-bg-success">
                                                &#10003; Diumumkan
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary">
                                                &#9203; Menunggu Pengumuman
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($attempt && in_array($attempt->status, ['completed', 'expired']))
                                            @if($isPublished)
                                                <span class="fs-5 fw-bold {{ (float) $attempt->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($attempt->score, 1) }}
                                                </span>
                                            @else
                                                <span class="text-secondary fw-bold fs-5">-</span>
                                                <div class="text-secondary small">Terkunci</div>
                                            @endif
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($attempt && in_array($attempt->status, ['completed', 'expired']))
                                            @if($isPublished)
                                                @if((float) $attempt->score >= (float) $exam->passing_grade)
                                                    <span class="badge text-bg-success">&#10003; Tuntas KKM</span>
                                                @else
                                                    <span class="badge text-bg-danger">&#9888; Remedial</span>
                                                @endif
                                            @else
                                                <span class="badge text-bg-light border text-secondary">Belum Diumumkan</span>
                                            @endif
                                        @else
                                            <span class="text-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($attempt && in_array($attempt->status, ['completed', 'expired']))
                                            @if($isPublished)
                                                <div class="d-flex flex-column gap-1">
                                                    <a href="{{ route('student.results.show', $exam) }}" class="btn btn-sm btn-primary">
                                                        &#128221; Pembahasan
                                                    </a>
                                                    <a href="{{ route('student.results.print', $exam) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        &#128424; Cetak Hasil
                                                    </a>
                                                </div>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Hasil belum dipublikasikan oleh guru">
                                                    &#128274; Menunggu Pengumuman
                                                </button>
                                            @endif
                                        @elseif($attempt && $attempt->isInProgress())
                                            <a href="{{ route('student.exams.show', $exam) }}" class="btn btn-sm btn-warning">
                                                Lanjutkan Ujian
                                            </a>
                                        @else
                                            <a href="{{ route('student.exams.show', $exam) }}" class="btn btn-sm btn-outline-primary">
                                                Buka Ujian
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-secondary">
                                        <div class="fs-1 mb-2">&#128220;</div>
                                        <h6 class="fw-bold mb-1">Belum ada riwayat ujian</h6>
                                        <p class="small text-secondary mb-0">Anda belum memiliki jadwal atau riwayat asesmen.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
