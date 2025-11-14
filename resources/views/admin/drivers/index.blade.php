@extends('layouts.app')

@section('title', __('drivers') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('drivers') }}</h2>
    <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_driver') }}
    </a>
</div>

<!-- Filters -->
<div class="content-card mb-4">
    <div class="content-card-body">
        <form action="{{ route('admin.drivers.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="{{ __('search') }} {{ __('driver_name') }}, {{ __('mobile') }}..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="driver_type" class="form-select">
                    <option value="">{{ __('driver_type') }} - All</option>
                    <option value="own_vehicle" {{ request('driver_type') === 'own_vehicle' ? 'selected' : '' }}>
                        {{ __('own_vehicle') }}
                    </option>
                    <option value="driver_only" {{ request('driver_type') === 'driver_only' ? 'selected' : '' }}>
                        {{ __('driver_only') }}
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('status') }} - All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                        {{ __('active') }}
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('inactive') }}
                    </option>
                    <option value="on_trip" {{ request('status') === 'on_trip' ? 'selected' : '' }}>
                        {{ __('on_trip') }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> {{__("Filter")}}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Drivers Table -->
<div class="content-card">
    <div class="content-card-header">
        <h5>{{ __('drivers') }}</h5>
        <span class="text-muted">Total: {{ $drivers->total() }}</span>
    </div>
    <div class="content-card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('driver_name') }}</th>
                        <th>{{ __('mobile') }}</th>
                        <th>{{ __('license_number') }}</th>
                        <th>{{ __('driver_type') }}</th>
                        <th>{{ __('vehicle') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr>
                        <td><strong>{{ $driver->name }}</strong></td>
                        <td>{{ $driver->mobile }}</td>
                        <td>{{ $driver->license_number }}</td>
                        <td>
                            @if($driver->driver_type === 'own_vehicle')
                                <span class="badge bg-primary">{{ __('own_vehicle') }}</span>
                            @else
                                <span class="badge bg-info">{{ __('driver_only') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->ownVehicle)
                                <span class="text-primary">
                                    <i class="bi bi-truck-front"></i> {{ $driver->ownVehicle->vehicle_number }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->status === 'active')
                                <span class="badge bg-success">{{ __('active') }}</span>
                            @elseif($driver->status === 'inactive')
                                <span class="badge bg-secondary">{{ __('inactive') }}</span>
                            @elseif($driver->status === 'on_trip')
                                <span class="badge bg-warning">{{ __('on_trip') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.drivers.edit', $driver) }}" 
                                   class="btn-action btn-edit" title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.drivers.destroy', $driver) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete delete-confirm" 
                                            title="{{ __('delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            {{ __('no_records') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($drivers->hasPages())
        <div class="mt-4">
            {{ $drivers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection