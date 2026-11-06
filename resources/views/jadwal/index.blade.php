<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Jadwal Perkuliahan</h2></x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($hari as $h)
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <h3 class="font-semibold text-indigo-700 mb-3">{{ $h }}</h3>
                    @forelse ($jadwal->get($h, collect()) as $k)
                        <div class="mb-3 rounded border border-gray-200 p-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $k->mataKuliah->nama }} <span class="text-gray-500">({{ $k->nama }})</span></div>
                            <div class="text-gray-600">{{ substr($k->jam_mulai, 0, 5) }} – {{ substr($k->jam_selesai, 0, 5) }} · {{ $k->ruang ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $k->dosen->nama }} · {{ $k->mataKuliah->sks }} SKS</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Tidak ada kuliah.</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
