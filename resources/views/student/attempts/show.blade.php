@extends('layouts.app')

@section('title', 'Ruang Ujian - ' . $attempt->exam->title . ' - CBT School')
@section('page_title', 'Ruang Pengerjaan Asesmen')
@section('page_subtitle', $attempt->exam->title . ' (' . $attempt->exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Siswa</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item active" aria-current="page">Attempt #{{ $attempt->attempt_number }}</li>
@endsection

@section('content')
<!-- Top Bar Informasi & Server Timer -->
<div class="row mb-3">
    <div class="col-12">
        <div class="p-3 border rounded bg-white shadow-xs d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge text-bg-primary me-2 fs-7">
                    Attempt #{{ $attempt->attempt_number }}
                </span>
                <span class="fw-bold fs-6 me-3">{{ $attempt->exam->title }}</span>
                <span class="text-secondary small">Mapel: <strong>{{ $attempt->exam->subject?->name }}</strong></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Pengatur Ukuran Font -->
                <div class="btn-group btn-group-sm d-none d-sm-inline-flex" role="group" aria-label="Ukuran Font">
                    <button type="button" class="btn btn-outline-secondary" id="fontSmBtn" title="Font Kecil">A-</button>
                    <button type="button" class="btn btn-outline-secondary active" id="fontMdBtn" title="Font Normal">A</button>
                    <button type="button" class="btn btn-outline-secondary" id="fontLgBtn" title="Font Besar">A+</button>
                </div>

                <!-- Status Autosave Indikator -->
                <span id="autosaveStatus" class="badge text-bg-light border text-success small d-flex align-items-center gap-1 px-2 py-1">
                    <span class="indicator-dot" style="width: 8px; height: 8px; background-color: #198754; border-radius: 50%; display: inline-block;"></span>
                    <span class="status-text">Tersimpan di Server</span>
                </span>

                <!-- Server Timer Display -->
                <div class="p-2 px-3 border border-danger rounded bg-danger-subtle d-flex align-items-center gap-2">
                    <span class="fs-5">&#9201;</span>
                    <div>
                        <div class="small text-danger fw-bold lh-1" style="font-size: 0.7rem;">SISA WAKTU</div>
                        <div class="fs-5 fw-bold font-monospace text-danger" id="timerDisplay">
                            {{ gmdate('H:i:s', $attempt->remaining_seconds) }}
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Panel Nomor (Khusus Mobile) -->
                <button type="button" class="btn btn-outline-primary btn-sm d-lg-none" data-bs-toggle="collapse" data-bs-target="#paletteCollapse" aria-expanded="false" aria-controls="paletteCollapse">
                    &#128221; Daftar Soal
                </button>

                <!-- Tombol Selesai Ujian -->
                <button type="button" class="btn btn-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#confirmFinishModal">
                    &#10003; Selesaikan Ujian
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Lembar Pengerjaan Soal (Single Question View per Tab) -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold fs-6 text-dark" id="currentQuestionTitle">
                    Soal Nomor 1 dari {{ $totalQuestions }}
                </span>
                <span class="badge text-bg-light border text-secondary small" id="currentQuestionWeight">
                    Bobot: 0
                </span>
            </div>
            <div class="card-body p-4 cbt-question-content" id="cbtQuestionContent">
                @foreach($attempt->attemptQuestions as $index => $aq)
                    <div class="question-pane {{ $index === 0 ? 'd-block' : 'd-none' }}" id="question-pane-{{ $aq->order }}" data-order="{{ $aq->order }}" data-aq-id="{{ $aq->id }}" data-weight="{{ $aq->question?->weight }}">
                        <!-- Teks Pertanyaan -->
                        <div class="fs-6 fw-semibold text-body mb-3 question-text">
                            {!! nl2br(e($aq->question?->question_text)) !!}
                        </div>

                        <!-- Gambar Soal Jika Ada -->
                        @if($aq->question?->hasImage())
                            <div class="mb-3">
                                <img src="{{ $aq->question->image_url }}" alt="Gambar Soal #{{ $aq->order }}" class="img-fluid border rounded" style="max-height: 280px;">
                            </div>
                        @endif

                        <hr class="my-3">

                        <!-- Pilihan Opsi Jawaban (A–E) -->
                        <div class="d-flex flex-column gap-2 options-container">
                            @foreach($aq->options as $opt)
                                @php
                                    $isSelected = ($aq->selected_exam_question_option_id === $opt->exam_question_option_id);
                                @endphp
                                <label class="cbt-option-item p-3 border rounded d-flex align-items-start gap-3 cursor-pointer transition {{ $isSelected ? 'border-primary bg-primary-subtle text-primary fw-semibold' : 'bg-white' }}" for="opt_{{ $aq->id }}_{{ $opt->id }}">
                                    <input type="radio" 
                                           name="answer_{{ $aq->id }}" 
                                           id="opt_{{ $aq->id }}_{{ $opt->id }}" 
                                           value="{{ $opt->exam_question_option_id }}" 
                                           class="form-check-input mt-1 option-radio" 
                                           data-aq-id="{{ $aq->id }}"
                                           data-order="{{ $aq->order }}"
                                           data-opt-id="{{ $opt->exam_question_option_id }}"
                                           {{ $isSelected ? 'checked' : '' }}>
                                    <span class="badge {{ $isSelected ? 'text-bg-primary' : 'text-bg-light border text-dark' }} font-monospace fs-7 px-2 py-1 option-badge">
                                        {{ $opt->display_label }}
                                    </span>
                                    <div class="flex-grow-1 text-dark option-text-body">
                                        {{ $opt->option_text }}
                                        @if($opt->hasImage())
                                            <div class="mt-2">
                                                <img src="{{ $opt->image_url }}" alt="Gambar Opsi {{ $opt->display_label }}" class="img-fluid border rounded" style="max-height: 140px;">
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer Bar Navigasi Lembar Soal -->
            <div class="card-footer bg-white border-top p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Tombol Sebelumnya -->
                <button type="button" class="btn btn-outline-secondary px-3" id="prevQuestionBtn" disabled>
                    &larr; Soal Sebelumnya
                </button>

                <!-- Checkbox Ragu-ragu -->
                <div class="form-check form-switch d-flex align-items-center gap-2 bg-light px-3 py-2 border rounded">
                    <input class="form-check-input" type="checkbox" role="switch" id="doubtfulSwitch">
                    <label class="form-check-label small fw-bold text-dark cursor-pointer mb-0" for="doubtfulSwitch">
                        &#9873; Ragu-ragu
                    </label>
                </div>

                <!-- Tombol Berikutnya -->
                <button type="button" class="btn btn-primary px-3 fw-bold" id="nextQuestionBtn">
                    Soal Berikutnya &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Navigasi Nomor Soal (Palette Grid) -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border collapse d-lg-block" id="paletteCollapse">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold small text-dark">&#128196; NAVIGASI NOMOR SOAL</span>
                <span class="badge text-bg-light border text-primary small">
                    {{ $totalQuestions }} Soal
                </span>
            </div>
            <div class="card-body p-3">
                <!-- Grid Kotak Nomor Soal -->
                <div class="d-grid gap-2 mb-3" style="grid-template-columns: repeat(5, 1fr);" id="paletteGrid">
                    @foreach($attempt->attemptQuestions as $index => $aq)
                        @php
                            $isAnswered = !is_null($aq->selected_exam_question_option_id);
                            $isDoubtful = (bool) $aq->is_doubtful;
                            $isActive = ($index === 0);

                            if ($isDoubtful) {
                                $btnClass = 'btn-warning text-dark';
                            } elseif ($isAnswered) {
                                $btnClass = 'btn-success';
                            } else {
                                $btnClass = 'btn-light border text-secondary';
                            }
                        @endphp
                        <button type="button" 
                                class="btn {{ $btnClass }} fw-bold p-2 palette-item {{ $isActive ? 'palette-active' : '' }}" 
                                id="palette-btn-{{ $aq->order }}" 
                                data-order="{{ $aq->order }}"
                                data-answered="{{ $isAnswered ? '1' : '0' }}"
                                data-doubtful="{{ $isDoubtful ? '1' : '0' }}"
                                title="Soal No. {{ $aq->order }}">
                            {{ $aq->order }}
                        </button>
                    @endforeach
                </div>

                <hr class="my-3">

                <!-- Legend / Ringkasan Status Indikator -->
                <div class="small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary"><span class="badge text-bg-success me-1">&#10003;</span> Sudah Dijawab</span>
                        <strong class="text-success" id="answeredCountBadge">{{ $answeredCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary"><span class="badge text-bg-warning text-dark me-1">&#9873;</span> Ragu-ragu</span>
                        <strong class="text-warning" id="doubtfulCountBadge">{{ $doubtfulCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary"><span class="badge text-bg-light border text-secondary me-1">&#9675;</span> Belum Dijawab</span>
                        <strong class="text-secondary" id="unansweredCountBadge">{{ $unansweredCount }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light p-3">
                <button type="button" class="btn btn-outline-danger w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#confirmFinishModal">
                    &#10003; Selesaikan Ujian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Selesai Ujian -->
<div class="modal fade" id="confirmFinishModal" tabindex="-1" aria-labelledby="confirmFinishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="confirmFinishModalLabel">Konfirmasi Selesai Ujian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-start">
                <p class="mb-3">Apakah Anda yakin ingin menyelesaikan sesi ujian ini? Pastikan Anda telah memeriksa seluruh butir jawaban.</p>

                <!-- Ringkasan Jawaban di Modal -->
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="fw-bold small text-dark mb-2">Ringkasan Status Pengerjaan:</div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Total Soal:</span>
                        <strong class="font-monospace">{{ $totalQuestions }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-success">&#10003; Sudah Dijawab:</span>
                        <strong class="text-success font-monospace" id="modalAnsweredCount">{{ $answeredCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-warning">&#9873; Ragu-ragu:</span>
                        <strong class="text-warning font-monospace" id="modalDoubtfulCount">{{ $doubtfulCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-danger">&#9675; Belum Dijawab:</span>
                        <strong class="text-danger font-monospace" id="modalUnansweredCount">{{ $unansweredCount }}</strong>
                    </div>
                </div>

                <div class="alert alert-warning small mb-0" id="modalWarningBox">
                    Perhatian: Setelah Anda menekan tombol selesaikan ujian, Anda <strong>tidak dapat lagi mengubah jawaban</strong> atau mengulang kembali ujian ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Periksa Kembali</button>
                <form method="POST" action="{{ route('student.attempts.finish', $attempt) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger fw-bold">
                        Ya, Selesaikan Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.transition { transition: all 0.2s ease-in-out; }
.palette-active {
    box-shadow: 0 0 0 3px #0d6efd !important;
    transform: scale(1.05);
}
.cbt-option-item:hover {
    border-color: #0d6efd !important;
}
.cbt-font-sm .question-text { font-size: 0.85rem !important; }
.cbt-font-sm .option-text-body { font-size: 0.82rem !important; }
.cbt-font-md .question-text { font-size: 1rem !important; }
.cbt-font-md .option-text-body { font-size: 0.95rem !important; }
.cbt-font-lg .question-text { font-size: 1.2rem !important; }
.cbt-font-lg .option-text-body { font-size: 1.1rem !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalQuestions = {{ $totalQuestions }};
    let currentOrder = 1;

    // Elements
    const prevBtn = document.getElementById('prevQuestionBtn');
    const nextBtn = document.getElementById('nextQuestionBtn');
    const doubtfulSwitch = document.getElementById('doubtfulSwitch');
    const titleEl = document.getElementById('currentQuestionTitle');
    const weightEl = document.getElementById('currentQuestionWeight');
    const answeredBadge = document.getElementById('answeredCountBadge');
    const doubtfulBadge = document.getElementById('doubtfulCountBadge');
    const unansweredBadge = document.getElementById('unansweredCountBadge');
    const modalAnswered = document.getElementById('modalAnsweredCount');
    const modalDoubtful = document.getElementById('modalDoubtfulCount');
    const modalUnanswered = document.getElementById('modalUnansweredCount');

    // Function to show question by order
    function showQuestion(order) {
        if (order < 1 || order > totalQuestions) return;

        currentOrder = order;

        // Hide all panes, show target
        document.querySelectorAll('.question-pane').forEach(pane => {
            pane.classList.remove('d-block');
            pane.classList.add('d-none');
        });

        const activePane = document.getElementById('question-pane-' + order);
        if (activePane) {
            activePane.classList.remove('d-none');
            activePane.classList.add('d-block');

            const weight = activePane.getAttribute('data-weight') || '0';
            if (titleEl) titleEl.textContent = 'Soal Nomor ' + order + ' dari ' + totalQuestions;
            if (weightEl) weightEl.textContent = 'Bobot: ' + weight;
        }

        // Update Nav Buttons
        if (prevBtn) prevBtn.disabled = (order === 1);
        if (nextBtn) {
            if (order === totalQuestions) {
                nextBtn.textContent = 'Selesai \u2713';
                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-danger');
            } else {
                nextBtn.textContent = 'Soal Berikutnya \u2192';
                nextBtn.classList.remove('btn-danger');
                nextBtn.classList.add('btn-primary');
            }
        }

        // Update Palette Active Outline
        document.querySelectorAll('.palette-item').forEach(btn => btn.classList.remove('palette-active'));
        const activePaletteBtn = document.getElementById('palette-btn-' + order);
        if (activePaletteBtn) {
            activePaletteBtn.classList.add('palette-active');
            
            // Sync doubtful switch
            const isDoubtful = activePaletteBtn.getAttribute('data-doubtful') === '1';
            if (doubtfulSwitch) doubtfulSwitch.checked = isDoubtful;
        }
    }

    // Palette item clicks
    document.querySelectorAll('.palette-item').forEach(btn => {
        btn.addEventListener('click', function () {
            const order = parseInt(this.getAttribute('data-order'), 10);
            showQuestion(order);
        });
    });

    // Prev & Next Buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentOrder > 1) showQuestion(currentOrder - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (currentOrder < totalQuestions) {
                showQuestion(currentOrder + 1);
            } else {
                // Open Finish Modal on last question
                const modalEl = document.getElementById('confirmFinishModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else if (modalEl) {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                }
            }
        });
    }

    // Autosave Constants & Handlers (P6-T05)
    const saveAnswerUrl = "{{ route('student.attempts.save-answer', $attempt) }}";
    const autosaveStatusEl = document.getElementById('autosaveStatus');

    function setAutosaveStatus(state, msg) {
        if (!autosaveStatusEl) return;
        const dot = autosaveStatusEl.querySelector('.indicator-dot');
        const text = autosaveStatusEl.querySelector('.status-text');

        autosaveStatusEl.classList.remove('text-bg-light', 'text-bg-success', 'text-bg-warning', 'text-bg-danger', 'text-success', 'text-dark', 'text-white');

        if (state === 'saving') {
            autosaveStatusEl.classList.add('text-bg-warning', 'text-dark');
            if (dot) dot.style.backgroundColor = '#ffc107';
            if (text) text.textContent = msg || 'Menyimpan...';
        } else if (state === 'saved') {
            autosaveStatusEl.classList.add('text-bg-light', 'border', 'text-success');
            if (dot) dot.style.backgroundColor = '#198754';
            if (text) text.textContent = msg || 'Tersimpan di Server';
        } else if (state === 'failed') {
            autosaveStatusEl.classList.add('text-bg-danger', 'text-white');
            if (dot) dot.style.backgroundColor = '#fff';
            if (text) text.textContent = msg || 'Gagal Menyimpan!';
        }
    }

    function postAnswerUpdate(order) {
        const activePane = document.getElementById('question-pane-' + order);
        if (!activePane) return;

        const aqId = activePane.getAttribute('data-aq-id');
        const checkedRadio = activePane.querySelector('.option-radio:checked');
        const optId = checkedRadio ? checkedRadio.value : null;
        const paletteBtn = document.getElementById('palette-btn-' + order);
        const isDoubtful = paletteBtn ? (paletteBtn.getAttribute('data-doubtful') === '1') : false;

        setAutosaveStatus('saving', 'Menyimpan jawaban...');

        fetch(saveAnswerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                exam_attempt_question_id: aqId,
                exam_question_option_id: optId,
                is_doubtful: isDoubtful ? 1 : 0,
                client_timestamp: Date.now()
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errData => { throw errData; });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                setAutosaveStatus('saved', 'Tersimpan di Server');
                if (paletteBtn) {
                    paletteBtn.setAttribute('data-answered', data.selected_exam_question_option_id ? '1' : '0');
                    paletteBtn.setAttribute('data-doubtful', data.is_doubtful ? '1' : '0');
                    updatePaletteButtonStyle(paletteBtn);
                }
                if (answeredBadge && typeof data.answered_count === 'number') answeredBadge.textContent = data.answered_count;
                if (doubtfulBadge && typeof data.doubtful_count === 'number') doubtfulBadge.textContent = data.doubtful_count;
                if (unansweredBadge && typeof data.unanswered_count === 'number') unansweredBadge.textContent = data.unanswered_count;
                if (modalAnswered && typeof data.answered_count === 'number') modalAnswered.textContent = data.answered_count;
                if (modalDoubtful && typeof data.doubtful_count === 'number') modalDoubtful.textContent = data.doubtful_count;
                if (modalUnanswered && typeof data.unanswered_count === 'number') modalUnanswered.textContent = data.unanswered_count;
            }
        })
        .catch(err => {
            console.error('Autosave error:', err);
            if (err && err.is_expired) {
                handleExpiration(err.redirect_url);
            } else {
                setAutosaveStatus('failed', 'Gagal Menyimpan! Cek Koneksi');
            }
        });
    }

    // Option change handler
    document.querySelectorAll('.option-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const order = parseInt(this.getAttribute('data-order'), 10);
            const parentPane = document.getElementById('question-pane-' + order);
            
            // Visual style reset on pane options
            if (parentPane) {
                parentPane.querySelectorAll('.cbt-option-item').forEach(item => {
                    item.classList.remove('border-primary', 'bg-primary-subtle', 'text-primary', 'fw-semibold');
                    item.classList.add('bg-white');
                    const b = item.querySelector('.option-badge');
                    if (b) {
                        b.classList.remove('text-bg-primary');
                        b.classList.add('text-bg-light', 'border', 'text-dark');
                    }
                });

                const checkedLabel = this.closest('.cbt-option-item');
                if (checkedLabel) {
                    checkedLabel.classList.remove('bg-white');
                    checkedLabel.classList.add('border-primary', 'bg-primary-subtle', 'text-primary', 'fw-semibold');
                    const b = checkedLabel.querySelector('.option-badge');
                    if (b) {
                        b.classList.remove('text-bg-light', 'border', 'text-dark');
                        b.classList.add('text-bg-primary');
                    }
                }
            }

            // Update palette item state
            const paletteBtn = document.getElementById('palette-btn-' + order);
            if (paletteBtn) {
                paletteBtn.setAttribute('data-answered', '1');
                updatePaletteButtonStyle(paletteBtn);
            }

            recalculateSummary();
            postAnswerUpdate(order);
        });
    });

    // Doubtful Switch handler
    if (doubtfulSwitch) {
        doubtfulSwitch.addEventListener('change', function () {
            const paletteBtn = document.getElementById('palette-btn-' + currentOrder);
            if (paletteBtn) {
                paletteBtn.setAttribute('data-doubtful', this.checked ? '1' : '0');
                updatePaletteButtonStyle(paletteBtn);
            }
            recalculateSummary();
            postAnswerUpdate(currentOrder);
        });
    }

    function updatePaletteButtonStyle(paletteBtn) {
        const isAnswered = paletteBtn.getAttribute('data-answered') === '1';
        const isDoubtful = paletteBtn.getAttribute('data-doubtful') === '1';

        paletteBtn.classList.remove('btn-warning', 'text-dark', 'btn-success', 'btn-light', 'border', 'text-secondary');

        if (isDoubtful) {
            paletteBtn.classList.add('btn-warning', 'text-dark');
        } else if (isAnswered) {
            paletteBtn.classList.add('btn-success');
        } else {
            paletteBtn.classList.add('btn-light', 'border', 'text-secondary');
        }
    }

    function recalculateSummary() {
        let answered = 0;
        let doubtful = 0;
        let unanswered = 0;

        document.querySelectorAll('.palette-item').forEach(btn => {
            const isAns = btn.getAttribute('data-answered') === '1';
            const isDoubt = btn.getAttribute('data-doubtful') === '1';

            if (isDoubt) {
                doubtful++;
            } else if (isAns) {
                answered++;
            } else {
                unanswered++;
            }
        });

        if (answeredBadge) answeredBadge.textContent = answered;
        if (doubtfulBadge) doubtfulBadge.textContent = doubtful;
        if (unansweredBadge) unansweredBadge.textContent = unanswered;

        if (modalAnswered) modalAnswered.textContent = answered;
        if (modalDoubtful) modalDoubtful.textContent = doubtful;
        if (modalUnanswered) modalUnanswered.textContent = unanswered;
    }

    // Font Sizing Toggles
    const contentBox = document.getElementById('cbtQuestionContent');
    const smBtn = document.getElementById('fontSmBtn');
    const mdBtn = document.getElementById('fontMdBtn');
    const lgBtn = document.getElementById('fontLgBtn');

    if (smBtn && mdBtn && lgBtn && contentBox) {
        smBtn.addEventListener('click', function () {
            contentBox.classList.remove('cbt-font-md', 'cbt-font-lg');
            contentBox.classList.add('cbt-font-sm');
            smBtn.classList.add('active');
            mdBtn.classList.remove('active');
            lgBtn.classList.remove('active');
        });
        mdBtn.addEventListener('click', function () {
            contentBox.classList.remove('cbt-font-sm', 'cbt-font-lg');
            contentBox.classList.add('cbt-font-md');
            mdBtn.classList.add('active');
            smBtn.classList.remove('active');
            lgBtn.classList.remove('active');
        });
        lgBtn.addEventListener('click', function () {
            contentBox.classList.remove('cbt-font-sm', 'cbt-font-md');
            contentBox.classList.add('cbt-font-lg');
            lgBtn.classList.add('active');
            smBtn.classList.remove('active');
            mdBtn.classList.remove('active');
        });
    }

    // Initialize first question
    showQuestion(1);

    // Live Server Countdown Timer & Periodic Sync (P6-T04)
    let remaining = {{ $attempt->remaining_seconds }};
    const timerElem = document.getElementById('timerDisplay');
    const syncUrl = "{{ route('student.attempts.sync-time', $attempt) }}";
    const finalizeUrl = "{{ route('student.attempts.finalize-expired', $attempt) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function updateTimerDisplay(sec) {
        if (!timerElem) return;
        const safeSec = Math.max(0, sec);
        const h = Math.floor(safeSec / 3600);
        const m = Math.floor((safeSec % 3600) / 60);
        const s = safeSec % 60;

        timerElem.textContent = 
            String(h).padStart(2, '0') + ':' + 
            String(m).padStart(2, '0') + ':' + 
            String(s).padStart(2, '0');
    }

    // Function to handle expiration
    function handleExpiration(redirectUrl) {
        if (timerElem) timerElem.textContent = '00:00:00';
        alert('Waktu ujian Anda telah habis! Sesi attempt telah otomatis diselesaikan oleh server.');
        window.location.href = redirectUrl || "{{ route('student.exams.show', $attempt->exam) }}";
    }

    // Sync with server time
    function syncServerTimer() {
        fetch(syncUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_expired) {
                handleExpiration(data.redirect_url);
                return;
            }
            if (typeof data.remaining_seconds === 'number') {
                remaining = data.remaining_seconds;
                updateTimerDisplay(remaining);
            }
        })
        .catch(err => {
            console.warn('Timer sync error:', err);
        });
    }

    if (timerElem) {
        updateTimerDisplay(remaining);

        if (remaining <= 0) {
            handleExpiration();
        } else {
            const interval = setInterval(function () {
                remaining--;
                updateTimerDisplay(remaining);

                if (remaining <= 0) {
                    clearInterval(interval);
                    // Finalize to backend
                    fetch(finalizeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => handleExpiration(data.redirect_url))
                    .catch(() => handleExpiration());
                }
            }, 1000);

            // Sync with server every 30 seconds
            setInterval(syncServerTimer, 30000);
        }
    }
});
</script>
@endsection
