@extends('layouts.app')

@section('title', 'Peserta Ujian - ' . $exam->title . ' - CBT School')
@section('page_title', 'Penetapan Peserta Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Peserta Ujian</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="p-3 border rounded bg-white shadow-xs d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge text-bg-{{ $exam->computed_status_badge_class }} me-2 fs-7">
                    {{ $exam->computed_status_label }}
                </span>
                <span class="fw-bold fs-6 me-3">{{ $exam->title }}</span>
                <span class="text-secondary small">Mapel: <strong>{{ $exam->subject?->name }}</strong></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light border px-3 py-2 fs-7">
                    Total Peserta Terdaftar: <strong class="text-primary">{{ $exam->total_participants }}</strong>
                </span>
                <a href="{{ route('teacher.exams.participants.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                    &#43; Tambah Peserta Ujian
                </a>
                @if($exam->total_participants > 0)
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearAllParticipantsModal">
                        &#128465; Kosongkan Semua
                    </button>
                @endif
                <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Detail Ujian
                </a>
            </div>
        </div>
    </div>
</div>

<x-card title="Daftar Peserta Ujian Terdaftar">
    <!-- Filter Bar -->
    <form method="GET" action="{{ route('teacher.exams.participants.index', $exam) }}" class="row g-2 mb-3">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light">&#128269;</span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIS siswa..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="classroom_id" class="form-select">
                <option value="">-- Seluruh Kelas Terdaftar --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary flex-grow-1">Filter</button>
            @if(request()->hasAny(['search', 'classroom_id']))
                <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    @if($participants->isEmpty())
        <div class="text-center py-5">
            <div class="text-secondary mb-3 fs-1">&#128101;</div>
            <h5 class="fw-bold text-secondary">
                @if(request()->hasAny(['search', 'classroom_id']))
                    Tidak ada peserta yang cocok dengan kriteria pencarian
                @else
                    Belum ada peserta yang didaftarkan ke jadwal ujian ini
                @endif
            </h5>
            <p class="text-secondary small mb-3">
                @if(request()->hasAny(['search', 'classroom_id']))
                    Coba ganti kata kunci pencarian atau ubah filter kelas.
                @else
                    Silakan tambahkan peserta berdasarkan rombongan belajar (kelas) atau pilih peserta secara individual.
                @endif
            </p>
            <a href="{{ route('teacher.exams.participants.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                Tambah Peserta Sekarang
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 140px;">NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width: 160px;">Rombel / Kelas</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 150px;">Waktu Didaftarkan</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $index => $participant)
                        <tr>
                            <td>{{ $participants->firstItem() + $index }}</td>
                            <td>
                                <span class="font-monospace fw-bold">{{ $participant->student?->nis ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $participant->student?->name ?? 'Siswa tidak ditemukan' }}</div>
                                <div class="small text-secondary font-monospace">{{ $participant->student?->user?->username ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ $participant->classroom?->name ?? ($participant->student?->classroom?->name ?? 'Tanpa Kelas') }}
                                </span>
                            </td>
                            <td>
                                @if($participant->status === 'registered')
                                    <span class="badge text-bg-info text-white">Terdaftar</span>
                                @elseif($participant->status === 'present')
                                    <span class="badge text-bg-primary">Hadir</span>
                                @elseif($participant->status === 'completed')
                                    <span class="badge text-bg-success">Selesai</span>
                                @else
                                    <span class="badge text-bg-secondary">{{ ucfirst($participant->status) }}</span>
                                @endif
                            </td>
                            <td class="small text-secondary">
                                {{ $participant->created_at ? $participant->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('teacher.exams.participants.destroy', [$exam, $participant]) }}" class="d-inline" onsubmit="return confirm('Hapus siswa {{ $participant->student?->name }} dari peserta ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus dari Ujian">
                                        &#128465;
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $participants->links() }}
        </div>
    @endif
</x-card>

<!-- Modal Konfirmasi Kosongkan Semua Peserta -->
@if($exam->total_participants > 0)
<div class="modal fade" id="clearAllParticipantsModal" tabindex="-1" aria-labelledby="clearAllParticipantsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="clearAllParticipantsModalLabel">Konfirmasi Kosongkan Peserta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <p>Apakah Anda yakin ingin mengosongkan <strong>seluruh ({{ $exam->total_participants }}) peserta</strong> dari jadwal ujian ini?</p>
                <div class="alert alert-warning small mb-0">
                    Siswa yang telah dikosongkan tidak dapat mengerjakan ujian ini kecuali didaftarkan kembali.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('teacher.exams.participants.destroy-all', $exam) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Kosongkan Semua</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
