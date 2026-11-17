<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand px-3">
        <a href="{{ url('/') }}" class="brand-link text-decoration-none d-flex align-items-center">
            <img src="{{ asset('theme/adminLTE/dist/assets/img/AdminLTELogo.png') }}" alt="CBT Logo" class="brand-image opacity-75 shadow-sm me-2" style="width: 32px; height: 32px;">
            <span class="brand-text fw-bold text-white fs-5">CBT School</span>
        </a>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper py-2">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column px-2" data-lte-toggle="treeview" data-accordion="false" id="navigation">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('*/dashboard') || request()->is('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon me-2">
                            <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </span>
                        <p class="d-inline">Dashboard</p>
                    </a>
                </li>

                <!-- Theme Demo (Prototype Testing) -->
                <li class="nav-item">
                    <a href="{{ route('theme.demo') }}" class="nav-link {{ request()->routeIs('theme.demo') ? 'active' : '' }}">
                        <span class="nav-icon me-2">
                            <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </span>
                        <p class="d-inline">Theme Demo</p>
                    </a>
                </li>

                @php
                    $isLoggedIn = auth()->check();
                    $isAdmin = $isLoggedIn && auth()->user()->isAnyAdmin();
                    $isTeacher = $isLoggedIn && auth()->user()->isTeacher();
                    $isStudent = $isLoggedIn && auth()->user()->isStudent();
                @endphp

                @if($isLoggedIn)
                    <!-- SECTION: SUPER ADMIN & ADMIN SEKOLAH -->
                    @if($isAdmin)
                        <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Data Master</li>

                        <li class="nav-item">
                            <a href="{{ route('admin.school.profile') }}" class="nav-link {{ request()->routeIs('admin.school.profile') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                <p class="d-inline">Profil Sekolah</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <p class="d-inline">Tahun Ajaran</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/></svg>
                                </span>
                                <p class="d-inline">Jurusan</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </span>
                                <p class="d-inline">Kelas</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </span>
                                <p class="d-inline">Mata Pelajaran</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <p class="d-inline">Data Guru</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </span>
                                <p class="d-inline">Data Siswa</p>
                            </a>
                        </li>

                        <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Manajemen Ujian</li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.question-banks.index') }}" class="nav-link {{ request()->routeIs('teacher.question-banks.*') || request()->routeIs('teacher.questions.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <p class="d-inline">Bank Soal</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.exams.index') }}" class="nav-link {{ request()->routeIs('admin.exams.*') || request()->routeIs('teacher.exams.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <p class="d-inline">Jadwal Ujian</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.monitoring.index') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </span>
                                <p class="d-inline">Monitoring Ujian</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.results.index') }}" class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </span>
                                <p class="d-inline">Hasil & Rekap Nilai</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </span>
                                <p class="d-inline">Laporan & Peringkat</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <p class="d-inline">Audit Log & Aktivitas</p>
                            </a>
                        </li>
                    @endif

                    <!-- SECTION: GURU PENGAMPU -->
                    @if($isTeacher)
                        <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Menu Guru</li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.question-banks.index') }}" class="nav-link {{ request()->routeIs('teacher.question-banks.*') || request()->routeIs('teacher.questions.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <p class="d-inline">Bank Soal</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.exams.index') }}" class="nav-link {{ request()->routeIs('teacher.exams.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <p class="d-inline">Jadwal Ujian Saya</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.monitoring.index') }}" class="nav-link {{ request()->routeIs('teacher.monitoring.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </span>
                                <p class="d-inline">Monitoring Peserta</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.results.index') }}" class="nav-link {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </span>
                                <p class="d-inline">Nilai & Analisis Soal</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.reports.index') }}" class="nav-link {{ request()->routeIs('teacher.reports.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </span>
                                <p class="d-inline">Laporan & Peringkat</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('teacher.audit-logs.index') }}" class="nav-link {{ request()->routeIs('teacher.audit-logs.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <p class="d-inline">Audit Log & Aktivitas</p>
                            </a>
                        </li>
                    @endif

                    <!-- SECTION: SISWA PESERTA UJIAN -->
                    @if($isStudent)
                        <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Menu Siswa</li>

                        <li class="nav-item">
                            <a href="{{ route('student.exams.index') }}" class="nav-link {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </span>
                                <p class="d-inline">Ujian Tersedia</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('student.results.index') }}" class="nav-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                                <span class="nav-icon me-2">
                                    <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <p class="d-inline">Riwayat & Nilai</p>
                            </a>
                        </li>
                    @endif
                @else
                    <!-- GUEST / DEMO SHOWCASE NAVIGATION -->
                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Autentikasi</li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                            <span class="nav-icon me-2">
                                <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            </span>
                            <p class="d-inline">Login Pengguna</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Pratinjau Modul CBT</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.school.profile') }}" class="nav-link">
                            <span class="nav-icon me-2">
                                <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <p class="d-inline">Panel Admin Sekolah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('teacher.question-banks.index') }}" class="nav-link">
                            <span class="nav-icon me-2">
                                <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <p class="d-inline">Panel Guru & Bank Soal</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('student.exams.index') }}" class="nav-link">
                            <span class="nav-icon me-2">
                                <svg style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </span>
                            <p class="d-inline">Ruang Ujian Siswa</p>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
