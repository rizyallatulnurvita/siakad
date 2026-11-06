<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Verifikasi Pembayaran</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')

        <div class="flex flex-wrap gap-2 text-sm">
            @foreach (['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $v => $l)
                <a href="{{ route('admin.pembayaran.index', ['status' => $v]) }}"
                   class="rounded px-3 py-1 {{ $status === $v ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }}">{{ $l }}</a>
            @endforeach
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Mahasiswa</th><th class="pr-4">Tagihan</th><th class="pr-4">Dibayar</th>
                    <th class="pr-4">Tanggal</th><th class="pr-4">Bukti</th><th class="pr-4">Status</th><th>Verifikasi</th>
                </tr></thead>
                <tbody>
                @forelse ($pembayaran as $p)
                    <tr class="border-b align-top">
                        <td class="py-2 pr-4">{{ $p->tagihan->mahasiswa->nama }}
                            <div class="text-xs text-gray-400">{{ $p->tagihan->mahasiswa->nim }}</div></td>
                        <td class="pr-4">{{ $p->tagihan->jenis }}
                            <div class="text-xs text-gray-400">{{ $p->tagihan->tahun_ajaran }} {{ ucfirst($p->tagihan->semester) }}</div></td>
                        <td class="pr-4">Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                            <div class="text-xs text-gray-400">{{ $p->metode }}</div></td>
                        <td class="pr-4">{{ $p->tanggal_bayar->format('d M Y') }}</td>
                        <td class="pr-4">
                            @if ($p->bukti_path)
                                <a href="{{ route('bukti.show', $p) }}" target="_blank" class="text-indigo-600 hover:underline">Lihat</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="pr-4"><x-badge :status="$p->status" />
                            @if ($p->catatan_admin) <div class="text-xs text-gray-400">{{ $p->catatan_admin }}</div> @endif
                        </td>
                        <td>
                            @if ($p->status === 'menunggu')
                                <form method="POST" action="{{ route('admin.pembayaran.verifikasi', $p) }}" class="space-y-1">
                                    @csrf @method('PATCH')
                                    <input type="text" name="catatan_admin" placeholder="Alasan (wajib jika ditolak)"
                                           class="w-52 rounded border border-gray-300 px-2 py-1 text-xs">
                                    <div class="space-x-2">
                                        <button name="aksi" value="terima" class="rounded bg-green-600 px-3 py-1 text-xs text-white">Terima</button>
                                        <button name="aksi" value="tolak" class="rounded bg-red-600 px-3 py-1 text-xs text-white">Tolak</button>
                                    </div>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">{{ $p->diverifikasi_pada?->format('d M Y H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-gray-500">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $pembayaran->links() }}</div>
        </div>
    </div>
</x-app-layout>
