@extends('layouts.app')
@section('title', $title)
@section('parent')<li class="breadcrumb-item"><a href="{{ route('admin.laporan.index') }}">Laporan</a></li>@endsection
@section('content')
@if (count($filters))
<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            @foreach ($filters as $f)
                @php $cur = $f['value'] ?? request($f['name']); @endphp
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ $f['label'] }}</label>
                    @if ($f['type'] === 'select')
                        <select name="{{ $f['name'] }}" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($f['options'] as $v => $l)
                                <option value="{{ $v }}" @selected((string) $cur === (string) $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    @else
                        <input name="{{ $f['name'] }}" value="{{ $cur }}" class="form-control form-control-sm">
                    @endif
                </div>
            @endforeach
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-primary btn-sm">Terapkan</button>
                <a href="{{ route('admin.laporan.show', $jenis) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $title }}</span>
        <span class="no-print d-flex gap-2">
            <a class="btn btn-sm btn-outline-success" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"><i class="bi bi-filetype-csv me-1"></i>Unduh CSV (Excel)</a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak / PDF</button>
        </span>
    </div>
    <div class="card-body pb-0">
        <div class="small text-muted">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</div>
        @isset($judul_tambahan)<div class="fw-semibold mt-1">{{ $judul_tambahan }}</div>@endisset
        @if (!empty($note))<div class="alert alert-info mt-3 mb-0">{{ $note }}</div>@endif
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead><tr><th style="width:44px">No</th>@foreach ($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse ($rows as $row)
                <tr><td>{{ $loop->iteration }}</td>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headings) + 1 }}" class="text-center text-muted py-4">Tidak ada data untuk filter yang dipilih.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if (!empty($summary))
        <div class="card-body">
            <table class="table table-sm w-auto mb-0">
                @foreach ($summary as $l => $v)<tr><th class="fw-semibold">{{ $l }}</th><td>{{ $v }}</td></tr>@endforeach
            </table>
        </div>
    @endif
    <div class="card-footer bg-white small text-muted">Jumlah baris: {{ count($rows) }}</div>
</div>
@endsection
