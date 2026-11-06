@extends('layouts.guest')
@section('title', '419')
@section('content')
<div class="card shadow-lg text-center">
    <div class="card-body p-5">
        <div class="display-3 fw-semibold" style="color:#12263a">419</div>
        <h1 class="h5 mt-2">Sesi Berakhir</h1>
        <p class="text-muted">Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
