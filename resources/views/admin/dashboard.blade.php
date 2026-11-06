<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- 3 kartu informasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="Total Mahasiswa" :value="$totalMahasiswa" sub="Terdaftar di semua program studi" color="indigo" />
            <x-stat-card title="Total Dosen" :value="$totalDosen" :sub="$totalMk . ' mata kuliah tersedia'" color="sky" />
            <x-stat-card title="Kelas Terkunci" :value="$kelasTerkunci . ' / ' . $totalKelas" sub="Nilai yang sudah final" color="green" />
        </div>

        {{-- 2 grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Distribusi Nilai Seluruh Kelas</h3>
                <x-chart id="chartNilai" type="bar" :labels="['A','B','C','D','E']" :data="$distribusi"
                         label="Jumlah nilai" :colors="['#22c55e','#84cc16','#f59e0b','#f97316','#ef4444']" />
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Mahasiswa per Program Studi</h3>
                <x-chart id="chartProdi" type="doughnut" :labels="$prodiLabels" :data="$prodiData" label="Mahasiswa" />
            </div>
        </div>

        {{-- 1 tabel informasi --}}
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">Ringkasan Kelas</h3>
                <a href="{{ route('admin.kelas.index') }}" class="text-sm text-indigo-600 hover:underline">Kelola kunci nilai</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left border-b text-gray-500">
                        <th class="py-2 pr-4">Mata Kuliah</th><th class="pr-4">Kelas</th><th class="pr-4">Dosen</th>
                        <th class="pr-4">Peserta</th><th class="pr-4">Rata-rata</th><th>Status Nilai</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($kelas as $k)
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $k->mataKuliah->nama }}</td>
                            <td class="pr-4">{{ $k->nama }}</td>
                            <td class="pr-4">{{ $k->dosen->nama }}</td>
                            <td class="pr-4">{{ $k->krs_count }}</td>
                            <td class="pr-4">{{ $k->krs_avg_nilai_akhir ? number_format($k->krs_avg_nilai_akhir, 1) : '-' }}</td>
                            <td>
                                @if ($k->nilai_terkunci)
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Terkunci</span>
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Terbuka</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
