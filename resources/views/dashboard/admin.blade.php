@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
    <div>
        <h1 class="h4 mb-0">Dashboard Administrator</h1>
        <div class="text-muted small">Semester aktif: {{ $ta?->label ?? 'belum ditetapkan' }}</div>
    </div>
    @if ($krsMenunggu > 0)
        <a href="{{ route('admin.krs.index', ['status' => 'diajukan']) }}" class="btn btn-warning btn-sm"><i class="bi bi-hourglass-split me-1"></i>{{ $krsMenunggu }} KRS menunggu persetujuan</a>
    @endif
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card title="Mahasiswa Aktif" :value="number_format($totalMahasiswa, 0, ',', '.')" icon="bi-mortarboard" color="primary" /></div>
    <div class="col-md-4"><x-stat-card title="Dosen Aktif" :value="number_format($totalDosen, 0, ',', '.')" icon="bi-person-workspace" color="success" /></div>
    <div class="col-md-4"><x-stat-card title="Mata Kuliah" :value="number_format($totalMk, 0, ',', '.')" icon="bi-journal-bookmark" color="warning" /></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Mahasiswa Aktif per Program Studi</div><div class="card-body">
        <x-chart id="chProdi" type="bar" label="Mahasiswa" :labels="$prodiLabels" :data="$prodiData" />
    </div></div></div>
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Mahasiswa Aktif per Angkatan</div><div class="card-body">
        <x-chart id="chAngkatan" type="line" label="Mahasiswa" :labels="$angkatanLabels" :data="$angkatanData" />
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">Pengajuan KRS Terbaru</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>SKS</th><th>Diajukan</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($krsTerbaru as $k)
                        <tr>
                            <td>{{ $k->mahasiswa->nim }}</td><td>{{ $k->mahasiswa->nama }}</td>
                            <td>{{ $k->mahasiswa->programStudi->nama }}</td><td>{{ $k->total_sks }}</td>
                            <td>{{ $k->diajukan_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.krs.show', $k->id) }}">Tinjau</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada KRS yang menunggu persetujuan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">@include('partials.pengumuman')</div>
</div>
@endsection
