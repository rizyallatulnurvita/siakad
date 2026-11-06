@extends('layouts.app')
@section('title', $c['title'])
@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <span><i class="bi {{ $c['icon'] }} me-2"></i>Data {{ $c['title'] }}</span>
            @unless ($c['readonly'])
                <a href="{{ route($c['route'] . '.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
            @endunless
        </div>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            @if ($c['search'])
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Pencarian</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari {{ strtolower($c['title']) }}...">
                </div>
            @endif
            @foreach ($c['filters'] as $f)
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ $f['label'] }}</label>
                    <select name="{{ $f['name'] }}" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach ($f['options'] as $val => $label)
                            <option value="{{ $val }}" @selected((string) request($f['name']) === (string) $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-12 col-md-auto d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route($c['route'] . '.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    @foreach ($c['columns'] as $col)<th>{{ $col['label'] }}</th>@endforeach
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-muted">{{ $rows->firstItem() + $loop->index }}</td>
                    @foreach ($c['columns'] as $col)
                        @php $v = isset($col['value']) ? $col['value']($row) : data_get($row, $col['key']); @endphp
                        <td>
                            @if (!empty($col['badge']))
                                @include('partials.badge', ['v' => $v])
                            @else
                                {{ $v }}
                            @endif
                        </td>
                    @endforeach
                    <td class="text-end text-nowrap">
                        @foreach ($c['row_actions'] as $a)
                            @if (!isset($a['if']) || $a['if']($row))
                                @php
                                    $label = is_callable($a['label']) ? $a['label']($row) : $a['label'];
                                    $method = strtoupper($a['method'] ?? 'GET');
                                @endphp
                                @if ($method === 'GET')
                                    <a href="{{ $a['url']($row) }}" class="btn btn-sm {{ $a['class'] ?? 'btn-outline-secondary' }}" title="{{ $label }}"><i class="bi {{ $a['icon'] ?? 'bi-arrow-right' }}"></i><span class="d-none d-xl-inline ms-1">{{ $label }}</span></a>
                                @else
                                    <form method="POST" action="{{ $a['url']($row) }}" class="d-inline" @if (!empty($a['confirm'])) onsubmit="return confirm(@js($a['confirm']))" @endif>
                                        @csrf @method($method)
                                        <button class="btn btn-sm {{ $a['class'] ?? 'btn-outline-secondary' }}" title="{{ $label }}"><i class="bi {{ $a['icon'] ?? 'bi-arrow-right' }}"></i><span class="d-none d-xl-inline ms-1">{{ $label }}</span></button>
                                    </form>
                                @endif
                            @endif
                        @endforeach
                        @if (!$c['readonly'])
                            <a href="{{ route($c['route'] . '.show', $row->getKey()) }}" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route($c['route'] . '.edit', $row->getKey()) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route($c['route'] . '.destroy', $row->getKey()) }}" class="d-inline" onsubmit="return confirm('Hapus data ini? Tindakan tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($c['columns']) + 2 }}" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>Tidak ada data{{ request()->hasAny(['q']) ? ' yang cocok dengan pencarian' : '' }}.
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($rows->hasPages() || $rows->total() > 0)
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-muted">Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} data</div>
            {{ $rows->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
