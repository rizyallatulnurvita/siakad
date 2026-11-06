<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Absensi — {{ $kelas->mataKuliah->nama }} ({{ $kelas->nama }})
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('partials.alerts')

        <form method="POST" action="{{ route('dosen.absensi.store', $kelas) }}"
              class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-wrap items-end gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                       class="mt-1 rounded border border-gray-300 px-3 py-2">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Materi (opsional)</label>
                <input type="text" name="materi" value="{{ old('materi') }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            <button class="rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">Buat Pertemuan Baru</button>
        </form>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">Pertemuan</th><th class="pr-4">Tanggal</th><th class="pr-4">Materi</th>
                    <th class="pr-4">Hadir</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                @forelse ($pertemuan as $p)
                    <tr class="border-b">
                        <td class="py-2 pr-4">Ke-{{ $p->pertemuan_ke }}</td>
                        <td class="pr-4">{{ $p->tanggal->format('d M Y') }}</td>
                        <td class="pr-4">{{ $p->materi ?? '-' }}</td>
                        <td class="pr-4">{{ $p->hadir_count }} / {{ $p->total_count }}</td>
                        <td><a class="text-indigo-600 hover:underline" href="{{ route('dosen.absensi.show', $p) }}">Isi / ubah kehadiran</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-gray-500">Belum ada pertemuan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('dosen.kelas.index') }}" class="inline-block text-sm text-indigo-600 hover:underline">&larr; Kembali ke daftar kelas</a>
    </div>
</x-app-layout>
