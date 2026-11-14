<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Start Navbar Links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <span class="navbar-toggler-icon">&#9776;</span>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <span class="nav-link text-muted fw-bold">Computer Based Test (LAN Server)</span>
            </li>
        </ul>

        <!-- End Navbar Links -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Server Time Indicator -->
            <li class="nav-item d-none d-sm-block me-3">
                <span class="badge text-bg-light border text-secondary" id="server-clock-badge">
                    <span id="server-clock">{{ now()->format('H:i:s') }}</span> WIB
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
                        <small class="badge text-bg-light text-primary text-uppercase">{{ auth()->user()->role ?? 'Super Admin' }}</small>
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
