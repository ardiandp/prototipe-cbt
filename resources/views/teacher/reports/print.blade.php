<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Hasil — {{ $exam->title }} ({{ $exam->code }})</title>
    <!-- Local AdminLTE / Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('theme/adminLTE/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
        }
        .print-container {
            max-width: 960px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 45px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        @media print {
            body {
                background: #fff !important;
                font-size: 11pt;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

<!-- Floating Action Bar (Hidden on Print) -->
<div class="no-print bg-dark py-2 px-3 text-white sticky-top shadow-sm mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold">&#128424; Cetak / Pratinjau Dokumen Rekapitulasi Nilai</span>
            <span class="text-secondary small ms-2">Format siap cetak dan simpan PDF</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
                &#128424; Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()" class="btn btn-outline-light btn-sm">
                Tutup
            </button>
        </div>
    </div>
</div>

<div class="print-container">
    <!-- Kop Surat Sekolah -->
    <div class="kop-surat text-center">
        <h3 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">{{ $school->name }}</h3>
        <div class="small text-secondary mb-1">
            NPSN: {{ $school->npsn }} &bull; Alamat: {{ $school->address }}
        </div>
        <div class="small text-secondary">
            Telepon: {{ $school->phone ?? '-' }} &bull; Email: {{ $school->email ?? '-' }}
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1" style="text-decoration: underline;">LEMBAR REKAPITULASI HASIL ASESMEN UJIAN</h5>
        <div class="fw-bold font-monospace text-secondary small">KODE DOKUMEN: CBT/REKAP/{{ $exam->code }}/{{ now()->format('Ymd') }}</div>
    </div>

    <!-- Metadata Ujian -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-secondary" style="width: 140px;">Judul Asesmen</td>
                    <td class="fw-bold">: {{ $exam->title }}</td>
                </tr>
                <tr>
                    <td class="text-secondary">Kode Ujian</td>
                    <td class="font-monospace fw-bold">: {{ $exam->code }}</td>
                </tr>
                <tr>
                    <td class="text-secondary">Mata Pelajaran</td>
                    <td>: {{ $exam->subject->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-secondary">Guru Pengampu</td>
                    <td>: {{ $exam->teacher->name ?? ($exam->teacher->user->name ?? '-') }}</td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-secondary" style="width: 140px;">Tahun / Semester</td>
                    <td>: {{ $exam->academicYear->name ?? '-' }} ({{ $exam->semester->name ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="text-secondary">Standar KKM</td>
                    <td class="fw-bold">: {{ number_format($exam->passing_grade, 0) }}</td>
                </tr>
                <tr>
                    <td class="text-secondary">Rombel / Kelas</td>
                    <td>: {{ $selectedClassroom ? $selectedClassroom->name : 'Semua Peserta' }}</td>
                </tr>
                <tr>
                    <td class="text-secondary">Waktu Cetak</td>
                    <td>: {{ now()->format('d/m/Y H:i') }} WIB</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="p-2 border rounded bg-light mb-4">
        <div class="row text-center g-2 small">
            <div class="col-2 border-end">
                <span class="text-secondary">Terdaftar:</span><br>
                <strong class="fs-6">{{ $summary['total_enrolled'] }}</strong>
            </div>
            <div class="col-2 border-end">
                <span class="text-secondary">Selesai:</span><br>
                <strong class="fs-6 text-primary">{{ $summary['total_completed'] }}</strong>
            </div>
            <div class="col-2 border-end">
                <span class="text-secondary">Rata-Rata:</span><br>
                <strong class="fs-6 {{ $summary['avg_score'] >= $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                    {{ number_format($summary['avg_score'], 1) }}
                </strong>
            </div>
            <div class="col-2 border-end">
                <span class="text-secondary">Tertinggi/Terendah:</span><br>
                <strong class="fs-6">{{ number_format($summary['max_score'], 1) }} / {{ number_format($summary['min_score'], 1) }}</strong>
            </div>
            <div class="col-2 border-end">
                <span class="text-secondary">Tuntas KKM:</span><br>
                <strong class="fs-6 text-success">{{ $summary['passed_count'] }}</strong>
            </div>
            <div class="col-2">
                <span class="text-secondary">Kelulusan:</span><br>
                <strong class="fs-6 text-success">{{ $summary['pass_percentage'] }}%</strong>
            </div>
        </div>
    </div>

    <!-- Tabel Nilai Peserta -->
    <table class="table table-bordered table-sm align-middle mb-4">
        <thead class="table-light text-center small text-uppercase">
            <tr>
                <th style="width: 45px;">Rank</th>
                <th style="width: 90px;">NIS</th>
                <th>Nama Peserta</th>
                <th style="width: 95px;">Kelas</th>
                <th style="width: 50px;">Benar</th>
                <th style="width: 50px;">Salah</th>
                <th style="width: 50px;">Kosong</th>
                <th style="width: 80px;">Durasi</th>
                <th style="width: 75px;">Nilai</th>
                <th style="width: 100px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rankings as $att)
                @php
                    $minutes = floor($att->duration_seconds / 60);
                    $seconds = $att->duration_seconds % 60;
                    $durationStr = sprintf('%dm %02ds', $minutes, $seconds);
                    $isPassed = (float) $att->score >= (float) $exam->passing_grade;
                @endphp
                <tr>
                    <td class="text-center font-monospace fw-bold">{{ $att->rank }}</td>
                    <td class="font-monospace small text-center">{{ $att->student->nis ?? '-' }}</td>
                    <td class="fw-semibold">{{ $att->student->name }}</td>
                    <td class="text-center">{{ $att->student->classroom->name ?? '-' }}</td>
                    <td class="text-center text-success">{{ $att->total_correct }}</td>
                    <td class="text-center text-danger">{{ $att->total_incorrect }}</td>
                    <td class="text-center text-secondary">{{ $att->total_unanswered }}</td>
                    <td class="text-center font-monospace small">{{ $durationStr }}</td>
                    <td class="text-center fw-bold fs-7 {{ $isPassed ? 'text-success' : 'text-danger' }}">
                        {{ number_format($att->score, 1) }}
                    </td>
                    <td class="text-center small">
                        @if($isPassed)
                            <span class="text-success fw-bold">&#10003; Tuntas</span>
                        @else
                            <span class="text-danger fw-bold">&#9888; Remedial</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-secondary">
                        Tidak ada data pengerjaan yang tercatat untuk ujian ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pejabat & Guru -->
    <div class="signature-section">
        <div class="row text-center">
            <div class="col-6">
                <div class="text-secondary small mb-1">Mengetahui,</div>
                <div class="fw-bold mb-5">Kepala Sekolah</div>
                <div class="fw-bold text-decoration-underline">{{ $school->principal_name }}</div>
                <div class="small text-secondary">NIP. ........................................</div>
            </div>
            <div class="col-6">
                <div class="text-secondary small mb-1">Ditetapkan di {{ explode(',', $school->address)[1] ?? 'Sekolah' }}, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="fw-bold mb-5">Guru Pengampu Mata Pelajaran</div>
                <div class="fw-bold text-decoration-underline">{{ $exam->teacher->name ?? ($exam->teacher->user->name ?? '-') }}</div>
                <div class="small text-secondary">NIP. {{ $exam->teacher->nip ?? '........................................' }}</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
