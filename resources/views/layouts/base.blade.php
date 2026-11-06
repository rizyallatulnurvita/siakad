<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIAKAD') — SIAKAD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-font-sans-serif: 'IBM Plex Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            --bs-primary: #1f5f8b; --bs-primary-rgb: 31, 95, 139;
            --bs-link-color: #1f5f8b; --bs-link-hover-color: #184c70;
            --ink: #12263a; --sb-w: 264px;
        }
        body { background: #f3f5f8; color: #1d2733; }
        .btn-primary { --bs-btn-bg: #1f5f8b; --bs-btn-border-color: #1f5f8b; --bs-btn-hover-bg: #184c70; --bs-btn-hover-border-color: #184c70; --bs-btn-active-bg: #143f5d; --bs-btn-active-border-color: #143f5d; }
        .btn-outline-primary { --bs-btn-color: #1f5f8b; --bs-btn-border-color: #1f5f8b; --bs-btn-hover-bg: #1f5f8b; --bs-btn-hover-border-color: #1f5f8b; }
        .card { border: 1px solid #e3e8ef; border-radius: .6rem; box-shadow: none; }
        .card-header { background: #fff; font-weight: 600; }
        .table > :not(caption) > * > * { padding: .6rem .75rem; }
        .table thead th { font-weight: 600; font-size: .8rem; color: #5b6b7c; background: #f8fafc; white-space: nowrap; }
        .form-label { font-weight: 500; font-size: .9rem; }
        @yield('css')
    </style>
    @stack('head')
</head>
<body>
@yield('body')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
