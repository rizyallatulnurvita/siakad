@if (session('status'))
    <div class="mb-4 rounded bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded bg-red-100 px-4 py-3 text-sm text-red-800">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
