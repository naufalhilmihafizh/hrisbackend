<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — HRIS</title>
    <meta name="description" content="HRIS Dashboard — Human Resource Information System">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">

    @stack('styles')
</head>
<body>
    <div class="app-wrapper" id="appWrapper">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-icon">
                    <i data-lucide="building-2" style="width:18px;height:18px;"></i>
                </div>
                <span class="sidebar-logo">HRIS</span>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">Menu Utama</div>

                <a href="{{ route('web.dashboard') }}" class="nav-item {{ request()->routeIs('web.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="nav-label">Dashboard</span>
                </a>

                <div class="nav-section">Manajemen</div>

                <a href="{{ route('web.employees.index') }}" class="nav-item {{ request()->routeIs('web.employees.*') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span class="nav-label">@if(Auth::user()->role === 'admin') Karyawan @else Tim Saya @endif</span>
                </a>

                @if(Auth::user()->role === 'manager')
                <a href="{{ route('web.teams.index') }}" class="nav-item {{ request()->routeIs('web.teams.*') ? 'active' : '' }}">
                    <i data-lucide="activity"></i>
                    <span class="nav-label">Monitoring Tim</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin')
                <a href="{{ route('web.departments.index') }}" class="nav-item {{ request()->routeIs('web.departments.*') ? 'active' : '' }}">
                    <i data-lucide="building-2"></i>
                    <span class="nav-label">Departemen</span>
                </a>

                <a href="{{ route('web.positions.index') }}" class="nav-item {{ request()->routeIs('web.positions.*') ? 'active' : '' }}">
                    <i data-lucide="briefcase"></i>
                    <span class="nav-label">Jabatan</span>
                </a>
                @endif

                <div class="nav-section">Monitoring</div>

                <a href="{{ route('web.attendances.index') }}" class="nav-item {{ request()->routeIs('web.attendances.*') ? 'active' : '' }}">
                    <i data-lucide="clock"></i>
                    <span class="nav-label">@if(Auth::user()->role === 'admin') Absensi @else Absensi Tim @endif</span>
                </a>

                <a href="{{ route('web.leaves.index') }}" class="nav-item {{ request()->routeIs('web.leaves.*') ? 'active' : '' }}">
                    <i data-lucide="calendar-off"></i>
                    <span class="nav-label">@if(Auth::user()->role === 'admin') Cuti @else Persetujuan Cuti @endif</span>
                </a>

                <a href="{{ route('web.overtimes.index') }}" class="nav-item {{ request()->routeIs('web.overtimes.*') ? 'active' : '' }}">
                    <i data-lucide="timer"></i>
                    <span class="nav-label">@if(Auth::user()->role === 'admin') Lembur @else Persetujuan Lembur @endif</span>
                </a>

                <div class="nav-section">Keuangan</div>

                <a href="{{ route('web.payrolls.index') }}" class="nav-item {{ request()->routeIs('web.payrolls.*') ? 'active' : '' }}">
                    <i data-lucide="wallet"></i>
                    <span class="nav-label">@if(Auth::user()->role === 'admin') Payroll @else Slip Gaji Saya @endif</span>
                </a>

                @if(Auth::user()->role === 'admin')
                <div class="nav-section">Laporan</div>
                <a href="{{ route('web.reports.index') }}" class="nav-item {{ request()->routeIs('web.reports.*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3"></i>
                    <span class="nav-label">Laporan HR</span>
                </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf
                    <button type="submit" class="nav-item w-full" style="border:none;background:none;text-align:left;">
                        <i data-lucide="log-out"></i>
                        <span class="nav-label">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Sidebar Mobile Overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        {{-- Main Content --}}
        <div class="main-content">
            {{-- Header --}}
            <header class="header">
                <div class="header-left">
                    <button class="header-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                        <i data-lucide="menu" style="width:20px;height:20px;"></i>
                    </button>
                    <h1 class="header-title">@yield('header-title', 'Dashboard')</h1>
                </div>

                <div class="header-right">
                    <div class="dropdown" id="userDropdown">
                        <div class="header-user" onclick="toggleDropdown('userDropdown')">
                            <div class="header-user-info">
                                <div class="header-user-name">{{ Auth::user()->name }}</div>
                                <div class="header-user-role">{{ Auth::user()->role }}</div>
                            </div>
                            <div class="header-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="dropdown-menu">
                            <a href="{{ route('web.account.profile') }}" class="dropdown-item">
                                <i data-lucide="user" style="width:16px;height:16px;"></i>
                                Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('web.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger w-full" style="border:none;background:none;cursor:pointer;">
                                    <i data-lucide="log-out" style="width:16px;height:16px;"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="content">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="x-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i data-lucide="x-circle"></i>
                        <div>
                            <strong>Terdapat kesalahan:</strong>
                            <ul style="margin-top:4px;padding-left:16px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Lucide Icons Init --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 640) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        }

        function toggleDropdown(id) {
            const el = document.getElementById(id);
            el.classList.toggle('open');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            document.querySelectorAll('.dropdown.open').forEach(function(dd) {
                if (!dd.contains(e.target)) {
                    dd.classList.remove('open');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
