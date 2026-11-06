<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Profil Saya</h2></x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <hr>
            <p class="text-sm text-gray-500">Kosongkan bagian password jika tidak ingin menggantinya.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password saat ini</label>
                <input type="password" name="current_password" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password baru</label>
                <input type="password" name="password" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Konfirmasi password baru</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <button class="rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">Simpan</button>
        </form>
    </div>
</x-app-layout>
