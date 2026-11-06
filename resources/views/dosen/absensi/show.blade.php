<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kehadiran Pertemuan {{ $pertemuan->pertemuan_ke }} — {{ $kelas->mataKuliah->nama }} ({{ $kelas->nama }})
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="POST" action="{{ route('dosen.absensi.update', $pertemuan) }}" class="bg-white shadow-sm sm:rounded-lg p-6">
            @csrf @method('PUT')
            <p class="mb-3 text-sm text-gray-600">Tanggal: {{ $pertemuan->tanggal->format('d M Y') }}</p>

            <label class="block text-sm font-medium text-gray-700">Materi</label>
            <input type="text" name="materi" value="{{ old('materi', $pertemuan->materi) }}"
                   class="mt-1 mb-4 w-full rounded border border-gray-300 px-3 py-2">

            <table class="min-w-full text-sm">
                <thead><tr class="text-left border-b text-gray-500">
                    <th class="py-2 pr-4">NIM</th><th class="pr-4">Nama</th><th>Kehadiran</th>
                </tr></thead>
                <tbody>
                @forelse ($peserta as $m)
                    @php $sekarang = $absen->get($m->id)?->status ?? 'hadir'; @endphp
                    <tr class="border-b">
                        <td class="py-2 pr-4">{{ $m->nim }}</td>
                        <td class="pr-4">{{ $m->nama }}</td>
                        <td class="space-x-3">
                            @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $nilai => $label)
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="status[{{ $m->id }}]" value="{{ $nilai }}" {{ $sekarang === $nilai ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-gray-500">Belum ada mahasiswa di kelas ini.</td></tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-4 flex items-center gap-4">
                <button class="rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">Simpan Kehadiran</button>
                <a href="{{ route('dosen.absensi.index', $kelas) }}" class="text-sm text-indigo-600 hover:underline">&larr; Daftar pertemuan</a>
            </div>
        </form>
    </div>
</x-app-layout>
