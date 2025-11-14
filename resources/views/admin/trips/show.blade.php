@extends('layouts.app')

@section('title', $trip->trip_number . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.trips.index') }}">{{ __('trips') }}</a></li>
                <li class="breadcrumb-item active">{{ $trip->trip_number }}</li>
            </ol>
        </nav>
        <h2><i class="bi bi-geo-alt"></i> Trip Details: {{ $trip->trip_number }}</h2>
    </div>
    <div class="d-flex gap-2">
        @if($trip->status === 'pending')
            {{-- Edit button only available for pending trips --}}
            <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> {{ __('edit') }}
            </a>
        @else
            {{-- Show disabled edit button with explanation for non-pending trips --}}
            <button class="btn btn-secondary" 
                    disabled
                    title="Cannot edit {{ __($trip->status) }} trips"
                    style="cursor: not-allowed;">
                <i class="bi bi-lock"></i> {{ __('edit') }} (Locked)
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
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> {{ __('delete') }}
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Warning banner for non-pending trips --}}
@if($trip->status !== 'pending')
<div class="alert alert-info border-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle fs-4 me-3"></i>
    <div>
        <strong>Read-Only Mode</strong><br>
        This trip is currently <strong>{{ __($trip->status) }}</strong> and cannot be edited. Only trips with "pending" status can be modified.
    </div>
</div>
@endif

<div class="row">
    <!-- Trip Information -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Trip Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">{{ __('trip_number') }}</label>
                        <h5 class="text-primary">{{ $trip->trip_number }}</h5>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">{{ __('status') }}</label>
                        <div>
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
                            <span class="badge bg-{{ $statusColors[$trip->status] ?? 'secondary' }} fs-6">
                                <i class="bi bi-{{ $statusIcons[$trip->status] ?? 'circle' }}"></i>
                                {{ __($trip->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">{{ __('shipment_reference') }}</label>
                        <p class="mb-0">{{ $trip->shipment_reference ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Location Type</label>
                        <p class="mb-0">
                            <i class="bi bi-{{ $trip->has_multiple_locations ? 'geo-alt' : 'pin-map' }}"></i>
                            {{ $trip->has_multiple_locations ? 'Multiple Locations' : 'Single Location' }}
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="text-primary mb-3"><i class="bi bi-calendar-range"></i> Trip Schedule</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">{{ __('start_date') }}</label>
                        <p class="mb-0">
                            @if($trip->start_date)
                                {{ \Carbon\Carbon::parse($trip->start_date)->format('F d, Y') }}
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">{{ __('end_date') }}</label>
                        <p class="mb-0">
                            @if($trip->end_date)
                                {{ \Carbon\Carbon::parse($trip->end_date)->format('F d, Y') }}
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle & Driver -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-truck-front"></i> Vehicle & Driver</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">{{ __('vehicle') }}</h6>
                        @if($trip->vehicle)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-truck-front fs-4"></i>
                                </div>
                                <div>
                                    <strong>{{ $trip->vehicle->vehicle_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $trip->vehicle->vehicleType->name ?? 'N/A' }}</small>
                                    <br>
                                    <span class="badge bg-{{ $trip->vehicle->status === 'active' ? 'success' : 'secondary' }} mt-1">
                                        {{ __($trip->vehicle->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($trip->vehicle->vehicleType)
                            <div class="small text-muted">
                                <i class="bi bi-box-seam"></i> Capacity: {{ $trip->vehicle->vehicleType->capacity }}
                            </div>
                            @endif
                        @else
                            <p class="text-muted">No vehicle assigned</p>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">{{ __('driver') }}</h6>
                        @if($trip->driver)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-person-badge fs-4"></i>
                                </div>
                                <div>
                                    <strong>{{ $trip->driver->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone"></i> {{ $trip->driver->mobile }}
                                    </small>
                                    <br>
                                    <span class="badge bg-{{ $trip->driver->driver_type === 'own_vehicle' ? 'primary' : 'info' }} mt-1">
                                        <i class="bi bi-{{ $trip->driver->driver_type === 'own_vehicle' ? 'truck' : 'person' }}"></i>
                                        {{ __($trip->driver->driver_type) }}
                                    </span>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-card-text"></i> License: {{ $trip->driver->license_number }}
                            </div>
                        @else
                            <p class="text-muted">No driver assigned</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Locations -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Locations</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small d-flex align-items-center">
                        <i class="bi bi-geo-fill text-success me-2"></i>
                        Pickup Location
                    </label>
                    <p class="mb-0">{{ $trip->pickup_location }}</p>
                </div>

                @if(!$trip->has_multiple_locations && $trip->drop_location)
                <div class="mb-3">
                    <label class="text-muted small d-flex align-items-center">
                        <i class="bi bi-geo text-danger me-2"></i>
                        Drop Location
                    </label>
                    <p class="mb-0">{{ $trip->drop_location }}</p>
                </div>
                @endif

                @if($trip->trip_instructions)
                <hr>
                <div>
                    <label class="text-muted small"><i class="bi bi-chat-left-text"></i> Trip Instructions</label>
                    <p class="mb-0">{{ $trip->trip_instructions }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Trip Files -->
        @if($trip->files->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Attached Files</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($trip->files as $file)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark"></i>
                            <strong>{{ $file->file_name }}</strong>
                            <br>
                            <small class="text-muted">
                                Uploaded by {{ $file->uploader->name ?? 'Unknown' }} on 
                                {{ \Carbon\Carbon::parse($file->created_at)->format('M d, Y') }}
                            </small>
                        </div>
                        <a href="{{ Storage::url($file->file_path) }}" 
                           class="btn btn-sm btn-primary" 
                           download
                           target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Cost Information -->
        @if($trip->cost)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Cost Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Base Cost</td>
                        <td class="text-end"><strong>{{ $trip->cost->base_cost }} USD</strong></td>
                    </tr>
                    <tr>
                        <td>Toll Cost</td>
                        <td class="text-end"><strong>{{ $trip->cost->toll_cost }} USD</strong></td>
                    </tr>
                    <tr>
                        <td>Driver Allowance</td>
                        <td class="text-end"><strong>{{ $trip->cost->driver_allowance }} USD</strong></td>
                    </tr>
                    <tr>
                        <td>Fuel Cost</td>
                        <td class="text-end"><strong>{{ $trip->cost->fuel_cost }} USD</strong></td>
                    </tr>
                    <tr>
                        <td>Other Costs</td>
                        <td class="text-end"><strong>{{ $trip->cost->other_costs }} USD</strong></td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Total Cost</strong></td>
                        <td class="text-end"><strong class="text-primary fs-5">{{ $trip->cost->total_cost }} USD</strong></td>
                    </tr>
                </table>
                @if($trip->cost->notes)
                <div class="mt-2">
                    <label class="text-muted small">Notes:</label>
                    <p class="mb-0 small">{{ $trip->cost->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Cost Details</h5>
            </div>
            <div class="card-body text-center">
                <p class="text-muted">No cost information available</p>
                <a href="{{ route('admin.costs.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Cost
                </a>
            </div>
        </div>
        @endif

        <!-- Delivery Information -->
        @if($trip->delivery)
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Delivery</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="text-muted small">Customer Name</label>
                    <p class="mb-0"><strong>{{ $trip->delivery->customer_name }}</strong></p>
                </div>
                <div class="mb-2">
                    <label class="text-muted small">Delivered At</label>
                    <p class="mb-0">{{ \Carbon\Carbon::parse($trip->delivery->delivered_at)->format('M d, Y h:i A') }}</p>
                </div>
                @if($trip->delivery->delivery_remarks)
                <div class="mb-2">
                    <label class="text-muted small">Remarks</label>
                    <p class="mb-0 small">{{ $trip->delivery->delivery_remarks }}</p>
                </div>
                @endif
                @if($trip->delivery->pod_file_path)
                <a href="{{ Storage::url($trip->delivery->pod_file_path) }}" 
                   class="btn btn-sm btn-primary w-100" 
                   target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> View POD
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <small class="text-muted">Created</small>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($trip->created_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($trip->updated_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 12px;
        bottom: -8px;
        width: 2px;
        background-color: #e2e8f0;
    }
</style>
@endpush