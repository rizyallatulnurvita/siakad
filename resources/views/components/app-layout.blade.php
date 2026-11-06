<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIAKAD') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    @php $role = auth()->user()->role; @endphp
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-16 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-5 text-sm">
                <a href="{{ route('dashboard') }}" class="font-bold text-indigo-700 text-base">SIAKAD</a>
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                <a href="{{ route('jadwal') }}" class="text-gray-600 hover:text-gray-900">Jadwal</a>
                @if ($role === 'admin')
                    <a href="{{ route('admin.kelas.index') }}" class="text-gray-600 hover:text-gray-900">Kelas &amp; Kunci Nilai</a>
                    <a href="{{ route('admin.tagihan.index') }}" class="text-gray-600 hover:text-gray-900">Tagihan</a>
                    <a href="{{ route('admin.pembayaran.index') }}" class="text-gray-600 hover:text-gray-900">Verifikasi Bayar</a>
                @elseif ($role === 'dosen')
                    <a href="{{ route('dosen.kelas.index') }}" class="text-gray-600 hover:text-gray-900">Kelas Saya</a>
                @elseif ($role === 'mahasiswa')
                    <a href="{{ route('mahasiswa.absensi.index') }}" class="text-gray-600 hover:text-gray-900">Absensi</a>
                    <a href="{{ route('mahasiswa.tagihan.index') }}" class="text-gray-600 hover:text-gray-900">Pembayaran</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-900">Profil</a>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-600">{{ auth()->user()->name }}
                    <span class="ml-1 rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{{ ucfirst($role) }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-600 hover:underline">Keluar</button>
                </form>
            </div>
        </div>
    </nav>

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
        </header>
    @endisset

    <main>{{ $slot }}</main>
</body>
</html>
