@extends('layouts.app')

@section('title', 'Manajemen Jadwal Ujian - CBT School')
@section('page_title', 'Jadwal Ujian')
@section('page_subtitle', 'Pengelolaan jadwal asesmen, durasi, dan parameter pelaksanaan ujian CBT')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jadwal Ujian</li>
@endsection

@section('content')
<x-card title="Daftar Pelaksanaan Ujian Terjadwal">
    <x-slot:tools>
        <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary btn-sm fw-bold">
            <span class="me-1">&#43;</span> Buat Jadwal Ujian
        </a>
    </x-slot:tools>

    <!-- Filter & Pencarian -->
    <form method="GET" action="{{ route('teacher.exams.index') }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari kode atau judul ujian..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="subject_id" class="form-select">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                            {{ $subj->code }} - {{ $subj->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">&#128269; Filter</button>
                @if(request()->hasAny(['search', 'subject_id', 'status']))
                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary" title="Reset Filter">&#10006;</a>
                @endif
            </div>
        </div>
    </form>

    <!-- Tabel Jadwal Ujian -->
    @if($exams->isEmpty())
        <div class="text-center py-5">
            <div class="text-secondary mb-3 fs-1">&#9201;</div>
            <h5 class="fw-bold text-secondary">Belum ada jadwal ujian ditemukan</h5>
            <p class="text-secondary small mb-3">Silakan buat jadwal pelaksanaan asesmen ujian baru untuk peserta didik Anda.</p>
            <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary btn-sm fw-bold">
                Buat Jadwal Ujian Sekarang
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode & Judul Ujian</th>
                        <th>Mata Pelajaran & Guru</th>
                        <th>Jadwal Pelaksanaan</th>
                        <th>Durasi & KKM</th>
                        <th class="text-center">Status</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $index => $exam)
                        <tr>
                            <td>{{ $exams->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold fs-6">
                                    <a href="{{ route('teacher.exams.show', $exam) }}" class="text-decoration-none text-dark">
                                        {{ $exam->title }}
                                    </a>
                                </div>
                                <span class="badge text-bg-light border font-monospace">{{ $exam->code }}</span>
                                @if($exam->token)
                                    <span class="badge text-bg-warning text-dark font-monospace" title="Token Akses">&#128273; {{ $exam->token }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">{{ $exam->subject?->name ?? '-' }}</div>
                                <small class="text-secondary d-block">{{ $exam->teacher?->name ?? 'Admin Sekolah' }}</small>
                            </td>
                            <td class="small">
                                <div><span class="fw-bold text-success">&#9654; Mulai:</span> {{ $exam->start_at->translatedFormat('d M Y, H:i') }}</div>
                                <div><span class="fw-bold text-danger">&#9632; Selesai:</span> {{ $exam->end_at->translatedFormat('d M Y, H:i') }}</div>
                            </td>
                            <td class="small">
                                <div><strong>Durasi:</strong> {{ $exam->duration }} menit</div>
                                <div><strong>KKM:</strong> {{ $exam->passing_grade }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-{{ $exam->computed_status_badge_class }}">
                                    {{ $exam->computed_status_label }}
                                </span>
                                @if($exam->isDraft())
                                    <div class="fs-8 text-secondary mt-1">Konsep</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-primary" title="Detail & Kelola">
                                        &#128065; Detail
                                    </a>
                                    <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-outline-secondary" title="Edit Jadwal">
                                        &#9998;
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $exam->id }}" title="Hapus Jadwal">
                                        &#128465;
                                    </button>
                                </div>

                                <!-- Modal Hapus Ujian -->
                                <x-modal id="deleteModal{{ $exam->id }}" title="Konfirmasi Hapus Jadwal Ujian">
                                    <div class="text-start">
                                        <p>Apakah Anda yakin ingin menghapus jadwal ujian <strong>{{ $exam->title }}</strong> ({{ $exam->code }})?</p>
                                        <div class="alert alert-warning small">
                                            Tindakan ini tidak dapat dibatalkan. Seluruh data ujian akan dihapus permanen.
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('teacher.exams.destroy', $exam) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="text-end">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Hapus Jadwal</button>
                                        </div>
                                    </form>
                                </x-modal>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($exams->hasPages())
            <div class="mt-4">
                {{ $exams->links() }}
            </div>
        @endif
    @endif
</x-card>
@endsection
