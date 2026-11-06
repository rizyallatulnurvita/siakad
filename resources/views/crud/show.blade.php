@extends('layouts.app')
@section('title', 'Detail ' . $c['title'])
@section('parent')<li class="breadcrumb-item"><a href="{{ route($c['route'] . '.index') }}">{{ $c['title'] }}</a></li>@endsection
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Detail {{ $c['title'] }}</span>
        <span class="d-flex gap-2">
            <a href="{{ route($c['route'] . '.edit', $model->getKey()) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route($c['route'] . '.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </span>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            @foreach ($c['fields'] as $f)
                @continue(($f['type'] ?? '') === 'password')
                @php
                    $v = data_get($model, $f['name']);
                    if (($f['type'] ?? '') === 'select') { $v = $f['options'][$v] ?? $v; }
                    if ($v instanceof \DateTimeInterface) { $v = $v->format('d/m/Y'); }
                    if (($f['type'] ?? '') === 'checkbox') { $v = $v ? 'Ya' : 'Tidak'; }
                @endphp
                <dt class="col-sm-4 col-lg-3 text-muted fw-normal">{{ $f['label'] }}</dt>
                <dd class="col-sm-8 col-lg-9" style="white-space:pre-line">{{ $v !== null && $v !== '' ? $v : '-' }}</dd>
            @endforeach
            <dt class="col-sm-4 col-lg-3 text-muted fw-normal">Dibuat</dt>
            <dd class="col-sm-8 col-lg-9">{{ $model->created_at?->format('d/m/Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
