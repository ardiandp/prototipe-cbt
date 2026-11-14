<div class="app-content-header py-3 border-bottom mb-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-4 fw-bold">@yield('page_title', 'Halaman')</h1>
                @hasSection('page_subtitle')
                    <small class="text-secondary">@yield('page_subtitle')</small>
                @endif
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Beranda</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
