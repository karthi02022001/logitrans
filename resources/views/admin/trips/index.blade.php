@extends('layouts.app')

@section('title', __('trips') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-geo-alt"></i> {{ __('trips') }}</h2>
    <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('create_trip') }}
    </a>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.trips.index') }}" class="row g-3">
            <div class="col-md-5">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="{{ __('search') }}... ({{ __('trip_number') }}, {{ __('shipment_reference') }})"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('status') }} - All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>{{ __('assigned') }}</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>{{ __('in_transit') }}</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>{{ __('delivered') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> {{ __('search') }}
                </button>
                <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Status Filter Pills -->
<div class="mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.trips.index') }}" 
           class="badge {{ !request('status') ? 'bg-primary' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-grid"></i> All Trips
        </a>
        <a href="{{ route('admin.trips.index', ['status' => 'pending']) }}" 
           class="badge {{ request('status') === 'pending' ? 'bg-warning text-dark' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-clock"></i> {{ __('pending') }}
        </a>
        <a href="{{ route('admin.trips.index', ['status' => 'assigned']) }}" 
           class="badge {{ request('status') === 'assigned' ? 'bg-info' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-check-circle"></i> {{ __('assigned') }}
        </a>
        <a href="{{ route('admin.trips.index', ['status' => 'in_transit']) }}" 
           class="badge {{ request('status') === 'in_transit' ? 'bg-primary' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-truck"></i> {{ __('in_transit') }}
        </a>
        <a href="{{ route('admin.trips.index', ['status' => 'delivered']) }}" 
           class="badge {{ request('status') === 'delivered' ? 'bg-success' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-check-all"></i> {{ __('delivered') }}
        </a>
        <a href="{{ route('admin.trips.index', ['status' => 'cancelled']) }}" 
           class="badge {{ request('status') === 'cancelled' ? 'bg-danger' : 'bg-light text-dark' }} p-2 text-decoration-none">
            <i class="bi bi-x-circle"></i> {{ __('cancelled') }}
        </a>
    </div>
</div>

<!-- Trips Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>{{ __('trip_number') }}</th>
                        <th>{{ __('vehicle') }}</th>
                        <th>{{ __('driver') }}</th>
                        <th>{{ __('pickup') }}</th>
                        <th>{{ __('drop') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('start_date') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $trip->trip_number }}</strong>
                            @if($trip->shipment_reference)
                            <br><small class="text-muted">Ref: {{ $trip->shipment_reference }}</small>
                            @endif
                        </td>
                        <td>
                            @if($trip->vehicle)
                                <div>{{ $trip->vehicle->vehicle_number }}</div>
                                <small class="text-muted">{{ $trip->vehicle->vehicleType->name ?? 'N/A' }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($trip->driver)
                                <div>{{ $trip->driver->name }}</div>
                                <small class="text-muted">
                                    <i class="bi bi-{{ $trip->driver->driver_type === 'own_vehicle' ? 'truck' : 'person' }}"></i>
                                    {{ __($trip->driver->driver_type) }}
                                </small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ Str::limit($trip->pickup_location, 30) }}</small>
                        </td>
                        <td>
                            @if($trip->has_multiple_locations && $trip->drop_location)
                                <small>{{ Str::limit($trip->drop_location, 30) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'assigned' => 'info',
                                    'in_transit' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'assigned' => 'check-circle',
                                    'in_transit' => 'truck',
                                    'delivered' => 'check-all',
                                    'cancelled' => 'x-circle'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$trip->status] ?? 'secondary' }}">
                                <i class="bi bi-{{ $statusIcons[$trip->status] ?? 'circle' }}"></i>
                                {{ __($trip->status) }}
                            </span>
                        </td>
                        <td>
                            @if($trip->start_date)
                                {{ \Carbon\Carbon::parse($trip->start_date)->format('M d, Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.trips.show', $trip) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="{{ __('view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($trip->status === 'pending')
                                    {{-- Edit button only available for pending trips --}}
                                    <a href="{{ route('admin.trips.edit', $trip) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="{{ __('edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @else
                                    {{-- Show disabled edit button with tooltip for non-pending trips --}}
                                    <button class="btn btn-sm btn-secondary" 
                                            disabled
                                            title="Cannot edit {{ __($trip->status) }} trips"
                                            style="cursor: not-allowed;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif

                                @if(in_array($trip->status, ['pending', 'cancelled']))
                                    {{-- Delete button only available for pending or cancelled trips --}}
                                    <form action="{{ route('admin.trips.destroy', $trip) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('{{ __('confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                title="{{ __('delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- Show disabled delete button for active/delivered trips --}}
                                    <button class="btn btn-sm btn-secondary" 
                                            disabled
                                            title="Cannot delete {{ __($trip->status) }} trips"
                                            style="cursor: not-allowed;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">{{ __('no_records') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($trips->hasPages())
        <div class="mt-4">
            {{ $trips->links() }}
        </div>
        @endif
    </div>
</div>
@endsection