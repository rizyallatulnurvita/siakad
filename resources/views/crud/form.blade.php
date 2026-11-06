@extends('layouts.app')
@section('title', ($model ? 'Edit ' : 'Tambah ') . $c['title'])
@section('parent')<li class="breadcrumb-item"><a href="{{ route($c['route'] . '.index') }}">{{ $c['title'] }}</a></li>@endsection
@section('content')
<div class="card">
    <div class="card-header">{{ $model ? 'Edit' : 'Tambah' }} {{ $c['title'] }}</div>
    <form method="POST" action="{{ $model ? route($c['route'] . '.update', $model->getKey()) : route($c['route'] . '.store') }}" novalidate>
        @csrf
        @if ($model) @method('PUT') @endif
        <div class="card-body">
            <div class="row g-3">
                @foreach ($c['fields'] as $f)
                    @php
                        $name = $f['name'];
                        $type = $f['type'] ?? 'text';
                        $val = $type === 'password' ? null : old($name, $model ? data_get($model, $name) : ($f['default'] ?? null));
                        if ($val instanceof \DateTimeInterface) { $val = $val->format('Y-m-d'); }
                        if ($type === 'time' && is_string($val) && strlen($val) === 8) { $val = substr($val, 0, 5); }
                        $required = ($f['required'] ?? false) === true || (($f['required'] ?? null) === 'create' && !$model);
                        $cls = 'form-control' . ($errors->has($name) ? ' is-invalid' : '');
                    @endphp
                    <div class="col-12 {{ ($f['col'] ?? 6) == 12 ? '' : 'col-md-6' }}">
                        @if ($type === 'checkbox')
                            <input type="hidden" name="{{ $name }}" value="0">
                            <div class="form-check">
                                <input class="form-check-input @error($name) is-invalid @enderror" type="checkbox" id="f_{{ $name }}" name="{{ $name }}" value="1" @checked((bool) $val)>
                                <label class="form-check-label" for="f_{{ $name }}">{{ $f['label'] }}</label>
                            </div>
                        @else
                            <label class="form-label" for="f_{{ $name }}">{{ $f['label'] }} @if ($required)<span class="text-danger">*</span>@endif</label>
                            @if ($type === 'select')
                                <select id="f_{{ $name }}" name="{{ $name }}" class="form-select {{ $errors->has($name) ? 'is-invalid' : '' }}">
                                    <option value="">— Pilih —</option>
                                    @foreach ($f['options'] as $ov => $ol)
                                        <option value="{{ $ov }}" @selected((string) $val === (string) $ov)>{{ $ol }}</option>
                                    @endforeach
                                </select>
                            @elseif ($type === 'textarea')
                                <textarea id="f_{{ $name }}" name="{{ $name }}" rows="{{ $f['rows'] ?? 3 }}" class="{{ $cls }}">{{ $val }}</textarea>
                            @else
                                <input id="f_{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}" class="{{ $cls }}" {!! $f['attrs'] ?? '' !!} @if ($type === 'password') autocomplete="new-password" @endif>
                            @endif
                            @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if (!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan</button>
            <a href="{{ route($c['route'] . '.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
