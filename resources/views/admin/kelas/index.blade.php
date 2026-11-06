<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelas &amp; Kunci Nilai</h2>
            <a href="{{ route('admin.kelas.create') }}"
               class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah Kelas</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Mata Kuliah</th><th class="pr-4">Kelas</th><th class="pr-4">Tahun Ajaran</th>
                    <th class="pr-4">Dosen</th><th class="pr-4">Jadwal</th><th class="pr-4">Peserta</th>
                    <th class="pr-4">Nilai</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @forelse ($kelas as $k)
                    <tr class="border-b align-top">
                        <td class="py-2 pr-4">{{ $k->mataKuliah->nama }}</td>
                        <td class="pr-4">{{ $k->nama }}</td>
                        <td class="pr-4">{{ $k->tahun_ajaran }} {{ ucfirst($k->semester) }}</td>
                        <td class="pr-4">{{ $k->dosen->nama }}</td>
                        <td class="pr-4">
                            @if ($k->hari)
                                {{ $k->hari }} {{ substr($k->jam_mulai, 0, 5) }}–{{ substr($k->jam_selesai, 0, 5) }}
                                <div class="text-xs text-gray-400">{{ $k->ruang }}</div>
                            @else
                                <span class="text-gray-400">Belum dijadwalkan</span>
                            @endif
                        </td>
                        <td class="pr-4">{{ $k->krs_count }} / {{ $k->kuota }}</td>
                        <td class="pr-4">
                            @if ($k->nilai_terkunci)
                                <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Terkunci</span>
                                <div class="text-xs text-gray-400">{{ $k->dikunci_pada?->format('d M Y H:i') }}</div>
                            @else
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Terbuka</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <a href="{{ route('admin.kelas.edit', $k) }}" class="text-indigo-600 hover:underline">Edit</a>

                                <form method="POST" action="{{ route('admin.kelas.kunci', $k) }}"
                                      onsubmit="return confirm('{{ $k->nilai_terkunci ? 'Buka kunci nilai kelas ini?' : 'Kunci nilai kelas ini?' }}')">
                                    @csrf @method('PATCH')
                                    <button class="text-indigo-600 hover:underline">{{ $k->nilai_terkunci ? 'Buka kunci' : 'Kunci' }}</button>
                                </form>

                                <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}"
                                      onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-4 text-gray-500">Belum ada kelas. Klik "Tambah Kelas".</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
