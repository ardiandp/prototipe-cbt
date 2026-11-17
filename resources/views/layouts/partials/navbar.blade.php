<nav class="app-header navbar navbar-expand bg-body shadow-sm">
    <div class="container-fluid">
        <!-- Start Navbar Links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"></path>
                    </svg>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <span class="nav-link text-body-secondary fw-semibold">
                    <span class="badge text-bg-primary me-2">LAN Server</span>
                    Computer Based Test (CBT School)
                </span>
            </li>
        </ul>

        <!-- End Navbar Links -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Server Time Indicator -->
            <li class="nav-item d-none d-sm-block me-3">
                <span class="badge text-bg-light border text-secondary shadow-sm py-2 px-3" id="server-clock-badge">
                    <svg class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span id="server-clock" class="fw-bold">{{ now()->format('H:i:s') }}</span> WIB
                </span>
            </li>

            <!-- User Menu Dropdown -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="{{ asset('theme/adminLTE/dist/assets/img/avatar5.png') }}" class="user-image rounded-circle shadow-sm me-2" alt="User Image" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->name ?? 'Demo User' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="user-header text-bg-primary p-3 text-center">
                        <img src="{{ asset('theme/adminLTE/dist/assets/img/avatar5.png') }}" class="rounded-circle shadow mb-2" alt="User Image" style="width: 64px; height: 64px;">
                        <p class="mb-0 fw-bold">{{ auth()->user()->name ?? 'Demo User' }}</p>
                        <small class="badge text-bg-light text-primary text-uppercase">{{ auth()->check() ? auth()->user()->roleLabel() : 'Tamu' }}</small>
                    </li>
                    <li class="user-footer d-flex justify-content-between p-2 border-top">
                        <a href="{{ url('/profile') }}" class="btn btn-sm btn-outline-secondary">Profil</a>
                        @if(auth()->check())
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Keluar</button>
                            </form>
                        @else
                            <a href="{{ url('/login') }}" class="btn btn-sm btn-outline-primary">Masuk</a>
                        @endif
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
