@extends('layouts.app')

@section('title', __('vehicles') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('vehicles') }}</h2>
    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_vehicle') }}
    </a>
</div>

<!-- Filters -->
<div class="content-card mb-4">
    <div class="content-card-body">
        <form action="{{ route('admin.vehicles.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="{{ __('search') }} {{ __('vehicle_number') }}..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('status') }} - All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                        {{ __('active') }}
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('inactive') }}
                    </option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>
                        {{ __('maintenance') }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Vehicles Table -->
<div class="content-card">
    <div class="content-card-header">
        <h5>{{ __('vehicles') }}</h5>
        <span class="text-muted">Total: {{ $vehicles->total() }}</span>
    </div>
    <div class="content-card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('vehicle_number') }}</th>
                        <th>{{ __('vehicle_type') }}</th>
                        <th>{{ __('capacity') }}</th>
                        <th>{{ __('registration_date') }}</th>
                        <th>{{ __('insurance_expiry') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                        <td>{{ $vehicle->vehicleType->name }}</td>
                        <td>{{ $vehicle->vehicleType->capacity }}</td>
                        <td>
                            @if($vehicle->registration_date)
                                {{ $vehicle->registration_date->format('M d, Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($vehicle->insurance_expiry)
                                <span class="{{ $vehicle->insurance_expiry->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $vehicle->insurance_expiry->format('M d, Y') }}
                                    @if($vehicle->insurance_expiry->isPast())
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($vehicle->status === 'active')
                                <span class="badge bg-success">{{ __('active') }}</span>
                            @elseif($vehicle->status === 'inactive')
                                <span class="badge bg-secondary">{{ __('inactive') }}</span>
                            @elseif($vehicle->status === 'maintenance')
                                <span class="badge bg-warning">{{ __('maintenance') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" 
                                   class="btn-action btn-edit" title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" 
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
        @if($vehicles->hasPages())
        <div class="mt-4">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection