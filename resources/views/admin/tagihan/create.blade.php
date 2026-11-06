@php $input = 'mt-1 w-full rounded border border-gray-300 px-3 py-2'; @endphp
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Tagihan</h2></x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="POST" action="{{ route('admin.tagihan.store') }}"
              class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Jenis Tagihan</label>
                <input type="text" name="jenis" required list="jenis-umum" placeholder="UKT / SPP / Praktikum"
                       value="{{ old('jenis', 'UKT') }}" class="{{ $input }}">
                <datalist id="jenis-umum"><option value="UKT"><option value="SPP"><option value="Praktikum"><option value="Wisuda"></datalist>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Keterangan (opsional)</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}" class="{{ $input }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" required value="{{ old('tahun_ajaran', $tahunAjaran) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="semester" class="{{ $input }}">
                    @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('semester', $semester) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                <input type="text" name="jumlah" required inputmode="numeric" placeholder="3.500.000" value="{{ old('jumlah') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
                <input type="date" name="jatuh_tempo" required value="{{ old('jatuh_tempo') }}" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Ditagihkan ke</label>
                <select name="target" id="target" class="{{ $input }}">
                    <option value="semua" @selected(old('target', 'semua') === 'semua')>Semua mahasiswa aktif</option>
                    <option value="prodi" @selected(old('target') === 'prodi')>Satu program studi</option>
                    <option value="mahasiswa" @selected(old('target') === 'mahasiswa')>Satu mahasiswa</option>
                </select>
            </div>

            <div class="sm:col-span-2" id="box-prodi" style="display:none">
                <label class="block text-sm font-medium text-gray-700">Program Studi</label>
                <select name="prodi_id" class="{{ $input }}">
                    <option value="">— pilih —</option>
                    @foreach ($prodi as $p)
                        <option value="{{ $p->id }}" @selected((int) old('prodi_id') === $p->id)>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2" id="box-mahasiswa" style="display:none">
                <label class="block text-sm font-medium text-gray-700">Mahasiswa</label>
                <select name="mahasiswa_id" class="{{ $input }}">
                    <option value="">— pilih —</option>
                    @foreach ($mahasiswa as $m)
                        <option value="{{ $m->id }}" @selected((int) old('mahasiswa_id') === $m->id)>{{ $m->nim }} — {{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>

            <p class="sm:col-span-2 text-xs text-gray-500">
                Mahasiswa yang sudah punya tagihan dengan jenis, tahun ajaran, dan semester yang sama akan dilewati.
            </p>

            <div class="sm:col-span-2 flex items-center gap-4">
                <button class="rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">Buat Tagihan</button>
                <a href="{{ route('admin.tagihan.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var sel = document.getElementById('target');
            function tampil() {
                document.getElementById('box-prodi').style.display = sel.value === 'prodi' ? 'block' : 'none';
                document.getElementById('box-mahasiswa').style.display = sel.value === 'mahasiswa' ? 'block' : 'none';
            }
            sel.addEventListener('change', tampil);
            tampil();
        })();
    </script>
</x-app-layout>
