<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Nilai — {{ $kelas->mataKuliah->nama }} ({{ $kelas->nama }})
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        @if ($kelas->nilai_terkunci)
            <div class="mb-4 rounded bg-amber-100 px-4 py-3 text-sm text-amber-800">
                Nilai kelas ini sudah <strong>dikunci</strong> dan tidak bisa diubah. Hubungi admin jika perlu membuka kunci.
            </div>
        @endif

        <form method="POST" action="{{ route('dosen.nilai.update', $kelas) }}" class="bg-white shadow-sm sm:rounded-lg p-6">
            @csrf @method('PUT')
            <p class="text-xs text-gray-500 mb-3">Bobot: Tugas 30% · UTS 30% · UAS 40%. Nilai akhir dihitung otomatis saat disimpan.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left border-b text-gray-500">
                        <th class="py-2 pr-4">NIM</th><th class="pr-4">Nama</th>
                        <th class="pr-2">Tugas</th><th class="pr-2">UTS</th><th class="pr-4">UAS</th>
                        <th class="pr-4">Akhir</th><th>Huruf</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($peserta as $p)
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $p->mahasiswa->nim }}</td>
                            <td class="pr-4">{{ $p->mahasiswa->nama }}</td>
                            @foreach (['tugas' => 'nilai_tugas', 'uts' => 'nilai_uts', 'uas' => 'nilai_uas'] as $key => $kolom)
                                <td class="pr-2">
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="nilai[{{ $p->id }}][{{ $key }}]" value="{{ $p->$kolom }}"
                                           {{ $kelas->nilai_terkunci ? 'readonly' : '' }}
                                           class="w-20 rounded border border-gray-300 px-2 py-1 {{ $kelas->nilai_terkunci ? 'bg-gray-100' : '' }}">
                                </td>
                            @endforeach
                            <td class="pr-4">{{ $p->nilai_akhir ?? '-' }}</td>
                            <td>{{ $p->nilai_huruf ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-gray-500">Belum ada mahasiswa di kelas ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @unless ($kelas->nilai_terkunci)
                <button class="mt-4 rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">Simpan Nilai</button>
            @endunless
        </form>

        @unless ($kelas->nilai_terkunci)
            <form method="POST" action="{{ route('dosen.nilai.kunci', $kelas) }}" class="mt-4"
                  onsubmit="return confirm('Kunci nilai? Setelah dikunci, nilai tampil ke mahasiswa dan tidak bisa diubah.')">
                @csrf
                <button class="rounded bg-rose-600 px-4 py-2 font-medium text-white hover:bg-rose-700">Kunci &amp; Publikasikan Nilai</button>
                <span class="ml-2 text-xs text-gray-500">Simpan nilai dulu sebelum mengunci.</span>
            </form>
        @endunless
    </div>
</x-app-layout>
