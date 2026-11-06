@extends('layouts.base')
@section('css')
        .sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sb-w); background: var(--ink); color: #b9c6d4; overflow-y: auto; z-index: 1040; transition: transform .25s ease; }
        .sidebar .brand { padding: 1.1rem 1.25rem; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: .2px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar .brand small { display: block; font-weight: 400; font-size: .72rem; color: #8fa3b8; }
        .sidebar .group { padding: 1rem 1.25rem .35rem; font-size: .75rem; color: #7d93a9; }
        .sidebar a.item { display: flex; align-items: center; gap: .7rem; padding: .55rem 1.25rem; color: #b9c6d4; text-decoration: none; font-size: .92rem; border-left: 3px solid transparent; }
        .sidebar a.item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar a.item.active { background: rgba(255,255,255,.1); color: #fff; border-left-color: #e0a93b; }
        .main { margin-left: var(--sb-w); min-height: 100vh; transition: margin .25s ease; }
        .topbar { background: #fff; border-bottom: 1px solid #e3e8ef; padding: .6rem 1.25rem; position: sticky; top: 0; z-index: 1030; }
        .content { padding: 1.25rem; }
        body.sb-collapsed .sidebar { transform: translateX(-100%); }
        body.sb-collapsed .main { margin-left: 0; }
        .backdrop-sb { display: none; }
        .stat-icon { width: 46px; height: 46px; border-radius: .6rem; display: grid; place-items: center; font-size: 1.4rem; }
        @media (max-width: 991.98px) {
            .main { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            body.sb-open .sidebar { transform: none; }
            body.sb-open .backdrop-sb { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1035; }
            body.sb-collapsed .sidebar { transform: translateX(-100%); }
        }
        @media print {
            .sidebar, .topbar, .no-print, .breadcrumb, .backdrop-sb { display: none !important; }
            .main { margin: 0 !important; } .content { padding: 0; } body { background: #fff; }
            .card { border: 0; }
        }
@endsection
@section('body')
<aside class="sidebar" id="sidebar">
    @include('partials.sidebar')
</aside>
<div class="backdrop-sb" onclick="document.body.classList.remove('sb-open')"></div>

<div class="main">
    <header class="topbar d-flex align-items-center gap-3">
        <button class="btn btn-light btn-sm" id="sbToggle" type="button" aria-label="Buka/tutup menu"><i class="bi bi-list fs-5"></i></button>
        <div class="fw-semibold d-none d-md-block">@yield('title', 'Dashboard')</div>
        <div class="ms-auto dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown" type="button">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                <span class="badge text-bg-secondary">{{ auth()->user()->role_label }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <main class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-3">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @yield('parent')
                @if (!request()->routeIs('dashboard'))
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                @endif
            </ol>
        </nav>

        @include('partials.alerts')
        @yield('content')
    </main>
</div>

<script>
    (function () {
        var b = document.body, mobile = function () { return window.innerWidth < 992; };
        try { if (!mobile() && localStorage.getItem('sb') === 'collapsed') b.classList.add('sb-collapsed'); } catch (e) {}
        document.getElementById('sbToggle').addEventListener('click', function () {
            if (mobile()) { b.classList.toggle('sb-open'); }
            else {
                b.classList.toggle('sb-collapsed');
                try { localStorage.setItem('sb', b.classList.contains('sb-collapsed') ? 'collapsed' : 'open'); } catch (e) {}
            }
        });
    })();
</script>
@endsection
