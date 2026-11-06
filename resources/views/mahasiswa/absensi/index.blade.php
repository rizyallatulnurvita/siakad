<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Absensi</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Mata Kuliah</th><th class="pr-4">Kelas</th><th class="pr-4">Pertemuan</th>
                    <th class="pr-4">Hadir</th><th class="pr-4">Izin</th><th class="pr-4">Sakit</th><th class="pr-4">Alpa</th><th>Kehadiran</th>
                </tr></thead>
                <tbody>
                @forelse ($rekap as $r)
                    <tr class="border-b">
                        <td class="py-2 pr-4">{{ $r['mk'] }}</td>
                        <td class="pr-4">{{ $r['kelas'] }}</td>
                        <td class="pr-4">{{ $r['total'] }}</td>
                        <td class="pr-4">{{ $r['hadir'] }}</td>
                        <td class="pr-4">{{ $r['izin'] }}</td>
                        <td class="pr-4">{{ $r['sakit'] }}</td>
                        <td class="pr-4">{{ $r['alpa'] }}</td>
                        <td>
                            <span class="rounded px-2 py-0.5 text-xs {{ $r['persen'] >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $r['persen'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-4 text-gray-500">Belum ada data absensi.</td></tr>
                @endforelse
                </tbody>
            </table>
            <p class="mt-3 text-xs text-gray-500">Batas kehadiran minimal 75% (hijau = aman, merah = di bawah batas).</p>
        </div>
    </div>
</x-app-layout>
