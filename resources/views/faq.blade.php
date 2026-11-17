@extends('layouts.app')

@section('title', 'Pusat Bantuan & FAQ - CBT School')
@section('page_title', 'Pusat Bantuan & FAQ')
@section('page_subtitle', 'Panduan operasional dan tanya-jawab penggunaan sistem untuk Seluruh Role (Admin, Guru, Siswa, Pengawas)')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">FAQ</li>
@endsection

@section('content')
@php
    $isAdmin = auth()->check() && auth()->user()->isAnyAdmin();
@endphp

<!-- Hero Header Callout -->
<div class="callout callout-primary bg-white shadow-sm mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-primary">Panduan Penggunaan & Tanya-Jawab Sistem CBT School</h5>
            <p class="mb-0 text-secondary fs-7">
                Temukan panduan langkah demi langkah dan solusi kendala teknis operasional ujian berbasis komputer dalam jaringan lokal (LAN) sekolah.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($isAdmin)
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    + Tambah FAQ
                </button>
            @endif

            <a href="{{ asset('FAQ.md') }}" target="_blank" class="btn btn-sm btn-outline-secondary shadow-sm" onclick="alert('Dokumentasi lengkap juga tersedia di file FAQ.md pada root project.');">
                <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Lihat FAQ.md
            </a>
        </div>
    </div>
</div>

<!-- Category Tabs -->
<div class="card shadow-sm mb-4">
    <div class="card-header p-2 bg-body">
        <ul class="nav nav-pills flex-wrap" id="faqTabs" role="tablist">
            @foreach($categories as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} py-2 px-3 fs-7" href="#faq-{{ $key }}" data-bs-toggle="tab">
                        @if($key === 'general')
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($key === 'admin')
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @elseif($key === 'teacher')
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @elseif($key === 'student')
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        @elseif($key === 'proctor')
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        @else
                            <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @endif
                        {{ $label }}
                        <span class="badge text-bg-secondary ms-1">{{ $faqsByCategory->get($key)?->count() ?? 0 }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<!-- Tab Contents -->
<div class="tab-content" id="faqTabContent">
    @foreach($categories as $key => $label)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="faq-{{ $key }}">
            @php
                $categoryFaqs = $faqsByCategory->get($key, collect());
            @endphp

            @forelse($categoryFaqs as $faq)
                <div class="card shadow-sm mb-3 border">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                        <h6 class="fw-bold mb-0 text-primary d-flex align-items-center">
                            <span class="badge text-bg-light border text-secondary me-2">#{{ $loop->iteration }}</span>
                            {{ $faq->question }}
                        </h6>

                        @if($isAdmin)
                            <div class="d-flex align-items-center gap-1 ms-2">
                                <!-- Pencil Icon Button (Edit) -->
                                <button type="button" 
                                    class="btn btn-sm btn-outline-warning btn-edit-faq py-1 px-2" 
                                    title="Ubah FAQ"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editFaqModal"
                                    data-id="{{ $faq->id }}"
                                    data-category="{{ $faq->category }}"
                                    data-question="{{ $faq->question }}"
                                    data-answer="{{ $faq->answer }}"
                                    data-order="{{ $faq->order }}"
                                    data-action="{{ route('admin.faqs.update', $faq) }}"
                                >
                                    <svg style="width: 0.95rem; height: 0.95rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>

                                <!-- Trash Icon Button (Delete) -->
                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan FAQ ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus FAQ">
                                        <svg style="width: 0.95rem; height: 0.95rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            @empty
                <div class="card shadow-sm border text-center py-5">
                    <div class="card-body">
                        <span class="fs-1 d-block mb-2 text-muted">&#128194;</span>
                        <h6 class="fw-semibold text-secondary">Belum ada pertanyaan pada kategori ini.</h6>
                        @if($isAdmin)
                            <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                                + Tambah Pertanyaan Pertama
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    @endforeach
</div>

@if($isAdmin)
    <!-- Modal: Tambah FAQ Baru -->
    <x-modal id="addFaqModal" title="Tambah Pertanyaan FAQ Baru">
        <form action="{{ route('admin.faqs.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="add_category" class="form-label fw-semibold">Kategori FAQ <span class="text-danger">*</span></label>
                <select name="category" id="add_category" class="form-select" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="add_question" class="form-label fw-semibold">Pertanyaan / Judul FAQ <span class="text-danger">*</span></label>
                <input type="text" name="question" id="add_question" class="form-control" placeholder="Contoh: Bagaimana jika timer ujian habis?" required>
            </div>

            <div class="mb-3">
                <label for="add_order" class="form-label fw-semibold">Urutan Tampilan</label>
                <input type="number" name="order" id="add_order" class="form-control" placeholder="0" value="0" min="0">
                <small class="form-text text-muted">Nomor urut yang lebih kecil akan ditampilkan lebih atas.</small>
            </div>

            <div class="mb-3">
                <label for="add_answer" class="form-label fw-semibold">Jawaban / Penjelasan <span class="text-danger">*</span></label>
                <textarea name="answer" id="add_answer" rows="5" class="form-control" placeholder="Tuliskan jawaban atau petunjuk operasional di sini..." required></textarea>
            </div>

            <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold shadow-sm">Simpan FAQ</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Ubah FAQ -->
    <x-modal id="editFaqModal" title="Ubah Pertanyaan FAQ">
        <form id="editFaqForm" action="#" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="edit_category" class="form-label fw-semibold">Kategori FAQ <span class="text-danger">*</span></label>
                <select name="category" id="edit_category" class="form-select" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_question" class="form-label fw-semibold">Pertanyaan / Judul FAQ <span class="text-danger">*</span></label>
                <input type="text" name="question" id="edit_question" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="edit_order" class="form-label fw-semibold">Urutan Tampilan</label>
                <input type="number" name="order" id="edit_order" class="form-control" min="0">
            </div>

            <div class="mb-3">
                <label for="edit_answer" class="form-label fw-semibold">Jawaban / Penjelasan <span class="text-danger">*</span></label>
                <textarea name="answer" id="edit_answer" rows="5" class="form-control" required></textarea>
            </div>

            <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold shadow-sm">Perbarui FAQ</button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Populate Edit Modal
            const editButtons = document.querySelectorAll('.btn-edit-faq');
            const editForm = document.getElementById('editFaqForm');
            const editCategory = document.getElementById('edit_category');
            const editQuestion = document.getElementById('edit_question');
            const editAnswer = document.getElementById('edit_answer');
            const editOrder = document.getElementById('edit_order');

            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (editForm) {
                        editForm.action = this.getAttribute('data-action');
                        editCategory.value = this.getAttribute('data-category');
                        editQuestion.value = this.getAttribute('data-question');
                        editAnswer.value = this.getAttribute('data-answer');
                        editOrder.value = this.getAttribute('data-order') || '0';
                    }
                });
            });
        });
    </script>
    @endpush
@endif
@endsection
