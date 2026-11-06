@php
    $map = [
        'aktif' => 'success', 'disetujui' => 'success', 'lulus' => 'info', 'diajukan' => 'primary', 'terjadwal' => 'info',
        'draft' => 'secondary', 'cuti' => 'warning', 'ditolak' => 'danger', 'nonaktif' => 'secondary', 'keluar' => 'dark',
        'berakhir' => 'secondary', 'admin' => 'dark', 'dosen' => 'primary', 'mahasiswa' => 'info', 'semua' => 'secondary',
        'ganjil' => 'primary', 'genap' => 'warning',
    ];
    $key = strtolower((string) $v);
@endphp
<span class="badge text-bg-{{ $map[$key] ?? 'secondary' }}">{{ ucfirst((string) $v) }}</span>
