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

    <!-- Polished AdminLTE Component & Layout Styling -->
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .fs-7 { font-size: 0.85rem !important; }
        .fs-8 { font-size: 0.75rem !important; }

        .sidebar-brand .brand-link { text-decoration: none; }
        .user-header { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; }

        /* Ensure Sidebar is full height and scrolls smoothly */
        .app-sidebar {
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .app-sidebar .sidebar-wrapper {
            height: calc(100vh - 3.5rem);
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Sidebar Item Polish */
        .sidebar-menu .nav-link {
            border-radius: 0.375rem;
            padding: 0.55rem 0.85rem;
            margin-bottom: 2px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
            display: flex;
            align-items: center;
        }
        .sidebar-menu .nav-link .nav-icon {
            width: 1.25rem;
            height: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-menu .nav-link.active {
            background-color: var(--bs-primary);
            color: #fff !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .sidebar-menu .nav-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.45) !important;
            padding: 0.85rem 0.85rem 0.35rem;
        }

        /* Small Box Widgets (AdminLTE Native) */
        .small-box {
            position: relative;
            display: block;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1.25rem;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .small-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .small-box > .inner {
            padding: 1.25rem 1.25rem 1rem;
            position: relative;
            z-index: 2;
        }
        .small-box h3 {
            font-size: 2.15rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
            white-space: nowrap;
        }
        .small-box p {
            font-size: 0.925rem;
            margin-bottom: 0;
            opacity: 0.9;
        }
        .small-box .small-box-icon {
            position: absolute;
            top: 1rem;
            right: 1.25rem;
            z-index: 1;
            width: 64px;
            height: 64px;
            max-width: 64px;
            max-height: 64px;
            opacity: 0.25;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .small-box:hover .small-box-icon {
            transform: scale(1.1);
            opacity: 0.35;
        }
        .small-box > .small-box-footer {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            background-color: rgba(0, 0, 0, 0.15);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }
        .small-box > .small-box-footer:hover {
            background-color: rgba(0, 0, 0, 0.25);
        }

        /* Info Box Widgets */
        .info-box {
            display: flex;
            align-items: center;
            min-height: 80px;
            background-color: var(--bs-card-bg, #fff);
            border: 1px solid var(--bs-border-color-translucent, rgba(0, 0, 0, 0.08));
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        }
        .info-box .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 0.375rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .info-box .info-box-content {
            flex: 1;
            min-width: 0;
        }
        .info-box-text {
            font-size: 0.8rem;
            color: var(--bs-secondary-color, #6c757d);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.03em;
            margin-bottom: 0.15rem;
        }
        .info-box-number {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--bs-body-color, #212529);
            margin-bottom: 0;
        }

        /* Callouts */
        .callout {
            padding: 1rem 1.25rem;
            border-left: 4px solid var(--bs-border-color);
            border-radius: 0.375rem;
            background-color: var(--bs-tertiary-bg, #f8f9fa);
            margin-bottom: 1.25rem;
        }
        .callout-primary { border-left-color: #0d6efd; background-color: rgba(13, 110, 253, 0.05); }
        .callout-success { border-left-color: #198754; background-color: rgba(25, 135, 84, 0.05); }
        .callout-warning { border-left-color: #ffc107; background-color: rgba(255, 193, 7, 0.07); }
        .callout-info { border-left-color: #0dcaf0; background-color: rgba(13, 202, 240, 0.06); }

        /* Card Outlines */
        .card {
            border: 1px solid var(--bs-border-color-translucent, rgba(0, 0, 0, 0.08));
            border-radius: 0.5rem;
        }
        .card.card-outline {
            border-top: 3px solid var(--bs-primary);
        }
        .card.card-outline.card-primary { border-top-color: #0d6efd; }
        .card.card-outline.card-success { border-top-color: #198754; }
        .card.card-outline.card-warning { border-top-color: #ffc107; }
        .card.card-outline.card-danger { border-top-color: #dc3545; }
        .card.card-outline.card-info { border-top-color: #0dcaf0; }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0, 0, 0, 0.08));
            padding: 0.85rem 1.25rem;
        }
        .card-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0;
        }

        /* Table Aesthetics */
        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--bs-secondary-color, #6c757d);
        }
        .table td {
            font-size: 0.925rem;
            vertical-align: middle;
        }
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

    <!-- Client-side Fallback & Server Clock Script (100% Offline Compatible) -->
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

            // Fallback for native dropdown toggle
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

            // Fallback for Tab navigation ([data-bs-toggle="tab"] / [data-bs-toggle="pill"])
            document.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]').forEach(function (tabBtn) {
                tabBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetSelector = this.getAttribute('data-bs-target') || this.getAttribute('href');
                    if (!targetSelector) return;
                    
                    const nav = this.closest('.nav');
                    if (nav) {
                        nav.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
                    }
                    this.classList.add('active');

                    const tabContent = document.querySelector(targetSelector)?.closest('.tab-content');
                    if (tabContent) {
                        tabContent.querySelectorAll('.tab-pane').forEach(pane => {
                            pane.classList.remove('show', 'active');
                        });
                    }
                    const targetPane = document.querySelector(targetSelector);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
