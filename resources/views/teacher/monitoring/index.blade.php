@extends('layouts.app')

@section('title', 'Monitoring Pelaksanaan Ujian')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 fw-bold">&#128065; Monitoring Ujian</h1>
            </div>
            <div class="col-sm-6 text-end">
                <span class="badge text-bg-primary fs-7">
                    Waktu Server: <span id="clockDisplay">{{ now()->format('H:i:s') }}</span> WIB
                </span>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-light py-2">
                <span class="fw-bold small text-secondary">&#128269; FILTER DAFTAR UJIAN</span>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Cari Judul / Kode Ujian</label>
                            <input type="text" name="search" class="form-control" placeholder="Kata kunci..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Mata Pelajaran</label>
                            <select name="subject_id" class="form-select">
                                <option value="">-- Semua Mata Pelajaran --</option>
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                        {{ $subj->name }} ({{ $subj->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Status Ujian</label>
                            <select name="status" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published (Aktif / Berjalan)</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Cari
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Reset Filter">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Exam List Table -->
        <div class="card shadow-sm border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold small text-secondary">DAFTAR JADWAL UJIAN TERDAFTAR</span>
                <span class="badge text-bg-light border text-primary">
                    Total: {{ $exams->total() }} Ujian
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small text-uppercase">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Informasi Ujian</th>
                                <th>Jadwal Pelaksanaan</th>
                                <th>Status Ujian</th>
                                <th class="text-center">Peserta & Progres</th>
                                <th style="width: 140px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $idx => $exam)
                                <tr>
                                    <td class="text-center font-monospace text-secondary">
                                        {{ $exams->firstItem() + $idx }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $exam->title }}</div>
                                        <div class="small text-secondary">
                                            <span class="font-monospace text-primary fw-bold">{{ $exam->code }}</span>
                                            &bull; {{ $exam->subject->name ?? '-' }}
                                            @if($exam->teacher?->user)
                                                &bull; Guru: {{ $exam->teacher->user->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark fw-semibold">
                                            {{ $exam->start_at->translatedFormat('d M Y, H:i') }} &ndash; {{ $exam->end_at->translatedFormat('H:i') }} WIB
                                        </div>
                                        <div class="small text-secondary">
                                            Durasi: <strong>{{ $exam->duration }} Menit</strong>
                                            &bull; Soal: <strong>{{ $exam->questions_count ?: $exam->total_questions }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($exam->isOpen())
                                            <span class="badge text-bg-success px-2 py-1">Sedang Buka</span>
                                        @elseif($exam->isUpcoming())
                                            <span class="badge text-bg-info text-white px-2 py-1">Akan Datang</span>
                                        @elseif($exam->isFinished())
                                            <span class="badge text-bg-secondary px-2 py-1">Selesai</span>
                                        @else
                                            <span class="badge text-bg-light border text-secondary px-2 py-1">{{ ucfirst($exam->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 mb-1">
                                            <span class="badge text-bg-warning text-dark" title="Sedang Mengerjakan">
                                                &#9201; {{ $exam->in_progress_count }} Mengerjakan
                                            </span>
                                            <span class="badge text-bg-success" title="Selesai Dikumpulkan">
                                                &#10003; {{ $exam->completed_count }} Selesai
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-center gap-1 small text-secondary">
                                            <span>Belum Mulai: <strong>{{ $exam->not_started_count }}</strong></span>
                                            &bull;
                                            <span>Total: <strong>{{ $exam->participants_count }}</strong></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $showRoute = auth()->user()->isTeacher() && !auth()->user()->isAdmin()
                                                ? route('teacher.monitoring.show', $exam)
                                                : route('admin.monitoring.show', $exam);
                                        @endphp
                                        <a href="{{ $showRoute }}" class="btn btn-primary btn-sm fw-bold">
                                            &#128065; Pantau Ujian
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <div class="fs-4 mb-2">&#128065;</div>
                                        <div>Belum ada data ujian yang tersedia untuk dimonitor.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($exams->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    {{ $exams->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    setInterval(function() {
        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' +
                        String(now.getMinutes()).padStart(2, '0') + ':' +
                        String(now.getSeconds()).padStart(2, '0');
        const elem = document.getElementById('clockDisplay');
        if (elem) elem.textContent = timeStr;
    }, 1000);
</script>
@endsection
