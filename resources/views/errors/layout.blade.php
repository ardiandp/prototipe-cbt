@extends('layouts.guest')

@section('body_class', 'bg-body-tertiary')

@section('content')
<main class="d-flex align-items-center min-vh-100 py-5 w-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 text-center">
                <div class="display-1 fw-bold @yield('code_class', 'text-warning') lh-1 mb-3">
                    @yield('code', 'Error')
                </div>
                <h1 class="h3 mb-3 fw-bold">@yield('heading', 'Terjadi Masalah')</h1>
                <p class="text-secondary mb-4">
                    @yield('message', 'Halaman atau aksi yang Anda minta tidak dapat diproses.')
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-primary shadow-sm px-4">
                        Kembali ke Beranda
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-3">
                        Halaman Sebelumnya
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
