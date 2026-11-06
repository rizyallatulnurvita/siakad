<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Mahasiswa</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div>
            <p class="text-gray-700"><strong>{{ $mhs->nama }}</strong> — NIM {{ $mhs->nim }} — {{ $mhs->prodi->nama }} ({{ $mhs->angkatan }})</p>
            <p class="text-sm text-gray-500">Dosen wali: {{ $mhs->dosenWali?->nama ?? '-' }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="SKS Diambil" :value="$totalSks" sub="KRS berstatus disetujui" color="indigo" />
            <x-stat-card title="IPK" :value="number_format($ipk, 2)" sub="Dari nilai yang sudah dikunci dosen" color="green" />
            <x-stat-card title="Kehadiran" :value="$persenHadir . '%'" sub="Hadir dari seluruh pertemuan" color="sky" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Nilai Akhir per Mata Kuliah</h3>
                @if (count($nilaiData))
                    <x-chart id="chartNilai" type="bar" :labels="$nilaiLabels" :data="$nilaiData" label="Nilai akhir" :max="100" />
                @else
                    <p class="text-sm text-gray-500">Belum ada nilai yang dipublikasikan.</p>
                @endif
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Komposisi Kehadiran</h3>
                <x-chart id="chartHadir" type="doughnut" :labels="['Hadir','Izin','Sakit','Alpa']" :data="$hadirData"
                         label="Pertemuan" :colors="['#22c55e','#06b6d4','#f59e0b','#ef4444']" />
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-3">KRS &amp; Nilai</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left border-b text-gray-500">
                        <th class="py-2 pr-4">Mata Kuliah</th><th class="pr-4">SKS</th><th class="pr-4">Dosen</th>
                        <th class="pr-4">Status KRS</th><th class="pr-4">Nilai Akhir</th><th>Huruf</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($krs as $r)
                        @php $tampil = $r->kelas->nilai_terkunci && $r->nilai_huruf; @endphp
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $r->kelas->mataKuliah->nama }}</td>
                            <td class="pr-4">{{ $r->kelas->mataKuliah->sks }}</td>
                            <td class="pr-4">{{ $r->kelas->dosen->nama }}</td>
                            <td class="pr-4">{{ ucfirst($r->status) }}</td>
                            <td class="pr-4">{{ $tampil ? number_format($r->nilai_akhir, 1) : '-' }}</td>
                            <td>{{ $tampil ? $r->nilai_huruf : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-gray-500">Belum ada KRS.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-gray-500">Nilai baru tampil setelah dosen mengunci nilai kelas.</p>
        </div>
    </div>
</x-app-layout>
