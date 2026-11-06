@extends('layouts.guest')
@section('title', '500')
@section('content')
<div class="card shadow-lg text-center">
    <div class="card-body p-5">
        <div class="display-3 fw-semibold" style="color:#12263a">500</div>
        <h1 class="h5 mt-2">Terjadi Kesalahan</h1>
        <p class="text-muted">Terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
