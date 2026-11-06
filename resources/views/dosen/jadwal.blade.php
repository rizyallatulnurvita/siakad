@extends('layouts.app')
@section('title', 'Jadwal Mengajar')
@section('content')
<div class="card">
    <div class="card-header">Jadwal Mengajar @if ($ta)<span class="text-muted fw-normal">· {{ $ta->label }}</span>@endif</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Hari</th><th>Jam</th><th>Ruang</th><th>Mata Kuliah</th><th>Kelas</th></tr></thead>
            <tbody>
            @forelse ($jadwal as $j)
                <tr><td>{{ $j->hari }}</td><td>{{ $j->jam }}</td><td>{{ $j->ruang }}</td><td>{{ $j->kelas->mataKuliah->nama }}</td><td>{{ $j->kelas->nama }}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada jadwal mengajar pada semester aktif.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
