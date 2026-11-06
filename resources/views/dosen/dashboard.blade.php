<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Dosen</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <p class="text-gray-700">Selamat datang, <strong>{{ $dosen->nama }}</strong> (NIDN {{ $dosen->nidn }})</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="Kelas Diampu" :value="$totalKelas" sub="Semester berjalan" color="indigo" />
            <x-stat-card title="Total Mahasiswa" :value="$totalPeserta" sub="Dari semua kelas Anda" color="sky" />
            <x-stat-card title="Nilai Belum Dikunci" :value="$belumKunci" sub="Kunci setelah nilai lengkap" color="amber" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Distribusi Nilai Mahasiswa Anda</h3>
                <x-chart id="chartNilai" type="bar" :labels="['A','B','C','D','E']" :data="$distribusi"
                         label="Jumlah nilai" :colors="['#22c55e','#84cc16','#f59e0b','#f97316','#ef4444']" />
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Rata-rata Nilai per Kelas</h3>
                <x-chart id="chartRata" type="bar" :labels="$rataLabels" :data="$rataData" label="Rata-rata nilai" :max="100" />
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Kelas yang Diampu</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left border-b text-gray-500">
                        <th class="py-2 pr-4">Mata Kuliah</th><th class="pr-4">Kelas</th><th class="pr-4">Jadwal</th>
                        <th class="pr-4">Peserta</th><th class="pr-4">Nilai</th><th>Aksi</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($kelas as $k)
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $k->mataKuliah->nama }}</td>
                            <td class="pr-4">{{ $k->nama }}</td>
                            <td class="pr-4">{{ $k->hari }} {{ substr($k->jam_mulai, 0, 5) }}–{{ substr($k->jam_selesai, 0, 5) }}</td>
                            <td class="pr-4">{{ $k->krs_count }}</td>
                            <td class="pr-4">
                                @if ($k->nilai_terkunci)
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Terkunci</span>
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Terbuka</span>
                                @endif
                            </td>
                            <td class="space-x-3">
                                <a class="text-indigo-600 hover:underline" href="{{ route('dosen.absensi.index', $k) }}">Absensi</a>
                                <a class="text-indigo-600 hover:underline" href="{{ route('dosen.nilai.edit', $k) }}">Nilai</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-gray-500">Belum ada kelas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
