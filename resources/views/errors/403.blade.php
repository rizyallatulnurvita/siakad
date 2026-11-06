@extends('layouts.guest')
@section('title', '403')
@section('content')
<div class="card shadow-lg text-center">
    <div class="card-body p-5">
        <div class="display-3 fw-semibold" style="color:#12263a">403</div>
        <h1 class="h5 mt-2">Akses Ditolak</h1>
        <p class="text-muted">{{ (isset($exception) && $exception->getMessage()) ? $exception->getMessage() : 'Anda tidak memiliki izin untuk membuka halaman ini.' }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection
