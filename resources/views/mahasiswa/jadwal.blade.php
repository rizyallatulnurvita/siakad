@extends('layouts.app')
@section('title', 'Jadwal Kuliah')
@section('content')
<div class="card">
    <div class="card-header">Jadwal Kuliah @if ($ta)<span class="text-muted fw-normal">· {{ $ta->label }}</span>@endif</div>
    <div class="card-body border-bottom small text-muted">Menampilkan kelas pada KRS yang sudah diajukan atau disetujui.</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Hari</th><th>Jam</th><th>Ruang</th><th>Mata Kuliah</th><th>Kelas</th><th>Dosen</th></tr></thead>
            <tbody>
            @forelse ($jadwal as $j)
                <tr><td>{{ $j->hari }}</td><td>{{ $j->jam }}</td><td>{{ $j->ruang }}</td><td>{{ $j->kelas->mataKuliah->nama }}</td><td>{{ $j->kelas->nama }}</td><td>{{ $j->kelas->dosen->nama }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada jadwal. Ajukan KRS terlebih dahulu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
