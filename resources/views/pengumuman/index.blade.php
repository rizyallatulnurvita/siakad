@extends('layouts.app')
@section('title', 'Pengumuman')
@section('content')
@forelse ($rows as $p)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-1">
                <h2 class="h6 mb-0">{{ $p->judul }}</h2>
                <span class="small text-muted">{{ $p->tanggal_mulai->translatedFormat('d F Y') }}</span>
            </div>
            <p class="mb-0 mt-2" style="white-space:pre-line">{{ $p->isi }}</p>
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-megaphone fs-3 d-block mb-2"></i>Belum ada pengumuman aktif.</div></div>
@endforelse
{{ $rows->links('pagination::bootstrap-5') }}
@endsection
