@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>{{ __('Shipment Details') }}</h2>
        </div>
        <div class="col-md-6 text-end">
            @if($shipment->status === 'pending')
                <a href="{{ route('admin.shipments.assign-vehicle', $shipment) }}" class="btn btn-success">
                    <i class="bi bi-truck"></i> {{ __('Assign Vehicle & Driver') }}
                </a>
                <a href="{{ route('admin.shipments.edit', $shipment) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                </a>
            @endif
            <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Back to List') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Shipment Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Shipment Information') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 40%">{{ __('Shipment Number') }}:</th>
                            <td><strong>{{ $shipment->shipment_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ __('Vehicle Type') }}:</th>
                            <td>{{ $shipment->vehicleType->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Capacity') }}:</th>
                            <td>{{ $shipment->vehicleType->capacity }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Cargo Weight') }}:</th>
                            <td>{{ $shipment->cargo_weight ? number_format($shipment->cargo_weight, 2) . ' T' : '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Priority') }}:</th>
                            <td>
                                <span class="badge bg-{{ $shipment->getPriorityBadgeClass() }}">
                                    {{ __(ucfirst($shipment->priority)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Status') }}:</th>
                            <td>
                                <span class="badge bg-{{ $shipment->getStatusBadgeClass() }}">
                                    {{ __(ucfirst(str_replace('_', ' ', $shipment->status))) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Created By') }}:</th>
                            <td>{{ $shipment->creator->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Created At') }}:</th>
                            <td>{{ $shipment->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>

                    @if($shipment->status === 'pending')
                        <div class="mt-3">
                            <form action="{{ route('admin.shipments.cancel', $shipment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to cancel this shipment?') }}')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-circle"></i> {{ __('Cancel Shipment') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Available Vehicles Preview (for pending shipments) -->
        <div class="col-md-6">
            @if($shipment->status === 'pending')
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Available Vehicles') }} ({{ $availableVehicles->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($availableVehicles->count() > 0)
                            <p class="text-muted">{{ __('Click "Assign Vehicle & Driver" button to view available vehicles and assign to this shipment.') }}</p>
                            <div class="list-group">
                                @foreach($availableVehicles->take(5) as $vehicle)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $vehicle->vehicle_number }}</strong><br>
                                                <small class="text-muted">{{ $vehicle->vehicleType->name }}</small>
                                            </div>
                                            <span class="badge bg-success">{{ __('Available') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                @if($availableVehicles->count() > 5)
                                    <div class="list-group-item text-center text-muted">
                                        <small>{{ __('And') }} {{ $availableVehicles->count() - 5 }} {{ __('more vehicles...') }}</small>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                {{ __('No available vehicles found for this vehicle type') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assigned Trips -->
    @if($shipment->trips->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Assigned Trips') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Trip Number') }}</th>
                                <th>{{ __('Vehicle') }}</th>
                                <th>{{ __('Driver') }}</th>
                                <th>{{ __('Pickup Location') }}</th>
                                <th>{{ __('Drop Location') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipment->trips as $trip)
                                <tr>
                                    <td><strong>{{ $trip->trip_number }}</strong></td>
                                    <td>{{ $trip->vehicle->vehicle_number }}</td>
                                    <td>{{ $trip->driver->name }}</td>
                                    <td>{{ Str::limit($trip->pickup_location, 30) }}</td>
                                    <td>{{ $trip->drop_location ? Str::limit($trip->drop_location, 30) : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $trip->status_color }}">
                                            {{ __(ucfirst(str_replace('_', ' ', $trip->status))) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.trips.show', $trip) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> {{ __('View Trip') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection