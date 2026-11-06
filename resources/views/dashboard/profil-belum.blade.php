@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="card"><div class="card-body text-center py-5">
    <i class="bi bi-person-exclamation fs-1 text-warning"></i>
    <h5 class="mt-3">Profil akademik belum tersedia</h5>
    <p class="text-muted mb-0">Akun Anda belum terhubung ke data {{ auth()->user()->role }}. Hubungi administrator.</p>
</div></div>
@endsection
