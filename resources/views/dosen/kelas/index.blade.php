<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelas Saya</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
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
                        <td class="pr-4">{{ $k->nilai_terkunci ? 'Terkunci' : 'Terbuka' }}</td>
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
</x-app-layout>
