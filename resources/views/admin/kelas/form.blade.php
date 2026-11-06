@php
    $edit = $kelas->exists;
    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $jam = fn ($f) => old($f, $kelas->$f ? substr($kelas->$f, 0, 5) : '');
    $input = 'mt-1 w-full rounded border border-gray-300 px-3 py-2';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $edit ? 'Edit Kelas' : 'Tambah Kelas' }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')

        <form method="POST" action="{{ $edit ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}"
              class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            @if ($edit) @method('PUT') @endif

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                @if ($adaKrs)
                    <input type="hidden" name="mata_kuliah_id" value="{{ $kelas->mata_kuliah_id }}">
                    <div class="{{ $input }} bg-gray-100">{{ $kelas->mataKuliah->kode }} — {{ $kelas->mataKuliah->nama }}</div>
                    <p class="mt-1 text-xs text-gray-500">Tidak bisa diganti karena kelas sudah memiliki peserta.</p>
                @else
                    <select name="mata_kuliah_id" required class="{{ $input }}">
                        <option value="">— pilih mata kuliah —</option>
                        @foreach ($mataKuliah as $mk)
                            <option value="{{ $mk->id }}" @selected((int) old('mata_kuliah_id', $kelas->mata_kuliah_id) === $mk->id)>
                                {{ $mk->kode }} — {{ $mk->nama }} ({{ $mk->sks }} SKS)
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Dosen Pengampu</label>
                <select name="dosen_id" required class="{{ $input }}">
                    <option value="">— pilih dosen —</option>
                    @foreach ($dosen as $d)
                        <option value="{{ $d->id }}" @selected((int) old('dosen_id', $kelas->dosen_id) === $d->id)>
                            {{ $d->nama }} ({{ $d->nidn }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                <input type="text" name="nama" maxlength="5" required placeholder="A" value="{{ old('nama', $kelas->nama) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kuota</label>
                <input type="number" name="kuota" min="1" max="500" required value="{{ old('kuota', $kelas->kuota) }}" class="{{ $input }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" required placeholder="2026/2027" value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="semester" required class="{{ $input }}">
                    @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('semester', $kelas->semester) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Hari</label>
                <select name="hari" class="{{ $input }}">
                    <option value="">— belum dijadwalkan —</option>
                    @foreach ($hariList as $h)
                        <option value="{{ $h }}" @selected(old('hari', $kelas->hari) === $h)>{{ $h }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Ruang</label>
                <input type="text" name="ruang" maxlength="30" value="{{ old('ruang', $kelas->ruang) }}" class="{{ $input }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                <input type="time" name="jam_mulai" value="{{ $jam('jam_mulai') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                <input type="time" name="jam_selesai" value="{{ $jam('jam_selesai') }}" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2 flex items-center gap-4 pt-2">
                <button class="rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">
                    {{ $edit ? 'Simpan Perubahan' : 'Tambah Kelas' }}
                </button>
                <a href="{{ route('admin.kelas.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            </div>
        </form>

        @if ($mataKuliah->isEmpty() || $dosen->isEmpty())
            <p class="mt-4 text-sm text-amber-700">Data mata kuliah atau dosen masih kosong. Jalankan seeder (db:seed) atau tambahkan datanya dulu.</p>
        @endif
    </div>
</x-app-layout>
