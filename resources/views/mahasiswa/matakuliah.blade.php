@extends('layouts.app')
@section('title', 'Mata Kuliah')
@section('content')
<div class="card">
    <div class="card-header">Daftar Mata Kuliah Program Studi</div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode / nama"></div>
            <div class="col-6 col-md-2"><input type="number" min="1" max="14" name="semester" value="{{ request('semester') }}" class="form-control form-control-sm" placeholder="Semester"></div>
            <div class="col-auto"><button class="btn btn-outline-primary btn-sm">Cari</button> <a href="{{ route('mahasiswa.mata-kuliah') }}" class="btn btn-outline-secondary btn-sm">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Semester</th><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Deskripsi</th></tr></thead>
            <tbody>
            @forelse ($rows as $m)
                <tr><td>{{ $m->semester }}</td><td>{{ $m->kode }}</td><td>{{ $m->nama }}</td><td>{{ $m->sks }}</td><td class="small text-muted">{{ \Illuminate\Support\Str::limit($m->deskripsi, 90) }}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Tidak ada mata kuliah.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($rows->hasPages())<div class="card-footer bg-white">{{ $rows->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
