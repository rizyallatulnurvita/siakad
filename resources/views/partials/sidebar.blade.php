@php
    $role = auth()->user()->role;
    $menus = match ($role) {
        'admin' => [
            ['Utama', [
                ['Dashboard', 'bi-speedometer2', 'dashboard', 'dashboard'],
            ]],
            ['Data Master', [
                ['Pengguna', 'bi-people', 'admin.users.index', 'admin.users.*'],
                ['Mahasiswa', 'bi-mortarboard', 'admin.mahasiswa.index', 'admin.mahasiswa.*'],
                ['Dosen', 'bi-person-workspace', 'admin.dosen.index', 'admin.dosen.*'],
                ['Program Studi', 'bi-building', 'admin.program-studi.index', 'admin.program-studi.*'],
                ['Tahun Akademik', 'bi-calendar3', 'admin.tahun-akademik.index', 'admin.tahun-akademik.*'],
                ['Mata Kuliah', 'bi-journal-bookmark', 'admin.mata-kuliah.index', 'admin.mata-kuliah.*'],
            ]],
            ['Perkuliahan', [
                ['Kelas Kuliah', 'bi-easel', 'admin.kelas-kuliah.index', 'admin.kelas-kuliah.*'],
                ['Jadwal Kuliah', 'bi-calendar-week', 'admin.jadwal-kuliah.index', 'admin.jadwal-kuliah.*'],
                ['KRS', 'bi-card-checklist', 'admin.krs.index', 'admin.krs.*'],
                ['Rekap Nilai', 'bi-clipboard-data', 'admin.nilai.index', 'admin.nilai.*'],
            ]],
            ['Lainnya', [
                ['Pengumuman', 'bi-megaphone', 'admin.pengumuman.index', 'admin.pengumuman.*'],
                ['Laporan', 'bi-printer', 'admin.laporan.index', 'admin.laporan.*'],
                ['Pengaturan Akademik', 'bi-sliders', 'admin.pengaturan.edit', 'admin.pengaturan.*'],
            ]],
        ],
        'dosen' => [
            ['Utama', [
                ['Dashboard', 'bi-speedometer2', 'dashboard', 'dashboard'],
                ['Kelas Saya', 'bi-easel', 'dosen.kelas.index', 'dosen.kelas.*'],
                ['Jadwal Mengajar', 'bi-calendar-week', 'dosen.jadwal', 'dosen.jadwal'],
                ['Riwayat Pengajaran', 'bi-clock-history', 'dosen.riwayat', 'dosen.riwayat'],
                ['Pengumuman', 'bi-megaphone', 'pengumuman.index', 'pengumuman.*'],
            ]],
        ],
        default => [
            ['Utama', [
                ['Dashboard', 'bi-speedometer2', 'dashboard', 'dashboard'],
                ['Mata Kuliah', 'bi-journal-bookmark', 'mahasiswa.mata-kuliah', 'mahasiswa.mata-kuliah'],
                ['Jadwal Kuliah', 'bi-calendar-week', 'mahasiswa.jadwal', 'mahasiswa.jadwal'],
                ['KRS', 'bi-card-checklist', 'mahasiswa.krs', 'mahasiswa.krs*'],
                ['KHS', 'bi-file-earmark-text', 'mahasiswa.khs', 'mahasiswa.khs'],
                ['Nilai & Transkrip', 'bi-clipboard-data', 'mahasiswa.nilai', 'mahasiswa.nilai'],
                ['Pengumuman', 'bi-megaphone', 'pengumuman.index', 'pengumuman.*'],
            ]],
        ],
    };
@endphp
<div class="brand">SIAKAD<small>Sistem Informasi Akademik</small></div>
<nav class="pb-4">
    @foreach ($menus as [$group, $items])
        @if (count($menus) > 1)<div class="group">{{ $group }}</div>@endif
        @foreach ($items as [$label, $icon, $route, $pattern])
            <a class="item {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    @endforeach
</nav>
