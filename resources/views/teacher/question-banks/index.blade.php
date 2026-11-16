@extends('layouts.app')

@section('title', 'Bank Soal - CBT School')
@section('page_title', 'Daftar Bank Soal')
@section('page_subtitle', 'Pengelolaan kategori paket butir soal asesmen sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bank Soal</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.question-banks.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama bank soal..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="subject_id" class="form-select">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $sb)
                        <option value="{{ $sb->id }}" {{ request('subject_id') == $sb->id ? 'selected' : '' }}>
                            {{ $sb->code }} - {{ $sb->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select">
                    <option value="">Semua Tingkat</option>
                    @foreach(['Semua', '10', '11', '12'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>
                            {{ $lvl === 'Semua' ? 'Semua Tingkat' : 'Tingkat ' . $lvl }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'subject_id', 'level', 'status']))
                    <a href="{{ route('teacher.question-banks.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <a href="{{ route('teacher.question-banks.create') }}" class="btn btn-primary ms-auto">
                    + Tambah Bank Soal
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Katalog Bank Soal">
    <x-table :headers="['#', 'Kode & Nama Bank Soal', 'Mata Pelajaran', 'Tingkat', 'Jumlah Soal', 'Penyusun', 'Status', 'Aksi']" :empty="$questionBanks->isEmpty()" emptyMessage="Belum ada bank soal yang dibuat. Silakan tambahkan bank soal baru.">
        @foreach($questionBanks as $index => $bank)
            <tr>
                <td>{{ $questionBanks->firstItem() + $index }}</td>
                <td>
                    <a href="{{ route('teacher.question-banks.show', $bank) }}" class="fw-bold text-decoration-none fs-6">
                        {{ $bank->name }}
                    </a>
                    <div><span class="badge text-bg-dark font-monospace">{{ $bank->code }}</span></div>
                </td>
                <td>
                    @if($bank->subject)
                        <span class="badge text-bg-info text-white">{{ $bank->subject->code }}</span>
                        <div class="small text-secondary">{{ $bank->subject->name }}</div>
                    @else
                        <span class="text-secondary">-</span>
                    @endif
                </td>
                <td>
                    <span class="badge text-bg-light border">{{ $bank->level ?? 'Semua' }}</span>
                </td>
                <td>
                    <a href="{{ route('teacher.question-banks.show', $bank) }}" class="btn btn-sm btn-outline-primary fw-bold">
                        &#128221; {{ $bank->questions_count }} Butir Soal
                    </a>
                </td>
                <td>
                    <span class="small text-secondary">{{ $bank->teacher?->name ?? 'Admin Sekolah' }}</span>
                </td>
                <td>
                    @if($bank->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('teacher.question-banks.show', $bank) }}" class="btn btn-primary" title="Kelola Soal">
                            &#128065; Soal
                        </a>
                        <a href="{{ route('teacher.question-banks.edit', $bank) }}" class="btn btn-outline-secondary" title="Edit">
                            &#9998;
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteBank{{ $bank->id }}" title="Hapus">
                            &#128465;
                        </button>
                    </div>

                    <!-- Modal Delete Bank -->
                    <x-modal id="modalDeleteBank{{ $bank->id }}" title="Konfirmasi Hapus Bank Soal">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus bank soal <strong>{{ $bank->name }}</strong> ({{ $bank->code }})?</p>
                            @if($bank->questions_count > 0)
                                <div class="alert alert-warning small">
                                    <strong>Peringatan:</strong> Menghapus bank soal ini juga akan menghapus {{ $bank->questions_count }} butir soal di dalamnya secara permanen.
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('teacher.question-banks.destroy', $bank) }}">
                            @csrf
                            @method('DELETE')
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus Bank Soal</button>
                            </div>
                        </form>
                    </x-modal>
                </td>
            </tr>
        @endforeach
    </x-table>

    @if($questionBanks->hasPages())
        <div class="p-3 border-top">
            {{ $questionBanks->links() }}
        </div>
    @endif
</x-card>
@endsection
