@extends('layouts.app')
@section('title', 'KRS')
@section('content')
@if (!$ta)
    <div class="alert alert-warning">Belum ada semester aktif. KRS belum dapat diisi.</div>
@else
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>KRS {{ $ta->label }}</span>
                @if ($krs) @include('partials.badge', ['v' => $krs->status]) @else <span class="badge text-bg-light border">Belum dibuat</span> @endif
            </div>
            <div class="card-body border-bottom small">
                Total SKS: <strong>{{ $krs?->total_sks ?? 0 }}</strong> dari maksimal <strong>{{ $maks }}</strong> SKS.
                @if (!$ta->periodeKrsTerbuka())<span class="text-danger ms-2"><i class="bi bi-lock"></i> Periode KRS ditutup.</span>@endif
                @if ($krs?->catatan)
                    <div class="mt-2 alert {{ $krs->status === 'ditolak' ? 'alert-danger' : 'alert-info' }} py-2 mb-0">
                        <strong>Catatan admin:</strong> {{ $krs->catatan }}
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Mata Kuliah</th><th>SKS</th><th>Jadwal</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($krs?->details ?? [] as $d)
                        <tr>
                            <td>{{ $d->kelas->mataKuliah->kode }} · {{ $d->kelas->mataKuliah->nama }} ({{ $d->kelas->nama }})<div class="small text-muted">{{ $d->kelas->dosen->nama }}</div></td>
                            <td>{{ $d->kelas->mataKuliah->sks }}</td>
                            <td class="small">@forelse ($d->kelas->jadwal as $j){{ $j->hari }} {{ $j->jam }} · {{ $j->ruang }}<br>@empty - @endforelse</td>
                            <td class="text-end">
                                @if ($editable)
                                    <form method="POST" action="{{ route('mahasiswa.krs.hapus', $d->id) }}" onsubmit="return confirm('Hapus kelas ini dari KRS?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kelas dipilih. Pilih dari daftar kelas di samping.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($editable && $krs && $krs->details->isNotEmpty())
                <div class="card-footer bg-white">
                    <form method="POST" action="{{ route('mahasiswa.krs.ajukan') }}" onsubmit="return confirm('Ajukan KRS sekarang? Setelah diajukan KRS tidak dapat diubah.')">
                        @csrf <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan KRS</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Kelas Tersedia (prodi Anda)</div>
            <div class="card-body border-bottom">
                <form method="GET" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode / nama mata kuliah">
                    <button class="btn btn-outline-primary btn-sm">Cari</button>
                </form>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($kelas as $k)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold">{{ $k->mataKuliah->kode }} · {{ $k->mataKuliah->nama }} ({{ $k->nama }})</div>
                                <div class="small text-muted">{{ $k->mataKuliah->sks }} SKS · {{ $k->dosen->nama }} · Terisi {{ $k->terisi }}/{{ $k->kapasitas }}</div>
                                <div class="small">@forelse ($k->jadwal as $j){{ $j->hari }} {{ $j->jam }} · {{ $j->ruang }}<br>@empty <span class="text-muted">Belum dijadwalkan</span> @endforelse</div>
                            </div>
                            @if ($editable)
                                <form method="POST" action="{{ route('mahasiswa.krs.tambah') }}">
                                    @csrf <input type="hidden" name="kelas_kuliah_id" value="{{ $k->id }}">
                                    <button class="btn btn-sm btn-outline-primary" @disabled($k->terisi >= $k->kapasitas)><i class="bi bi-plus-lg"></i></button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted py-4">Tidak ada kelas yang tersedia.</li>
                @endforelse
            </ul>
            @if ($kelas->hasPages())<div class="card-footer bg-white">{{ $kelas->links('pagination::bootstrap-5') }}</div>@endif
        </div>
    </div>
</div>
@endif
@endsection
