@extends('layouts.app')
@section('title', 'KHS')
@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>Kartu Hasil Studi (KHS)</span>
        <span class="no-print d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="ta" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($semester as $s)<option value="{{ $s['id'] }}" @selected($s['id'] === $pilih)>{{ $s['label'] }}</option>@endforeach
                </select>
            </form>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak</button>
        </span>
    </div>
    @if ($semester->isEmpty())
        <div class="card-body text-center text-muted py-5"><i class="bi bi-file-earmark-text fs-3 d-block mb-2"></i>Belum ada nilai yang dapat ditampilkan.</div>
    @else
        <div class="card-body border-bottom">
            <div class="row small">
                <div class="col-md-4"><span class="text-muted">Nama</span><div class="fw-semibold">{{ $mhs->nama }}</div></div>
                <div class="col-md-4"><span class="text-muted">NIM</span><div class="fw-semibold">{{ $mhs->nim }}</div></div>
                <div class="col-md-4"><span class="text-muted">Program Studi</span><div class="fw-semibold">{{ $mhs->programStudi->nama }}</div></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai Akhir</th><th>Huruf</th><th>Bobot</th><th>Mutu</th></tr></thead>
                <tbody>
                @foreach ($rows as $r)
                    <tr><td>{{ $r->kode }}</td><td>{{ $r->mk_nama }}</td><td>{{ $r->sks }}</td><td>{{ $r->nilai_akhir }}</td><td>{{ $r->huruf }}</td><td>{{ number_format($r->bobot, 2, ',', '.') }}</td><td>{{ number_format($r->sks * $r->bobot, 2, ',', '.') }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3"><div class="text-muted small">SKS Semester</div><div class="fs-5 fw-semibold">{{ $ips['sks'] }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">IP Semester</div><div class="fs-5 fw-semibold">{{ number_format($ips['ip'], 2, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Total SKS (Lulus)</div><div class="fs-5 fw-semibold">{{ $ipk['sks_lulus'] }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">IPK</div><div class="fs-5 fw-semibold">{{ number_format($ipk['ip'], 2, ',', '.') }}</div></div>
            </div>
        </div>
    @endif
</div>
@endsection
