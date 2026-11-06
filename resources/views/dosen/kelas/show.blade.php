@extends('layouts.app')
@section('title', $kelas->mataKuliah->nama . ' (' . $kelas->nama . ')')
@section('parent')<li class="breadcrumb-item"><a href="{{ route('dosen.kelas.index') }}">Kelas Saya</a></li>@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card title="Peserta" :value="$peserta->count() . ' / ' . $kelas->kapasitas" icon="bi-people" color="primary" /></div>
    <div class="col-md-4"><x-stat-card title="Rata-rata Nilai Akhir" :value="$rata !== null ? number_format($rata, 2, ',', '.') : '-'" icon="bi-bar-chart" color="success" /></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-body">
        <div class="text-muted small mb-1">Distribusi nilai</div>
        @forelse ($distribusi as $h => $n)<span class="badge text-bg-secondary me-1">{{ $h }}: {{ $n }}</span>@empty<span class="text-muted small">Belum ada nilai.</span>@endforelse
    </div></div></div>
</div>

<div class="card">
    <div class="card-header">Daftar Mahasiswa & Input Nilai · {{ $kelas->tahunAkademik->label }}</div>
    <form method="POST" action="{{ route('dosen.kelas.nilai', $kelas->id) }}">
        @csrf
        <div class="card-body border-bottom small text-muted">
            Nilai akhir = Tugas × {{ rtrim(rtrim((string) $bobot['tugas'], '0'), '.') }}% + UTS × {{ rtrim(rtrim((string) $bobot['uts'], '0'), '.') }}% + UAS × {{ rtrim(rtrim((string) $bobot['uas'], '0'), '.') }}%.
            Nilai akhir dihitung bila ketiga komponen terisi. Rentang nilai 0–100.
            @unless ($bisaInput)<div class="text-danger mt-1"><i class="bi bi-lock me-1"></i>Periode input nilai sedang ditutup.</div>@endunless
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>NIM</th><th>Nama</th><th>KRS</th><th style="width:110px">Tugas</th><th style="width:110px">UTS</th><th style="width:110px">UAS</th><th>Akhir</th><th>Huruf</th></tr></thead>
                <tbody>
                @php $adaInput = false; @endphp
                @forelse ($peserta as $d)
                    @php
                        $n = $d->nilai; $m = $d->krs->mahasiswa;
                        $edit = $bisaInput && $d->krs->status === 'disetujui' && $n;
                        $adaInput = $adaInput || $edit;
                    @endphp
                    <tr>
                        <td>{{ $m->nim }}</td><td>{{ $m->nama }}</td>
                        <td>@include('partials.badge', ['v' => $d->krs->status])</td>
                        @foreach (['tugas', 'uts', 'uas'] as $c)
                            <td>
                                @if ($edit)
                                    <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm"
                                        name="nilai[{{ $d->id }}][{{ $c }}]" value="{{ old("nilai.{$d->id}.{$c}", $n->{$c}) }}">
                                @else
                                    {{ $n?->{$c} ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                        <td>{{ $n?->nilai_akhir ?? '-' }}</td>
                        <td>{{ $n?->huruf ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">Belum ada mahasiswa yang mengambil kelas ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($adaInput)
            <div class="card-footer bg-white"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Nilai</button></div>
        @endif
    </form>
</div>
@endsection
