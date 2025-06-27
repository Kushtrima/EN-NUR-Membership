<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Additional brand icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/brands.min.css">

    <style>
        /* Force refresh - version 2024 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background-color: #1F6E38 !important;
            background: #1F6E38 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            padding: 2rem 0 !important;
            min-height: 80px !important;
            border: none !important;
        }
        
        header.header {
            background-color: #1F6E38 !important;
            background: #1F6E38 !important;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
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
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
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
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #f8f9fa;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* Utility classes */
        .text-center {
            text-align: center;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .table {
                font-size: 0.875rem;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body>
    <div style="background-color: #1F6E38; background: #1F6E38; padding: 2rem 0; min-height: 80px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('dashboard') }}" style="font-size: 1.5rem; font-weight: bold; color: white; text-decoration: none;">
                    {{ config('app.name', 'Laravel SaaS') }}
                </a>

                <nav style="display: flex; gap: 1rem; align-items: center;">
                    @auth
                        <a href="{{ route('dashboard') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s;">
                            🏠 Dashboard
                        </a>
                        
                        <a href="{{ route('payment.create') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s;">
                            💳 Payments
                        </a>
                        
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s; background-color: rgba(255,255,255,0.1);">
                                🛡️ Admin Panel
                            </a>
                            <a href="{{ route('testing-dashboard') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s; background-color: rgba(255,255,255,0.15);">
                                🧪 Testing
                            </a>
                        @endif
                        
                        <a href="{{ route('profile.edit') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s;">
                            ⚙️ Profile
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 1rem;">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px;">Login</a>
                        <a href="{{ route('register') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 1rem;">Register</a>
                    @endauth
                </nav>
            </div>
        </div>
    </div>

    <main class="main">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</body>
</html> 