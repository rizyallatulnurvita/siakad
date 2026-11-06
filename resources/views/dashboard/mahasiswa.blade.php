@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mb-3">
    <h1 class="h4 mb-0">Halo, {{ $mhs->nama }}</h1>
    <div class="text-muted small">NIM {{ $mhs->nim }} · Semester aktif: {{ $ta?->label ?? 'belum ditetapkan' }}</div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card title="SKS Semester Aktif (KRS)" :value="$sksAktif" icon="bi-journal-check" color="primary"
        :note="$krs ? 'Status KRS: ' . ucfirst($krs->status) : 'KRS belum dibuat'" /></div>
    <div class="col-md-4"><x-stat-card title="IP Semester Aktif" :value="$ipsAktif && $ipsAktif['sks'] > 0 ? number_format($ipsAktif['ip'], 2, ',', '.') : '-'" icon="bi-bar-chart" color="success"
        note="Terhitung setelah nilai diinput dosen" /></div>
    <div class="col-md-4"><x-stat-card title="IPK Kumulatif" :value="$ipk['sks'] > 0 ? number_format($ipk['ip'], 2, ',', '.') : '-'" icon="bi-award" color="warning"
        :note="$ipk['sks_lulus'] . ' SKS lulus'" /></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Perkembangan IP per Semester</div><div class="card-body">
        <x-chart id="chIp" type="line" label="IP" :labels="$ipLabels" :data="$ipData" :max="4" />
    </div></div></div>
    <div class="col-lg-6"><div class="card h-100"><div class="card-header">Nilai Akhir Mata Kuliah (semester terakhir)</div><div class="card-body">
        <x-chart id="chNilai" type="bar" label="Nilai akhir" :labels="$nilaiLabels" :data="$nilaiData" />
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8"><div class="card h-100">
        <div class="card-header d-flex justify-content-between"><span>Mata Kuliah & Jadwal pada KRS Aktif</span>
            <a href="{{ route('mahasiswa.krs') }}" class="small">Kelola KRS</a></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Dosen</th><th>Jadwal</th></tr></thead>
            <tbody>
            @forelse ($krs?->details ?? [] as $d)
                <tr>
                    <td>{{ $d->kelas->mataKuliah->kode }}</td>
                    <td>{{ $d->kelas->mataKuliah->nama }} ({{ $d->kelas->nama }})</td>
                    <td>{{ $d->kelas->mataKuliah->sks }}</td>
                    <td>{{ $d->kelas->dosen->nama }}</td>
                    <td>@forelse ($d->kelas->jadwal as $j){{ $j->hari }} {{ $j->jam }} · {{ $j->ruang }}<br>@empty <span class="text-muted">Belum dijadwalkan</span> @endforelse</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada mata kuliah pada KRS semester aktif.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>
    <div class="col-lg-4">@include('partials.pengumuman')</div>
</div>
@endsection
