@props(['status'])
@php
    [$label, $kelas] = match ($status) {
        'lunas'       => ['Lunas', 'bg-green-100 text-green-700'],
        'diterima'    => ['Diterima', 'bg-green-100 text-green-700'],
        'sebagian'    => ['Sebagian', 'bg-amber-100 text-amber-700'],
        'menunggu'    => ['Menunggu verifikasi', 'bg-sky-100 text-sky-700'],
        'ditolak'     => ['Ditolak', 'bg-red-100 text-red-700'],
        default       => ['Belum bayar', 'bg-rose-100 text-rose-700'],
    };
@endphp
<span class="rounded px-2 py-0.5 text-xs {{ $kelas }}">{{ $label }}</span>
