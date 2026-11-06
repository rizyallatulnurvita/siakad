@php $input = 'mt-1 w-full rounded border border-gray-300 px-3 py-2'; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bayar — {{ $tagihan->jenis }} {{ $tagihan->tahun_ajaran }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('partials.alerts')

        {{-- GANTI dengan data rekening resmi kampus --}}
        <div class="rounded bg-indigo-50 border border-indigo-200 p-4 text-sm text-indigo-900">
            <div class="font-semibold mb-1">Rekening tujuan pembayaran</div>
            <div>Bank: <strong>NAMA BANK</strong> · No. Rekening: <strong>000-000-0000</strong></div>
            <div>Atas nama: <strong>NAMA KAMPUS</strong></div>
            <div class="mt-2 text-xs">Transfer sesuai jumlah, lalu unggah bukti di form berikut. Pembayaran sah setelah diverifikasi admin.</div>
        </div>

        <form method="POST" action="{{ route('mahasiswa.tagihan.bayar.store', $tagihan) }}" enctype="multipart/form-data"
              class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            @csrf
            <p class="text-sm text-gray-600">Maksimal yang bisa dibayar sekarang:
                <strong>Rp {{ number_format($maks, 0, ',', '.') }}</strong> (boleh dicicil).</p>

            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah dibayar (Rp)</label>
                <input type="text" name="jumlah" required inputmode="numeric" value="{{ old('jumlah', $maks) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal bayar</label>
                <input type="date" name="tanggal_bayar" required max="{{ now()->toDateString() }}"
                       value="{{ old('tanggal_bayar', now()->toDateString()) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Metode</label>
                <select name="metode" class="{{ $input }}">
                    @foreach (['Transfer Bank', 'QRIS', 'E-Wallet'] as $m)
                        <option value="{{ $m }}" @selected(old('metode') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Bukti pembayaran (JPG / PNG / PDF, maks. 2 MB)</label>
                <input type="file" name="bukti" required accept=".jpg,.jpeg,.png,.pdf" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                <input type="text" name="catatan" value="{{ old('catatan') }}" class="{{ $input }}">
            </div>

            <div class="flex items-center gap-4">
                <button class="rounded bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700">Kirim Bukti Pembayaran</button>
                <a href="{{ route('mahasiswa.tagihan.show', $tagihan) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
