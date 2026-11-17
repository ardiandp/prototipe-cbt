<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Hasil Ujian — {{ $student->name }} ({{ $exam->code }}) - CBT School</title>
    <!-- Local AdminLTE / Bootstrap CSS (strictly offline / LAN compliant) -->
    <link rel="stylesheet" href="{{ asset('theme/adminLTE/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
        }
        .sheet-container {
            max-width: 920px;
            margin: 25px auto;
            background: #ffffff;
            padding: 40px 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 4px;
        }
        .kop-surat {
            border-bottom: 3px double #111;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .kop-logo {
            width: 65px;
            height: 65px;
            border-radius: 10px;
            background: #0d6efd;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 13px;
        }
        .question-item {
            page-break-inside: avoid;
            margin-bottom: 16px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .signature-block {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        @media print {
            body {
                background: #ffffff !important;
                font-size: 11pt;
            }
            .sheet-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>

<!-- Floating Action Bar -->
<div class="no-print bg-dark py-2 px-3 text-white sticky-top shadow-sm mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold">&#128424; Cetak Lembar Hasil & Rekap Jawaban Ujian Siswa</div>
            <div class="text-secondary small">{{ $exam->title }} ({{ $exam->code }}) &bull; {{ $student->name }}</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-3">
                &#128424; Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()" class="btn btn-outline-light btn-sm">
                Tutup
            </button>
        </div>
    </div>
</div>

<div class="sheet-container">
    <!-- Kop Surat -->
    <div class="kop-surat">
        <div class="row align-items-center">
            <div class="col-2 text-center">
                <div class="kop-logo">CBT</div>
            </div>
            <div class="col-10 text-center">
                <h4 class="fw-bold text-uppercase mb-0" style="letter-spacing: 1.5px;">{{ $school->name }}</h4>
                <div class="text-secondary small mt-1">
                    NPSN: <strong>{{ $school->npsn }}</strong> &bull; Alamat: {{ $school->address }}
                </div>
                <div class="text-secondary small">
                    Telepon: {{ $school->phone ?? '-' }} &bull; Email: {{ $school->email ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1" style="text-decoration: underline; letter-spacing: 1px;">
            LEMBAR HASIL ASESMEN UJIAN BERBASIS KOMPUTER
        </h5>
        <div class="font-monospace text-secondary small">NO. DOKUMEN: CBT/LHU/{{ $exam->code }}/{{ $student->nis }}</div>
    </div>

    <!-- Metadata Peserta & Ujian -->
    <div class="card border mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-6">
                    <table class="meta-table w-100">
                        <tr>
                            <td class="text-secondary" style="width: 130px;">Nama Siswa</td>
                            <td style="width: 10px;">:</td>
                            <td class="fw-bold text-dark">{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">NIS / NISN</td>
                            <td>:</td>
                            <td class="font-monospace fw-bold">{{ $student->nis }} / {{ $student->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Kelas / Jurusan</td>
                            <td>:</td>
                            <td>{{ $student->classroom->name ?? '-' }} ({{ $student->classroom->department->code ?? 'RPL' }})</td>
                        </tr>
                    </table>
                </div>
                <div class="col-6 border-start">
                    <table class="meta-table w-100">
                        <tr>
                            <td class="text-secondary" style="width: 130px;">Mata Pelajaran</td>
                            <td style="width: 10px;">:</td>
                            <td class="fw-bold">{{ $exam->subject->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Nama Asesmen</td>
                            <td>:</td>
                            <td>{{ $exam->title }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Waktu Pengerjaan</td>
                            <td>:</td>
                            <td>
                                {{ $attempt->started_at?->format('d/m/Y H:i') }} &mdash; {{ $attempt->completed_at?->format('H:i') }}
                                ({{ max(0, $attempt->started_at?->diffInMinutes($attempt->completed_at)) }} menit)
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Summary Box -->
    <div class="row g-2 mb-4 text-center">
        <div class="col-3">
            <div class="p-3 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Nilai Akhir</div>
                <div class="fs-2 fw-bold {{ (float) $attempt->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                    {{ number_format($attempt->score, 1) }}
                </div>
                <div class="small text-secondary">Skala 0 - 100</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Status KKM</div>
                <div class="mt-2">
                    @if((float) $attempt->score >= (float) $exam->passing_grade)
                        <span class="badge text-bg-success fs-7 px-3 py-2">&#10003; TUNTAS KKM</span>
                    @else
                        <span class="badge text-bg-danger fs-7 px-3 py-2">&#9888; REMEDIAL</span>
                    @endif
                </div>
                <div class="small text-secondary mt-1">KKM: {{ number_format($exam->passing_grade, 0) }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Jawaban Benar</div>
                <div class="fs-2 fw-bold text-success">{{ $attempt->total_correct }}</div>
                <div class="small text-secondary">Dari {{ $attempt->attemptQuestions->count() }} Soal</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Jawaban Salah</div>
                <div class="fs-2 fw-bold text-danger">{{ $attempt->total_incorrect }}</div>
                <div class="small text-secondary">Kosong: {{ $attempt->total_unanswered }}</div>
            </div>
        </div>
    </div>

    <!-- Lembar Jawaban & Pembahasan Soal -->
    <h6 class="fw-bold mb-3">&#128221; Rincian Lembar Jawaban Peserta</h6>
    @foreach($attempt->attemptQuestions as $aq)
        @php
            $q = $aq->question;
            $isCorrect = (bool) $aq->is_correct;
            $isAnswered = $aq->isAnswered();
        @endphp
        <div class="question-item p-3 {{ $isAnswered ? ($isCorrect ? 'border-success bg-light' : 'border-danger') : 'border-secondary' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="badge text-bg-dark font-monospace">Soal #{{ $aq->order }}</span>
                    <span class="text-secondary small ms-2">Bobot: {{ number_format($q->weight ?? 1, 1) }}</span>
                </div>
                <div>
                    @if(! $isAnswered)
                        <span class="badge text-bg-secondary">Tidak Dijawab (0.0)</span>
                    @elseif($isCorrect)
                        <span class="badge text-bg-success">&#10003; Benar (+{{ number_format($aq->earned_weight, 1) }})</span>
                    @else
                        <span class="badge text-bg-danger">&#10007; Salah (0.0)</span>
                    @endif
                </div>
            </div>
            <div class="small text-dark mb-2">
                {!! nl2br(e($q->question_text ?? '')) !!}
            </div>
            <div class="list-group list-group-flush border-top pt-2">
                @foreach($aq->options as $attemptOpt)
                    @php
                        $opt = $attemptOpt->questionOption;
                        $isSelected = $aq->selected_exam_question_option_id === $opt?->id;
                        $isKey = (bool) $opt?->is_correct;
                    @endphp
                    <div class="list-group-item py-1 px-2 d-flex align-items-center {{ $isSelected ? ($isKey ? 'list-group-item-success' : 'list-group-item-danger') : ($isKey ? 'list-group-item-light fw-bold text-success' : '') }}">
                        <span class="badge {{ $isSelected ? 'text-bg-primary' : 'text-bg-light border text-dark' }} font-monospace me-2">
                            {{ $attemptOpt->display_label ?? $opt?->label }}
                        </span>
                        <span class="flex-grow-1 small">{{ $attemptOpt->option_text }}</span>
                        @if($isSelected && $isKey)
                            <span class="badge text-bg-success small ms-2">&#10003; Pilihan Siswa (Benar)</span>
                        @elseif($isSelected && ! $isKey)
                            <span class="badge text-bg-danger small ms-2">&#10007; Pilihan Siswa (Salah)</span>
                        @elseif(! $isSelected && $isKey)
                            <span class="badge text-bg-outline-success border border-success text-success small ms-2">&#10003; Kunci</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Tanda Tangan -->
    <div class="signature-block">
        <div class="row text-center">
            <div class="col-6">
                <div class="small text-secondary mb-1">Peserta Ujian,</div>
                <div class="fw-semibold">{{ $student->name }}</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">{{ $student->name }}</div>
                <div class="small text-secondary font-monospace">NIS: {{ $student->nis }}</div>
            </div>
            <div class="col-6">
                <div class="small text-secondary mb-1">Jakarta, {{ now()->format('d/m/Y') }}</div>
                <div class="fw-semibold">Guru Pengampu / Pengawas Ujian</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">{{ $exam->teacher->name ?? ($exam->teacher->user->name ?? 'Budi Santoso, S.Pd., M.Kom.') }}</div>
                <div class="small text-secondary font-monospace">NIP: {{ $exam->teacher->nip ?? '19800512 200501 1 003' }}</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
