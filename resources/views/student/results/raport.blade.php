<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Hasil Ujian — {{ $student->name }} ({{ $student->nis }}) - CBT School</title>
    <!-- Local AdminLTE / Bootstrap CSS (strictly offline / LAN compliant) -->
    <link rel="stylesheet" href="{{ asset('theme/adminLTE/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
        }
        .raport-sheet {
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
            width: 70px;
            height: 70px;
            border-radius: 10px;
            background: #0d6efd;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
        }
        .identity-table td {
            padding: 4px 8px;
            font-size: 13px;
        }
        .grades-table th {
            background-color: #f8f9fa !important;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .grades-table td {
            vertical-align: middle;
            font-size: 13px;
        }
        .signature-block {
            margin-top: 45px;
            page-break-inside: avoid;
        }
        @media print {
            body {
                background: #ffffff !important;
                font-size: 11pt;
            }
            .raport-sheet {
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

<!-- Floating Action Bar (Hidden in Print) -->
<div class="no-print bg-dark py-2 px-3 text-white sticky-top shadow-sm mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-5">&#128424;</span>
            <div>
                <div class="fw-bold">Pratinjau Cetak Rapor Hasil Asesmen Ujian CBT</div>
                <div class="text-secondary small">Dokumen resmi nilai siswa — Siap cetak ke kertas atau simpan sebagai PDF</div>
            </div>
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

<div class="raport-sheet">
    <!-- Kop Surat Lembaga / Sekolah -->
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
                    Telepon: {{ $school->phone ?? '-' }} &bull; Email: {{ $school->email ?? '-' }} &bull; Website: cbtschool.sch.id
                </div>
            </div>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1" style="text-decoration: underline; letter-spacing: 1px;">
            LAPORAN HASIL ASESMEN SUMATIF PESERTA DIDIK
        </h5>
        <div class="fw-semibold text-secondary">
            Tahun Ajaran {{ $academicYear->name ?? '2025/2026' }} &mdash; Semester {{ $semester->name ?? 'Ganjil' }}
        </div>
        <div class="font-monospace text-muted small mt-1">NO. DOK: CBT/RAPOR/{{ $student->nis }}/{{ now()->format('Ymd') }}</div>
    </div>

    <!-- Identitas Siswa -->
    <div class="card border mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="identity-table w-100">
                        <tr>
                            <td class="text-secondary" style="width: 140px;">Nama Siswa</td>
                            <td style="width: 10px;">:</td>
                            <td class="fw-bold text-dark fs-6">{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Nomor Induk Siswa</td>
                            <td>:</td>
                            <td class="font-monospace fw-bold">{{ $student->nis }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">NISN</td>
                            <td>:</td>
                            <td class="font-monospace">{{ $student->nisn ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 border-start-md">
                    <table class="identity-table w-100">
                        <tr>
                            <td class="text-secondary" style="width: 140px;">Rombel / Kelas</td>
                            <td style="width: 10px;">:</td>
                            <td class="fw-bold">{{ $student->classroom->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Program Keahlian</td>
                            <td>:</td>
                            <td>{{ $student->classroom->department->name ?? 'Rekayasa Perangkat Lunak' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Status Siswa</td>
                            <td>:</td>
                            <td><span class="badge text-bg-success">Aktif Terdaftar</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Nilai -->
    <div class="row g-2 mb-4 text-center">
        <div class="col-3">
            <div class="p-2 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Total Asesmen</div>
                <div class="fs-4 fw-bold text-primary">{{ $totalExams }}</div>
                <div class="small text-secondary">Mata Pelajaran</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Rata-rata Nilai</div>
                <div class="fs-4 fw-bold {{ $avgScore >= 75 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($avgScore, 2) }}
                </div>
                <div class="small text-secondary">Skala 0 - 100</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Predikat Umum</div>
                <div class="fs-4 fw-bold text-dark">{{ $overallPredicate }}</div>
                <div class="small text-secondary">Kategori Prestasi</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-2 border rounded bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Status Ketuntasan</div>
                <div class="fs-4 fw-bold {{ $passedCount == $totalExams ? 'text-success' : 'text-warning' }}">
                    {{ $passedCount }} / {{ $totalExams }}
                </div>
                <div class="small text-secondary">Asesmen Tuntas KKM</div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Nilai Asesmen Ujian -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle grades-table mb-0">
            <thead>
                <tr class="text-center align-middle">
                    <th style="width: 45px;">No</th>
                    <th class="text-start">Mata Pelajaran & Judul Asesmen</th>
                    <th>Guru Pengampu</th>
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 65px;">KKM</th>
                    <th style="width: 85px;">Nilai Akhir</th>
                    <th style="width: 110px;">Predikat</th>
                    <th style="width: 130px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $idx => $g)
                    <tr>
                        <td class="text-center fw-semibold text-secondary">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $g->exam->subject->name ?? '-' }}</div>
                            <div class="small text-secondary">{{ $g->exam->title }}</div>
                            <span class="badge text-bg-light border font-monospace text-secondary" style="font-size: 10px;">{{ $g->exam->code }}</span>
                        </td>
                        <td class="text-center small">
                            {{ $g->exam->teacher->name ?? ($g->exam->teacher->user->name ?? '-') }}
                        </td>
                        <td class="text-center small">
                            {{ $g->attempt->completed_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="text-center font-monospace fw-bold">
                            {{ number_format($g->passing_grade, 0) }}
                        </td>
                        <td class="text-center font-monospace fs-6 fw-bold {{ $g->is_passed ? 'text-success' : 'text-danger' }}">
                            {{ number_format($g->score, 2) }}
                        </td>
                        <td class="text-center small fw-semibold">
                            {{ $g->predicate }}
                        </td>
                        <td class="text-center">
                            @if($g->is_passed)
                                <span class="badge text-bg-success">&#10003; Tuntas KKM</span>
                            @else
                                <span class="badge text-bg-danger">&#9888; Remedial</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">
                            Belum ada rekap nilai asesmen yang dipublikasikan untuk siswa ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="5" class="text-end py-2">RATA-RATA NILAI ASESMEN CBT :</td>
                    <td class="text-center font-monospace fs-6 {{ $avgScore >= 75 ? 'text-success' : 'text-danger' }} py-2">
                        {{ number_format($avgScore, 2) }}
                    </td>
                    <td class="text-center py-2">{{ $overallPredicate }}</td>
                    <td class="text-center py-2">
                        @if($totalExams > 0 && $passedCount == $totalExams)
                            <span class="badge text-bg-success">TUNTAS SEMUA</span>
                        @elseif($totalExams > 0)
                            <span class="badge text-bg-warning">SEBAGIAN REMEDIAL</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Catatan Akademik & Karakter -->
    <div class="card border mb-4">
        <div class="card-header bg-light py-2">
            <span class="fw-bold small text-uppercase">&#128221; Catatan Wali Kelas & Evaluasi Belajar</span>
        </div>
        <div class="card-body p-3">
            <p class="small text-dark mb-0">
                @if($avgScore >= 85)
                    Peserta didik menunjukkan penguasaan kompetensi yang luar biasa pada seluruh asesmen berbasis komputer. Pertahankan konsistensi belajar, kedisiplinan waktu, dan perluas pemahaman praktikum pada semester mendatang.
                @elseif($avgScore >= 75)
                    Peserta didik telah berhasil memenuhi Kriteria Ketuntasan Minimal (KKM). Tingkatkan kembali latihan soal mandiri dan ketelitian dalam menjawab butir soal analitis.
                @else
                    Peserta didik disarankan untuk mengikuti bimbingan remidial intensif dan mengulang evaluasi kompetensi pada mata pelajaran yang belum mencapai KKM.
                @endif
            </p>
        </div>
    </div>

    <!-- Kolom Tanda Tangan Resmi (3 Pihak) -->
    <div class="signature-block">
        <div class="row text-center">
            <div class="col-4">
                <div class="small text-secondary mb-1">Mengetahui,</div>
                <div class="fw-semibold">Orang Tua / Wali Siswa</div>
                <div style="height: 65px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">( ......................................... )</div>
                <div class="small text-secondary">Tanda Tangan & Nama Terang</div>
            </div>
            <div class="col-4">
                <div class="small text-secondary mb-1">Mengetahui,</div>
                <div class="fw-semibold">Wali Kelas {{ $student->classroom->name ?? '' }}</div>
                <div style="height: 65px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">Budi Santoso, S.Pd., M.Kom.</div>
                <div class="small text-secondary font-monospace">NIP: 19800512 200501 1 003</div>
            </div>
            <div class="col-4">
                <div class="small text-secondary mb-1">Ditetapkan di Jakarta, {{ now()->format('d/m/Y') }}</div>
                <div class="fw-semibold">Kepala Satuan Pendidikan</div>
                <div style="height: 65px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">{{ $school->principal_name ?? 'Drs. H. Mulyono, M.Pd.' }}</div>
                <div class="small text-secondary font-monospace">NIP: 19750510 200003 1 002</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
