<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tagihan Mahasiswa</h2>
            <a href="{{ route('admin.tagihan.create') }}"
               class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Buat Tagihan</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="Total Tagihan" :value="'Rp ' . number_format($totalTagihan, 0, ',', '.')" sub="Seluruh tagihan yang dibuat" color="indigo" />
            <x-stat-card title="Pembayaran Diterima" :value="'Rp ' . number_format($totalMasuk, 0, ',', '.')" sub="Sudah diverifikasi" color="green" />
            <a href="{{ route('admin.pembayaran.index') }}">
                <x-stat-card title="Menunggu Verifikasi" :value="$menunggu" sub="Klik untuk memverifikasi" color="amber" />
            </a>
        </div>

        <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500">Cari NIM / nama</label>
                <input type="text" name="q" value="{{ $cari }}" class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Status</label>
                <select name="status" class="mt-1 rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach (['belum_bayar' => 'Belum bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas'] as $v => $l)
                        <option value="{{ $v }}" @selected($status === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded bg-gray-800 px-4 py-2 text-sm text-white">Filter</button>
        </form>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Mahasiswa</th><th class="pr-4">Tagihan</th><th class="pr-4">Jumlah</th>
                    <th class="pr-4">Terbayar</th><th class="pr-4">Jatuh Tempo</th><th class="pr-4">Status</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @forelse ($tagihan as $t)
                    <tr class="border-b align-top">
                        <td class="py-2 pr-4">{{ $t->mahasiswa->nama }}
                            <div class="text-xs text-gray-400">{{ $t->mahasiswa->nim }} · {{ $t->mahasiswa->prodi->nama ?? '-' }}</div></td>
                        <td class="pr-4">{{ $t->jenis }}
                            <div class="text-xs text-gray-400">{{ $t->tahun_ajaran }} {{ ucfirst($t->semester) }}</div></td>
                        <td class="pr-4">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                        <td class="pr-4">Rp {{ number_format((int) $t->terbayar, 0, ',', '.') }}</td>
                        <td class="pr-4">{{ $t->jatuh_tempo->format('d M Y') }}</td>
                        <td class="pr-4"><x-badge :status="$t->status" /></td>
                        <td>
                            <form method="POST" action="{{ route('admin.tagihan.destroy', $t) }}" onsubmit="return confirm('Hapus tagihan ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-gray-500">Belum ada tagihan.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $tagihan->links() }}</div>
        </div>
    </div>
</x-app-layout>
