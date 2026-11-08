<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KoperasiKu') | AdminLTE</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"
                   role="button" aria-label="Buka atau tutup sidebar">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            @auth
                <li class="nav-item d-flex align-items-center mr-3">
                    <span class="text-dark">
                        <i class="fas fa-user-circle mr-1"></i>
                        {{ auth()->user()->name }}
                        ({{ ucfirst(auth()->user()->role) }})
                    </span>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm mt-1">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                </li>
            @endauth
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ url('/') }}" class="brand-link text-center">
            <i class="fas fa-landmark ml-2 mr-1"></i>
            <span class="brand-text font-weight-light"><strong>KoperasiKu</strong></span>
        </a>

        <div class="sidebar">
            @auth
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    </div>
                    <div class="info">
                        <span class="d-block text-white">{{ auth()->user()->name }}</span>
                        <small class="text-light">{{ ucfirst(auth()->user()->role) }}</small>
                    </div>
                </div>
            @endauth

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview" role="menu" data-accordion="false">

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    {{-- ===== MANAJEMEN KOPERASI ===== --}}
                    <li class="nav-header">MANAJEMEN KOPERASI</li>

                    @if(Route::has('anggota.index'))
                        <li class="nav-item">
                            <a href="{{ route('anggota.index') }}"
                               class="nav-link {{ request()->routeIs('anggota.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Data Anggota</p>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()?->role === 'admin' && Route::has('users.index'))
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}"
                               class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-cog"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                    @endif

                    {{-- ===== TRANSAKSI KOPERASI ===== --}}
                    <li class="nav-header">TRANSAKSI KOPERASI</li>

                    @if(Route::has('simpanan.index'))
                        <li class="nav-item">
                            <a href="{{ route('simpanan.index') }}"
                               class="nav-link {{ request()->routeIs('simpanan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-wallet"></i>
                                <p>Simpanan</p>
                            </a>
                        </li>
                    @endif

                    @if(Route::has('pinjaman.index'))
                        <li class="nav-item">
                            <a href="{{ route('pinjaman.index') }}"
                               class="nav-link {{ request()->routeIs('pinjaman.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <p>Pinjaman</p>
                            </a>
                        </li>
                    @endif

                    @if(Route::has('angsuran.index'))
                        <li class="nav-item">
                            <a href="{{ route('angsuran.index') }}"
                               class="nav-link {{ request()->routeIs('angsuran.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-money-check-alt"></i>
                                <p>Angsuran</p>
                            </a>
                        </li>
                    @endif

                    {{-- ===== LAPORAN ===== --}}
                    <li class="nav-header">LAPORAN</li>

                    @if(Route::has('laporan.index'))
                        <li class="nav-item">
                            <a href="{{ route('laporan.index') }}"
                               class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Laporan</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Untuk halaman Breeze (x-app-layout), mis. Profil --}}
                {{ $header ?? '' }}
                {{ $slot ?? '' }}

                {{-- Untuk halaman AdminLTE (@extends) --}}
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; {{ date('Y') }} KoperasiKu.</strong>
        Sistem Informasi Koperasi Simpan Pinjam.
        <div class="float-right d-none d-sm-inline-block"><b>Versi</b> 1.0</div>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>