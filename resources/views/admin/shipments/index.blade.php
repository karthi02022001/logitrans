@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>{{ __('Shipment Management') }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.shipments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('Create Shipment') }}
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

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.shipments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search by shipment number') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>{{ __('Assigned') }}</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>{{ __('In Transit') }}</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">{{ __('All Priority') }}</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="vehicle_type_id" class="form-select">
                        <option value="">{{ __('All Vehicle Types') }}</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="card">
        <div class="card-body">
            @if($shipments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Shipment Number') }}</th>
                                <th>{{ __('Vehicle Type') }}</th>
                                <th>{{ __('Cargo Weight') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                                <tr>
                                    <td><strong>{{ $shipment->shipment_number }}</strong></td>
                                    <td>{{ $shipment->vehicleType->name }}</td>
                                    <td>{{ $shipment->cargo_weight ? number_format($shipment->cargo_weight, 2) . ' T' : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $shipment->getPriorityBadgeClass() }}">
                                            {{ __(ucfirst($shipment->priority)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $shipment->getStatusBadgeClass() }}">
                                            {{ __(ucfirst(str_replace('_', ' ', $shipment->status))) }}
                                        </span>
                                    </td>
                                    <td>{{ $shipment->creator->name }}</td>
                                    <td>{{ $shipment->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-info" title="{{ __('View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($shipment->status === 'pending')
                                                <a href="{{ route('admin.shipments.edit', $shipment) }}" class="btn btn-warning" title="{{ __('Edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.shipments.destroy', $shipment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $shipments->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    {{ __('No shipments found') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection