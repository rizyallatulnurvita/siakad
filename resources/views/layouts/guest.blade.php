@extends('layouts.base')
@section('css')
        body { min-height: 100vh; display: grid; place-items: center; background: linear-gradient(160deg, #12263a 0%, #1f5f8b 100%); padding: 1rem; }
        .auth-card { width: 100%; max-width: 420px; }
@endsection
@section('body')
<div class="auth-card">
    @yield('content')
</div>
@endsection
