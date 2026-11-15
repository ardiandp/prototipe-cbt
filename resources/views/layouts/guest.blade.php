<!doctype html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CBT School - Computer Based Test')</title>

    <!-- Theme Init (No-flash Dark/Light Mode) -->
    <script>
        (() => {
            'use strict';
            const root = document.documentElement;
            const STORAGE_KEY = 'cbt-theme';
            let stored = null;
            try {
                stored = localStorage.getItem(STORAGE_KEY);
            } catch (e) {}
            const authored = root.getAttribute('data-bs-theme');
            let resolved = 'light';
            if (stored === 'dark' || stored === 'light') {
                resolved = stored;
            } else if (authored === 'dark' || authored === 'light') {
                resolved = authored;
            }
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
        })();
    </script>

    <!-- Core Local AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('theme/adminLTE/dist/css/adminlte.min.css') }}">

    <style>
        body.login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-box {
            width: 100%;
            max-width: 440px;
            padding: 15px;
        }
        .fs-7 { font-size: 0.85rem !important; }
    </style>

    @stack('styles')
</head>
<body class="@yield('body_class', 'login-page bg-body-secondary')">
    @yield('content')

    <!-- Core Local AdminLTE JS -->
    <script src="{{ asset('theme/adminLTE/dist/js/adminlte.min.js') }}"></script>
    @stack('scripts')
</body>
</html>

