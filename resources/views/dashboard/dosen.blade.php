@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mb-3">
    <h1 class="h4 mb-0">Selamat datang, {{ $dosen->nama }}</h1>
    <div class="text-muted small">Semester aktif: {{ $ta?->label ?? 'belum ditetapkan' }}</div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card title="Kelas yang Diampu" :value="$jumlahKelas" icon="bi-easel" color="primary" /></div>
    <div class="col-md-4"><x-stat-card title="Mahasiswa yang Diajar" :value="$jumlahMahasiswa" icon="bi-people" color="success" /></div>
    <div class="col-md-4"><x-stat-card title="Nilai Belum Lengkap" :value="$belumLengkap" icon="bi-exclamation-circle" color="warning" note="Mahasiswa dengan nilai akhir kosong" /></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Distribusi Nilai Huruf</div><div class="card-body">
        <x-chart id="chDist" type="doughnut" label="Mahasiswa" :labels="$distLabels" :data="$distData" />
    </div></div></div>
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Mahasiswa per Kelas</div><div class="card-body">
        <x-chart id="chKelas" type="bar" label="Mahasiswa" :labels="$kelasLabels" :data="$kelasData" />
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8"><div class="card h-100">
        <div class="card-header">Jadwal Mengajar</div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Hari</th><th>Jam</th><th>Ruang</th><th>Mata Kuliah</th><th>Kelas</th><th></th></tr></thead>
            <tbody>
            @forelse ($jadwal as $j)
                <tr>
                    <td>{{ $j->hari }}</td><td>{{ $j->jam }}</td><td>{{ $j->ruang }}</td>
                    <td>{{ $j->kelas->mataKuliah->nama }}</td><td>{{ $j->kelas->nama }}</td>
                    <td class="text-end"><a href="{{ route('dosen.kelas.show', $j->kelas_kuliah_id) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jadwal mengajar pada semester aktif.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>
    <div class="col-lg-4">@include('partials.pengumuman')</div>
</div>
@endsection
