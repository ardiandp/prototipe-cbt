<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand p-3 border-bottom border-secondary">
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
                        <span class="nav-icon me-2">&#128202;</span>
                        <p class="d-inline">Dashboard</p>
                    </a>
                </li>

                <!-- Theme Demo (Prototype Testing) -->
                <li class="nav-item">
                    <a href="{{ route('theme.demo') }}" class="nav-link {{ request()->routeIs('theme.demo') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#127912;</span>
                        <p class="d-inline">Theme Demo</p>
                    </a>
                </li>

                @php
                    $isGuest = ! auth()->check();
                    $isAdmin = auth()->check() && auth()->user()->isAnyAdmin();
                    $isTeacher = auth()->check() && auth()->user()->isTeacher();
                    $isStudent = auth()->check() && auth()->user()->isStudent();
                @endphp

                <!-- SECTION: SUPER ADMIN & ADMIN SEKOLAH -->
                @if($isAdmin || $isGuest)
                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Data Master</li>

                    <li class="nav-item">
                        <a href="{{ route('admin.school.profile') }}" class="nav-link {{ request()->routeIs('admin.school.profile') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#127979;</span>
                            <p class="d-inline">Profil Sekolah</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128197;</span>
                            <p class="d-inline">Tahun Ajaran</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#127891;</span>
                            <p class="d-inline">Jurusan</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#127963;</span>
                            <p class="d-inline">Kelas</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128218;</span>
                            <p class="d-inline">Mata Pelajaran</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128101;</span>
                            <p class="d-inline">Data Guru</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#127891;</span>
                            <p class="d-inline">Data Siswa</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Manajemen Ujian</li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.question-banks.index') }}" class="nav-link {{ request()->routeIs('teacher.question-banks.*') || request()->routeIs('teacher.questions.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128221;</span>
                            <p class="d-inline">Bank Soal</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.exams.index') }}" class="nav-link {{ request()->routeIs('admin.exams.*') || request()->routeIs('teacher.exams.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#9201;</span>
                            <p class="d-inline">Jadwal Ujian</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.monitoring.index') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128065;</span>
                            <p class="d-inline">Monitoring Ujian</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.results.index') }}" class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128200;</span>
                            <p class="d-inline">Hasil & Rekap Nilai</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128202;</span>
                            <p class="d-inline">Laporan & Peringkat</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128220;</span>
                            <p class="d-inline">Audit Log & Aktivitas</p>
                        </a>
                    </li>
                @endif

                <!-- SECTION: GURU PENGAMPU -->
                @if($isTeacher || $isGuest)
                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Menu Guru</li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.question-banks.index') }}" class="nav-link {{ request()->routeIs('teacher.question-banks.*') || request()->routeIs('teacher.questions.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128221;</span>
                            <p class="d-inline">Bank Soal</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.exams.index') }}" class="nav-link {{ request()->routeIs('teacher.exams.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#9201;</span>
                            <p class="d-inline">Jadwal Ujian Saya</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.monitoring.index') }}" class="nav-link {{ request()->routeIs('teacher.monitoring.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128065;</span>
                            <p class="d-inline">Monitoring Peserta</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.results.index') }}" class="nav-link {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128200;</span>
                            <p class="d-inline">Nilai & Analisis Soal</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.reports.index') }}" class="nav-link {{ request()->routeIs('teacher.reports.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128202;</span>
                            <p class="d-inline">Laporan & Peringkat</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teacher.audit-logs.index') }}" class="nav-link {{ request()->routeIs('teacher.audit-logs.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128220;</span>
                            <p class="d-inline">Audit Log & Aktivitas</p>
                        </a>
                    </li>
                @endif

                <!-- SECTION: SISWA PESERTA UJIAN -->
                @if($isStudent || $isGuest)
                    <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Menu Siswa</li>

                    <li class="nav-item">
                        <a href="{{ route('student.exams.index') }}" class="nav-link {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#9997;</span>
                            <p class="d-inline">Ujian Tersedia</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('student.results.index') }}" class="nav-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                            <span class="nav-icon me-2">&#128220;</span>
                            <p class="d-inline">Riwayat & Nilai</p>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
