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
                    <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
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

                <!-- Header Master Data -->
                <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Data Master</li>

                <li class="nav-item">
                    <a href="{{ url('/school-profile') }}" class="nav-link {{ request()->is('school-profile*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#127979;</span>
                        <p class="d-inline">Profil Sekolah</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/academic-years') }}" class="nav-link {{ request()->is('academic-years*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128197;</span>
                        <p class="d-inline">Tahun Ajaran</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/classes') }}" class="nav-link {{ request()->is('classes*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#127963;</span>
                        <p class="d-inline">Kelas & Jurusan</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/subjects') }}" class="nav-link {{ request()->is('subjects*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128218;</span>
                        <p class="d-inline">Mata Pelajaran</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/users') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128101;</span>
                        <p class="d-inline">Guru & Siswa</p>
                    </a>
                </li>

                <!-- Header CBT Engine -->
                <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Pelaksanaan Ujian</li>

                <li class="nav-item">
                    <a href="{{ url('/question-banks') }}" class="nav-link {{ request()->is('question-banks*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128221;</span>
                        <p class="d-inline">Bank Soal</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/exams') }}" class="nav-link {{ request()->is('exams*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#9201;</span>
                        <p class="d-inline">Jadwal Ujian</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/exam-monitoring') }}" class="nav-link {{ request()->is('exam-monitoring*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128065;</span>
                        <p class="d-inline">Monitoring Ujian</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/results') }}" class="nav-link {{ request()->is('results*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#128200;</span>
                        <p class="d-inline">Hasil & Rekap Nilai</p>
                    </a>
                </li>

                <!-- Student Quick Link -->
                <li class="nav-header text-uppercase text-secondary fs-7 mt-3 mb-1 px-3">Peserta Ujian</li>
                <li class="nav-item">
                    <a href="{{ url('/student/exams') }}" class="nav-link {{ request()->is('student*') ? 'active' : '' }}">
                        <span class="nav-icon me-2">&#9997;</span>
                        <p class="d-inline">Ruang Ujian Siswa</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
