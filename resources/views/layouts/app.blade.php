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

    <!-- Inline Fallback for Core Utilities -->
    <style>
        .fs-7 { font-size: 0.85rem !important; }
        .sidebar-brand .brand-link { text-decoration: none; }
        .user-header { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; }
        .app-footer { margin-top: auto; }
        .app-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
        .app-main { flex: 1; }
    </style>

    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Navbar -->
        @include('layouts.partials.navbar')

        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <main class="app-main">
            @include('layouts.partials.content-header')

            <div class="app-content pb-4">
                <div class="container-fluid">
                    <!-- Session / Validation Alerts -->
                    @include('components.alert')

                    @yield('content')
                </div>
            </div>
        </main>

        <!-- Footer -->
        @include('layouts.partials.footer')
    </div>

    <!-- Core Local AdminLTE JS -->
    <script src="{{ asset('theme/adminLTE/dist/js/adminlte.min.js') }}"></script>

    <!-- Client-side Fallback & Server Clock Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Live Server Clock Simulation based on initial server time
            const clockEl = document.getElementById('server-clock');
            if (clockEl) {
                let parts = clockEl.innerText.split(':');
                if (parts.length === 3) {
                    let d = new Date();
                    d.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), parseInt(parts[2], 10));
                    setInterval(() => {
                        d.setSeconds(d.getSeconds() + 1);
                        clockEl.innerText = d.toTimeString().split(' ')[0];
                    }, 1000);
                }
            }

            // Fallback for native dropdown toggle (works offline even without bootstrap.js)
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        menu.classList.toggle('show');
                    }
                });
            });

            // Close dropdowns on outside click
            document.addEventListener('click', function () {
                document.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
                    menu.classList.remove('show');
                });
            });

            // Fallback for dismissing alerts
            document.querySelectorAll('[data-bs-dismiss="alert"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const alert = this.closest('.alert');
                    if (alert) alert.remove();
                });
            });

            // Fallback for modal show/hide triggers
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-bs-target');
                    if (targetId) {
                        const modal = document.querySelector(targetId);
                        if (modal) {
                            modal.classList.add('show');
                            modal.style.display = 'block';
                            modal.removeAttribute('aria-hidden');
                            modal.setAttribute('aria-modal', 'true');
                            
                            // Backdrop
                            let backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            backdrop.id = 'modal-backdrop-' + targetId.replace('#', '');
                            document.body.appendChild(backdrop);
                        }
                    }
                });
            });

            document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                        modal.setAttribute('aria-hidden', 'true');
                        modal.removeAttribute('aria-modal');
                        
                        const backdrop = document.getElementById('modal-backdrop-' + modal.id);
                        if (backdrop) backdrop.remove();
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
