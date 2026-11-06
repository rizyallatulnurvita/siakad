<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Tagihan — {{ $tagihan->jenis }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        @php $terbayar = $tagihan->terbayar(); $menunggu = $tagihan->menunggu(); @endphp
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-1">
            <div><span class="text-gray-500">Periode:</span> {{ $tagihan->tahun_ajaran }} {{ ucfirst($tagihan->semester) }}</div>
            @if ($tagihan->keterangan) <div><span class="text-gray-500">Keterangan:</span> {{ $tagihan->keterangan }}</div> @endif
            <div><span class="text-gray-500">Jumlah:</span> Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</div>
            <div><span class="text-gray-500">Terbayar:</span> Rp {{ number_format($terbayar, 0, ',', '.') }}</div>
            <div><span class="text-gray-500">Sisa:</span> Rp {{ number_format(max(0, $tagihan->jumlah - $terbayar), 0, ',', '.') }}</div>
            <div><span class="text-gray-500">Jatuh tempo:</span> {{ $tagihan->jatuh_tempo->format('d M Y') }}</div>
            <div class="pt-1"><x-badge :status="$tagihan->status" /></div>

            @if ($tagihan->status !== 'lunas' && $tagihan->jumlah - $terbayar - $menunggu > 0)
                <a href="{{ route('mahasiswa.tagihan.bayar', $tagihan) }}"
                   class="mt-3 inline-block rounded bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700">Bayar Sekarang</a>
            @endif
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <h3 class="font-semibold text-gray-800 mb-3">Riwayat Pembayaran</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Tanggal</th><th class="pr-4">Jumlah</th><th class="pr-4">Metode</th>
                    <th class="pr-4">Bukti</th><th>Status</th>
                </tr></thead>
                <tbody>
                @forelse ($riwayat as $p)
                    <tr class="border-b align-top">
                        <td class="py-2 pr-4">{{ $p->tanggal_bayar->format('d M Y') }}</td>
                        <td class="pr-4">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                        <td class="pr-4">{{ $p->metode }}</td>
                        <td class="pr-4">
                            @if ($p->bukti_path)
                                <a href="{{ route('bukti.show', $p) }}" target="_blank" class="text-indigo-600 hover:underline">Lihat</a>
                            @else - @endif
                        </td>
                        <td><x-badge :status="$p->status" />
                            @if ($p->catatan_admin) <div class="text-xs text-gray-500">Catatan admin: {{ $p->catatan_admin }}</div> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-gray-500">Belum ada pembayaran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('mahasiswa.tagihan.index') }}" class="inline-block text-sm text-indigo-600 hover:underline">&larr; Kembali ke daftar tagihan</a>
    </div>
</x-app-layout>
