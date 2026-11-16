@extends('layouts.app')

@section('title', 'Profil Sekolah - CBT School')
@section('page_title', 'Profil Sekolah')
@section('page_subtitle', 'Identitas resmi satuan pendidikan untuk kop ujian, kartu peserta, dan lembar hasil')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profil Sekolah</li>
@endsection

@section('content')
<div class="row">
    <!-- Left Column: Summary Card -->
    <div class="col-lg-4 mb-4">
        <x-card class="text-center shadow-sm">
            <div class="p-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <img src="{{ asset('theme/adminLTE/dist/assets/img/AdminLTELogo.png') }}" alt="Logo Sekolah" style="width: 48px; height: 48px;">
                </div>
                <h4 class="fw-bold mb-1">{{ $profile->name }}</h4>
                <div class="mb-3">
                    <span class="badge text-bg-primary fs-7">NPSN: {{ $profile->npsn ?? '-' }}</span>
                </div>
                <hr>
                <div class="text-start">
                    <div class="mb-2">
                        <small class="text-secondary d-block fw-semibold">Kepala Sekolah:</small>
                        <span class="fw-bold text-dark">{{ $profile->principal_name ?? '-' }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-secondary d-block fw-semibold">Alamat:</small>
                        <span class="text-dark">{{ $profile->address ?? '-' }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-secondary d-block fw-semibold">Email Resmi:</small>
                        <span class="text-dark">{{ $profile->email ?? '-' }}</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-secondary d-block fw-semibold">Telepon:</small>
                        <span class="text-dark">{{ $profile->phone ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="card bg-body-tertiary border shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2">&#8505; Catatan Konfigurasi</h6>
                <small class="text-secondary">
                    Data profil sekolah ini digunakan secara otomatis pada header halaman ujian siswa, berita acara pelaksanaan ujian, serta cetak rekapitulasi nilai.
                </small>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Form -->
    <div class="col-lg-8">
        <x-card title="Ubah Informasi Sekolah" type="primary" :outline="true">
            <form action="{{ route('admin.school.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <x-form-input 
                            name="name" 
                            label="Nama Satuan Pendidikan" 
                            :value="$profile->name" 
                            placeholder="Contoh: SMA Negeri 1 CBT Indonesia" 
                            required="true"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form-input 
                            name="npsn" 
                            label="NPSN" 
                            :value="$profile->npsn" 
                            placeholder="Contoh: 20109999" 
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <x-form-input 
                            name="principal_name" 
                            label="Nama Kepala Sekolah Lengkap & Gelar" 
                            :value="$profile->principal_name" 
                            placeholder="Contoh: Drs. H. Mulyono, M.Pd." 
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="3" 
                        class="form-control @error('address') is-invalid @enderror" 
                        placeholder="Masukkan alamat lengkap sekolah, kota, dan kode pos"
                    >{{ old('address', $profile->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input 
                            name="email" 
                            label="Email Sekolah" 
                            type="email" 
                            :value="$profile->email" 
                            placeholder="Contoh: info@sman1cbt.sch.id" 
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input 
                            name="phone" 
                            label="Nomor Telepon" 
                            :value="$profile->phone" 
                            placeholder="Contoh: 021-5551234" 
                        />
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary px-3">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection

