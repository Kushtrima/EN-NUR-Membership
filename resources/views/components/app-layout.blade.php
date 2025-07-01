<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: #1F6E38 !important;
            background-color: #1F6E38 !important;
            border-bottom: 2px solid #1F6E38;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 2rem 0 !important;
            min-height: 80px !important;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: bold;
            color: white !important;
            text-decoration: none;
        }

        .nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav a {
            text-decoration: none;
            color: white !important;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            border: 1px solid transparent;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .nav a:hover {
            border-color: rgba(255, 255, 255, 0.3);
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav a.active {
            border-color: rgba(255, 255, 255, 0.5);
            color: white !important;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #C19A61;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #a67d42;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #1F6E38;
            border: none;
        }

        .btn-success:hover {
            background-color: #165028;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Main content */
        .main {
            padding: 2rem 0;
            min-height: calc(100vh - 120px);
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #1F6E38;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #333;
            font-weight: 600;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #C19A61;
            box-shadow: 0 0 0 2px rgba(193,154,97,0.25);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4f4d4;
            color: #1F6E38;
            border: 1px solid #1F6E38;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #1F6E38;
            height: 3rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .table tr:hover {
            background-color: #fdf9f0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                padding: 0 10px;
            }

            .card {
                padding: 1rem;
            }

            .table {
                font-size: 0.875rem;
            }

            .table th,
            .table td {
                padding: 0.5rem;
            }
        }

        /* Payment specific styles */
        .payment-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .payment-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .payment-card:hover {
            border-color: #C19A61;
            transform: translateY(-2px);
        }

        .payment-card.selected {
            border-color: #C19A61;
            background-color: #fdf9f0;
        }

        .payment-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #1F6E38;
            margin-bottom: 0.5rem;
        }

        .payment-type {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            color: #333;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Additional Green & Gold Theme Styles */
        .text-primary {
            color: #1F6E38 !important;
        }

        .text-gold {
            color: #C19A61 !important;
        }

        .bg-primary {
            background-color: #1F6E38 !important;
        }

        .bg-gold {
            background-color: #C19A61 !important;
        }

        .border-primary {
            border-color: #1F6E38 !important;
        }

        .border-gold {
            border-color: #C19A61 !important;
        }

        /* Enhanced payment cards */
        .payment-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .payment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(193,154,97,0.1), transparent);
            transition: left 0.5s;
        }

        .payment-card:hover::before {
            left: 100%;
        }

        /* Enhanced buttons */
        .btn-outline-primary {
            background-color: transparent;
            border: 2px solid #1F6E38;
            color: #1F6E38;
        }

        .btn-outline-primary:hover {
            background-color: #1F6E38;
            color: white;
        }

        .btn-outline-gold {
            background-color: transparent;
            border: 2px solid #C19A61;
            color: #C19A61;
        }

        .btn-outline-gold:hover {
            background-color: #C19A61;
            color: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="logo">
                    {{ config('app.name', 'EN NUR - MEMBERSHIP') }}
                </a>
                
                <nav class="nav">
                    @auth
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Paneli</a>
                        <a href="{{ route('payment.create') }}" class="{{ request()->routeIs('payment.*') && !request()->routeIs('exports.*') ? 'active' : '' }}">Krijo Pagesë</a>
                        <a href="{{ route('payment.index') }}" class="{{ request()->routeIs('payment.index') || request()->routeIs('admin.payments*') ? 'active' : '' }}">Pagesat</a>
                        
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">Anëtarët</a>
                        @endif
                        
                        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">Profili</a>
                        
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">Dil</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Hyr</a>
                        <a href="{{ route('register') }}" class="btn">Regjistrohu</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Page Content -->
            {{ $slot }}
        </div>
    </main>

    <footer style="background: linear-gradient(135deg, #1F6E38, #165028); color: white; text-align: center; padding: 1rem 0; margin-top: 2rem; border-top: 3px solid #C19A61;">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 