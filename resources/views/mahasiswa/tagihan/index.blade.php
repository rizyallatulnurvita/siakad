<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Tagihan &amp; Pembayaran</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="Total Tagihan" :value="'Rp ' . number_format($totalTagihan, 0, ',', '.')" color="indigo" />
            <x-stat-card title="Sudah Dibayar" :value="'Rp ' . number_format($totalBayar, 0, ',', '.')" sub="Pembayaran yang sudah diverifikasi" color="green" />
            <x-stat-card title="Sisa Tagihan" :value="'Rp ' . number_format(max(0, $totalTagihan - $totalBayar), 0, ',', '.')" color="rose" />
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Tagihan</th><th class="pr-4">Jumlah</th><th class="pr-4">Terbayar</th>
                    <th class="pr-4">Jatuh Tempo</th><th class="pr-4">Status</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @forelse ($tagihan as $t)
                    @php
                        $terbayar = (int) $t->terbayar;
                        $menunggu = (int) $t->menunggu;
                        $telat = $t->status !== 'lunas' && $t->jatuh_tempo->isPast();
                    @endphp
                    <tr class="border-b align-top">
                        <td class="py-2 pr-4">{{ $t->jenis }}
                            <div class="text-xs text-gray-400">{{ $t->tahun_ajaran }} {{ ucfirst($t->semester) }}</div></td>
                        <td class="pr-4">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                        <td class="pr-4">Rp {{ number_format($terbayar, 0, ',', '.') }}
                            @if ($menunggu > 0)
                                <div class="text-xs text-sky-600">+ Rp {{ number_format($menunggu, 0, ',', '.') }} menunggu verifikasi</div>
                            @endif
                        </td>
                        <td class="pr-4 {{ $telat ? 'text-red-600' : '' }}">{{ $t->jatuh_tempo->format('d M Y') }}
                            @if ($telat) <div class="text-xs">Lewat jatuh tempo</div> @endif</td>
                        <td class="pr-4"><x-badge :status="$t->status" /></td>
                        <td class="space-x-3">
                            <a href="{{ route('mahasiswa.tagihan.show', $t) }}" class="text-indigo-600 hover:underline">Detail</a>
                            @if ($t->status !== 'lunas' && $t->jumlah - $terbayar - $menunggu > 0)
                                <a href="{{ route('mahasiswa.tagihan.bayar', $t) }}" class="text-green-700 hover:underline">Bayar</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-gray-500">Belum ada tagihan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
