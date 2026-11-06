@extends('layouts.app')
@section('title', 'Tinjau KRS')
@section('parent')<li class="breadcrumb-item"><a href="{{ route('admin.krs.index') }}">KRS</a></li>@endsection
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ $krs->mahasiswa->nim }} — {{ $krs->mahasiswa->nama }}</span>
                @include('partials.badge', ['v' => $krs->status])
            </div>
            <div class="card-body">
                <div class="row small text-muted">
                    <div class="col-sm-6">Program Studi: <span class="text-body">{{ $krs->mahasiswa->programStudi->nama }}</span></div>
                    <div class="col-sm-6">Semester: <span class="text-body">{{ $krs->tahunAkademik->label }}</span></div>
                    <div class="col-sm-6">Total SKS: <span class="text-body">{{ $krs->total_sks }}</span></div>
                    <div class="col-sm-6">Diajukan: <span class="text-body">{{ $krs->diajukan_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
                    @if ($krs->diproses_at)
                        <div class="col-sm-6">Diproses: <span class="text-body">{{ $krs->diproses_at->format('d/m/Y H:i') }} oleh {{ $krs->pemroses?->name ?? '-' }}</span></div>
                    @endif
                    @if ($krs->catatan)<div class="col-12 mt-1">Catatan: <span class="text-body">{{ $krs->catatan }}</span></div>@endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Dosen</th><th>Jadwal</th><th>Kuota</th></tr></thead>
                    <tbody>
                    @foreach ($krs->details as $d)
                        <tr>
                            <td>{{ $d->kelas->mataKuliah->kode }}</td>
                            <td>{{ $d->kelas->mataKuliah->nama }} ({{ $d->kelas->nama }})</td>
                            <td>{{ $d->kelas->mataKuliah->sks }}</td>
                            <td>{{ $d->kelas->dosen->nama }}</td>
                            <td>@forelse ($d->kelas->jadwal as $j){{ $j->hari }} {{ $j->jam }} · {{ $j->ruang }}<br>@empty - @endforelse</td>
                            <td>{{ $terisi[$d->kelas_kuliah_id] }} / {{ $d->kelas->kapasitas }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if ($krs->status === 'diajukan')
            <div class="card">
                <div class="card-header">Keputusan</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.krs.setujui', $krs->id) }}" class="mb-3">
                        @csrf
                        <label class="form-label">Catatan persetujuan (opsional)</label>
                        <textarea name="catatan" rows="2" class="form-control mb-2" maxlength="500"></textarea>
                        <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Setujui KRS</button>
                    </form>
                    <hr>
                    <form method="POST" action="{{ route('admin.krs.tolak', $krs->id) }}">
                        @csrf
                        <label class="form-label">Catatan penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan" rows="2" class="form-control mb-2" maxlength="500" required></textarea>
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-x-lg me-1"></i>Tolak KRS</button>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-secondary mb-0">KRS berstatus <strong>{{ $krs->status }}</strong>; tidak ada tindakan yang tersedia.</div>
        @endif
    </div>
</div>
@endsection
