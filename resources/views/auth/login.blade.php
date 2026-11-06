<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIAKAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-bold text-indigo-700 text-center mb-1">SIAKAD</h1>
        <p class="text-center text-sm text-gray-500 mb-6">Sistem Informasi Akademik</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
            <button class="w-full rounded bg-indigo-600 py-2 font-medium text-white hover:bg-indigo-700">Masuk</button>
        </form>
    </div>
</body>
</html>
