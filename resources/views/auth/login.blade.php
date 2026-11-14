@extends('layouts.guest')

@section('title', 'Masuk - CBT School')

@section('content')
<main class="login-box">
    <!-- Brand Logo & Heading -->
    <div class="text-center mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm mb-2" style="width: 54px; height: 54px;">
            <img src="{{ asset('theme/adminLTE/dist/assets/img/AdminLTELogo.png') }}" alt="CBT Logo" style="width: 32px; height: 32px;">
        </div>
        <h2 class="h4 fw-bold text-dark mb-0">CBT School</h2>
        <span class="text-secondary fs-7">Aplikasi Ujian Berbasis Komputer (LAN)</span>
    </div>

    <!-- Login Card -->
    <div class="card card-outline card-primary shadow-sm border-0">
        <div class="card-header bg-transparent text-center border-0 pt-3 pb-0">
            <h5 class="fw-semibold text-secondary mb-0">Silakan Masuk</h5>
        </div>
        <div class="card-body login-card-body p-4">
            <!-- Flash Message / Validation Alerts -->
            @include('components.alert')

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username / Email / NISN</label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}" 
                            class="form-control @error('username') is-invalid @enderror" 
                            placeholder="Masukkan username atau email" 
                            required 
                            autofocus
                        >
                        <span class="input-group-text bg-body">&#128100;</span>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label fw-semibold mb-0">Kata Sandi</label>
                    </div>
                    <div class="input-group mt-1">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Masukkan kata sandi" 
                            required
                        >
                        <span class="input-group-text bg-body">&#128274;</span>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-7">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label fs-7 text-secondary" for="remember">
                                Ingat Saya
                            </label>
                        </div>
                    </div>
                    <div class="col-5 text-end">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm fw-semibold">
                            Masuk
                        </button>
                    </div>
                </div>
            </form>

            <div class="alert alert-light border mt-3 mb-0 p-2 fs-7 text-secondary">
                <small class="d-block fw-bold text-dark mb-1">&#128736; Demo Multi-Role Akun:</small>
                <div class="row g-1 text-nowrap">
                    <div class="col-6">&bull; <b>Admin:</b> admin / password</div>
                    <div class="col-6">&bull; <b>Guru:</b> guru / password</div>
                    <div class="col-6">&bull; <b>Siswa:</b> siswa / password</div>
                    <div class="col-6">&bull; <b>Super:</b> superadmin / password</div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent text-center border-0 pb-3 pt-0">
            <a href="{{ route('theme.demo') }}" class="fs-7 text-decoration-none text-muted">
                &larr; Lihat Halaman Theme Demo
            </a>
        </div>
    </div>
</main>
@endsection
