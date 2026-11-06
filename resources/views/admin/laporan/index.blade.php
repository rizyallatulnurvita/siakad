@extends('layouts.app')
@section('title', 'Laporan Akademik')
@section('content')
<div class="row g-3">
    @foreach ($reports as $key => $r)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('admin.laporan.show', $key) }}" class="card h-100 text-decoration-none text-body">
                <div class="card-body d-flex gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary flex-shrink-0"><i class="bi {{ $r['icon'] }}"></i></div>
                    <div><div class="fw-semibold">{{ $r['title'] }}</div><div class="small text-muted">{{ $r['desc'] }}</div></div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
