<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app_name'))</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-truck"></i>
            <span>LogiFlow</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>{{ __('dashboard') }}</span>
                </a>
            </li>

            @if(auth()->check() && auth()->user()->hasPermission('vehicle_types.view'))
            <li>
                <a href="{{ route('admin.vehicle-types.index') }}" class="{{ request()->routeIs('admin.vehicle-types.*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>{{ __('vehicle_types') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('vehicles.view'))
            <li>
                <a href="{{ route('admin.vehicles.index') }}" class="{{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                    <i class="bi bi-truck-front"></i>
                    <span>{{ __('vehicles') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('drivers.view'))
            <li>
                <a href="{{ route('admin.drivers.index') }}" class="{{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>{{ __('drivers') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('shipments.view'))
            <li>
                <a href="{{ route('admin.shipments.index') }}" class="{{ request()->routeIs('admin.shipments.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>{{ __('shipments') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('trips.view'))
            <li>
                <a href="{{ route('admin.trips.index') }}" class="{{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i>
                    <span>{{ __('trips') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('deliveries.view'))
            <li>
                <a href="{{ route('admin.deliveries.index') }}" class="{{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>{{ __('deliveries') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasAnyPermission(['trips.view', 'trips.create', 'trips.edit']))
            <li>
                <a href="{{ route('admin.costs.index') }}" class="{{ request()->routeIs('admin.costs.*') ? 'active' : '' }}">
                    <i class="bi bi-currency-dollar"></i>
                    <span>{{ __('costs') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('users.view'))
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>{{ __('users') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->hasPermission('roles.view'))
            <li>
                <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>{{ __('roles') }}</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div>
                @if(auth()->check())
                    {{ __('welcome') }}, <strong>{{ auth()->user()->name }}</strong>
                    @if(auth()->user()->roles->isNotEmpty())
                        <span class="badge bg-primary ms-2">{{ auth()->user()->roles->first()->display_name }}</span>
                    @endif
                @endif
            </div>
            <div class="user-menu">
                <div class="language-switcher">
                    <select onchange="window.location.href='/lang/' + this.value" class="form-select form-select-sm">
                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="hi" {{ app()->getLocale() == 'hi' ? 'selected' : '' }}>हिंदी</option>
                        <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>Español</option>
                        <option value="ta" {{ app()->getLocale() == 'ta' ? 'selected' : '' }}>தமிழ்</option>
                    </select>
                </div>
                @if(auth()->check())
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> {{ __('logout') }}
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Page Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>