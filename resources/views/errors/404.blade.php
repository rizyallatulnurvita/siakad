@extends('layouts.guest')
@section('title', '404')
@section('content')
<div class="card shadow-lg text-center">
    <div class="card-body p-5">
        <div class="display-3 fw-semibold" style="color:#12263a">404</div>
        <h1 class="h5 mt-2">Halaman Tidak Ditemukan</h1>
        <p class="text-muted">Halaman yang Anda cari tidak ada atau sudah dipindahkan.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
