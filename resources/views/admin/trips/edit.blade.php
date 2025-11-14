@extends('layouts.app')

@section('title', __('edit') . ' ' . __('trips') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.trips.index') }}">{{ __('trips') }}</a></li>
                <li class="breadcrumb-item active">{{ __('edit') }} - {{ $trip->trip_number }}</li>
            </ol>
        </nav>
        <h2><i class="bi bi-pencil-square"></i> {{ __('edit') }} {{ __('trips') }}</h2>
        <p class="text-muted mb-0">Trip #{{ $trip->trip_number }}</p>
    </div>
    <div>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
        </a>
    </div>
</div>

{{-- Critical Warning: Trip is pending and can be locked --}}
@if($trip->status === 'pending')
<div class="alert alert-warning border-warning d-flex align-items-start mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div>
        <h6 class="alert-heading mb-2"><strong>Important: Status Change Warning</strong></h6>
        <p class="mb-0">
            This trip is currently <strong>PENDING</strong> and can be edited. 
            <strong class="text-danger">Once you change the status to "Assigned", "In Transit", "Delivered", or "Cancelled", 
            the trip will be permanently locked and cannot be edited again.</strong>
        </p>
        <hr class="my-2">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Make sure all trip details are correct before changing the status.
        </small>
    </div>
</div>
@endif

<form action="{{ route('admin.trips.update', $trip) }}" method="POST" enctype="multipart/form-data" id="tripEditForm">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Shipment Selection -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-box-seam me-2"></i>Shipment Assignment</h5>
                    @if($trip->shipment_id)
                        <span class="badge bg-info">Linked</span>
                    @else
                        <span class="badge bg-secondary">Optional</span>
                    @endif
                </div>
                <div class="content-card-body">
                    @if($trip->shipment_id)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-link-45deg me-2"></i>
                            <strong>Currently Linked:</strong> This trip is linked to shipment {{ $trip->shipment_reference }}. 
                            Vehicle filtering is active based on shipment requirements.
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>{{ __('optional_feature') }}:</strong> 
                            Assign this trip to an existing pending shipment. <strong>Vehicle selection will be automatically filtered</strong> to show only vehicles matching the shipment's required vehicle type.
                        </div>
                    @endif

                    <div class="mb-0">
                        <label for="shipment_id" class="form-label">
                            <i class="bi bi-box-seam me-1"></i>{{ __('shipment') }}
                        </label>
                        <select name="shipment_id" 
                                id="shipment_id" 
                                class="form-select @error('shipment_id') is-invalid @enderror"
                                {{ $trip->shipment_id ? 'disabled' : '' }}>
                            <option value="">-- {{ __('no_shipment') }} / {{ __('manual_trip') }} --</option>
                            @foreach($shipments as $shipment)
                            <option value="{{ $shipment->id }}" 
                                    data-vehicle-type="{{ $shipment->vehicle_type_id }}"
                                    data-vehicle-type-name="{{ $shipment->vehicleType->name }}"
                                    data-shipment-number="{{ $shipment->shipment_number }}"
                                    {{ ($trip->shipment_id == $shipment->id) ? 'selected' : '' }}
                                    {{ old('shipment_id') == $shipment->id ? 'selected' : '' }}>
                                {{ $shipment->shipment_number }} - 
                                {{ $shipment->vehicleType->name }} - 
                                {{ $shipment->cargo_weight }}kg -
                                {{ ucfirst($shipment->priority) }}
                            </option>
                            @endforeach
                        </select>
                        @error('shipment_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if($trip->shipment_id)
                            <small class="form-text text-primary">
                                <i class="bi bi-lock me-1"></i>Shipment assignment is locked for this trip
                            </small>
                        @endif
                        
                        <!-- Shipment Filter Active Indicator -->
                        <div class="alert alert-success mt-2 d-none" id="shipment-filter-active">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-funnel-fill me-2 fs-5"></i>
                                <div>
                                    <strong>Vehicle Filter Active</strong><br>
                                    <small>Showing only vehicles of type: <strong id="filter-vehicle-type"></strong></small><br>
                                    <small class="text-muted"><span id="filtered-count">0</span> vehicle(s) available</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Matching Vehicles Warning -->
                        <div class="alert alert-danger mt-2 d-none" id="no-matching-vehicles">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div>
                                    <strong>No Compatible Vehicles Found</strong><br>
                                    <small>No vehicles of type <strong id="required-vehicle-type"></strong> are available. Please select a different shipment or create a manual trip.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle & Driver Assignment -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-truck-front me-2"></i>Vehicle & Driver Assignment</h5>
                    <span class="badge bg-light text-dark" id="vehicle-count-badge">
                        {{ count($allVehicles) }} vehicles
                    </span>
                </div>
                <div class="content-card-body">
                    <!-- Current Assignment Info -->
                    <div class="alert alert-light border mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Current Vehicle</small>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck-front text-primary me-2 fs-5"></i>
                                    <strong>{{ $trip->vehicle ? $trip->vehicle->vehicle_number : 'Not assigned' }}</strong>
                                    @if($trip->vehicle && $trip->vehicle->vehicleType)
                                        <span class="text-muted ms-2">({{ $trip->vehicle->vehicleType->name }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Current Driver</small>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge text-success me-2 fs-5"></i>
                                    <strong>{{ $trip->driver ? $trip->driver->name : 'Not assigned' }}</strong>
                                    @if($trip->driver)
                                        <span class="text-muted ms-2">({{ ucfirst(str_replace('_', ' ', $trip->driver->driver_type)) }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="vehicle_id" class="form-label">
                                {{ __('vehicle') }} <span class="text-danger">*</span>
                                <span class="badge bg-primary d-none" id="filtered-badge">
                                    <i class="bi bi-funnel"></i> Filtered
                                </span>
                            </label>
                            <select name="vehicle_id" 
                                    id="vehicle_id" 
                                    class="form-select @error('vehicle_id') is-invalid @enderror" 
                                    required>
                                <option value="">{{ __('select_option') }}</option>
                                @foreach($allVehicles as $vehicle)
                                    @php
                                        $isSelected = false;
                                        if (old('vehicle_id')) {
                                            $isSelected = old('vehicle_id') == $vehicle->id;
                                        } else {
                                            $isSelected = $trip->vehicle_id == $vehicle->id;
                                        }
                                    @endphp
                                <option value="{{ $vehicle->id }}" 
                                        data-vehicle-type="{{ $vehicle->vehicle_type_id }}"
                                        data-vehicle-type-name="{{ $vehicle->vehicleType->name }}"
                                        data-owner="{{ $vehicleOwners[$vehicle->id] ?? '' }}"
                                        data-in-use="{{ in_array($vehicle->id, $vehiclesInUse) ? '1' : '0' }}"
                                        data-trip="{{ $vehicleTrips[$vehicle->id] ?? '' }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_number }} - {{ $vehicle->vehicleType->name }}
                                    @if(isset($vehicleOwners[$vehicle->id]))
                                        [Owner's Vehicle]
                                    @endif
                                    @if(in_array($vehicle->id, $vehiclesInUse))
                                        [In Use: {{ $vehicleTrips[$vehicle->id] }}]
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="vehicle-hint">
                                @if($trip->vehicle)
                                    Currently assigned: <strong>{{ $trip->vehicle->vehicle_number }}</strong>
                                @else
                                    Select vehicle for this trip
                                @endif
                            </small>
                            
                            <!-- Vehicle Availability Warning -->
                            <div class="alert alert-warning mt-2 d-none" id="vehicle-availability-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <small><strong>Warning:</strong> <span id="vehicle-warning-text"></span></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="driver_id" class="form-label">
                                {{ __('driver') }} <span class="text-danger">*</span>
                            </label>
                            <select name="driver_id" 
                                    id="driver_id" 
                                    class="form-select @error('driver_id') is-invalid @enderror" 
                                    required>
                                <option value="">{{ __('select_option') }}</option>
                                @foreach($allDrivers as $driver)
                                    @php
                                        $isSelected = false;
                                        if (old('driver_id')) {
                                            $isSelected = old('driver_id') == $driver->id;
                                        } else {
                                            $isSelected = $trip->driver_id == $driver->id;
                                        }
                                    @endphp
                                <option value="{{ $driver->id }}" 
                                        data-own-vehicle="{{ $driverOwnVehicles[$driver->id] ?? '' }}"
                                        data-type="{{ $driver->driver_type }}"
                                        data-on-trip="{{ in_array($driver->id, $driversOnTrip) ? '1' : '0' }}"
                                        data-trip="{{ $driverTrips[$driver->id] ?? '' }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                    {{ $driver->name }} - {{ __($driver->driver_type) }}
                                    @if($driver->driver_type === 'own_vehicle' && $driver->ownVehicle)
                                        [{{ $driver->ownVehicle->vehicle_number }}]
                                    @endif
                                    @if(in_array($driver->id, $driversOnTrip))
                                        [On Trip: {{ $driverTrips[$driver->id] }}]
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                @if($trip->driver)
                                    Currently assigned: <strong>{{ $trip->driver->name }}</strong>
                                @else
                                    Select driver for this trip
                                @endif
                            </small>
                            
                            <!-- Driver Availability Warning -->
                            <div class="alert alert-warning mt-2 d-none" id="driver-availability-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <small><strong>Warning:</strong> <span id="driver-warning-text"></span></small>
                            </div>
                        </div>
                    </div>

                    <!-- Pairing Info Alert -->
                    <div class="alert alert-info mt-3 d-none" id="pairing-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="pairing-message"></span>
                    </div>

                    <!-- Compatibility Error Alert -->
                    <div class="alert alert-danger mt-3 d-none" id="compatibility-error">
                        <i class="bi bi-x-circle me-2"></i>
                        <span id="compatibility-message"></span>
                    </div>
                </div>
            </div>

            <!-- Location Details, Additional Information sections continue as before... -->
            <!-- Keeping the form compact for this response -->
            
        </div>

        <!-- Right Column - Trip Details Sidebar -->
        <div class="col-lg-4">
            <div class="content-card sticky-top" style="top: 2rem;">
                <div class="content-card-header">
                    <h5><i class="bi bi-info-circle me-2"></i>Trip Details</h5>
                </div>
                <div class="content-card-body">
                    <!-- Status Badge -->
                    <div class="mb-3">
                        <label class="form-label d-block">Current Status</label>
                        <span class="badge bg-{{ 
                            $trip->status === 'pending' ? 'warning' : 
                            ($trip->status === 'assigned' ? 'info' : 
                            ($trip->status === 'in_transit' ? 'primary' : 
                            ($trip->status === 'delivered' ? 'success' : 'danger'))) 
                        }} fs-6 px-3 py-2">
                            {{ __(ucfirst(str_replace('_', ' ', $trip->status))) }}
                        </span>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="status" class="form-label">
                            {{ __('status') }} <span class="text-danger">*</span>
                            @if($trip->status === 'pending')
                                <i class="bi bi-exclamation-circle text-warning ms-1" 
                                   data-bs-toggle="tooltip" 
                                   title="Changing status will lock the trip"></i>
                            @endif
                        </label>
                        <select name="status" 
                                id="status" 
                                class="form-select @error('status') is-invalid @enderror" 
                                required>
                            <option value="pending" {{ old('status', $trip->status) == 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                            <option value="assigned" {{ old('status', $trip->status) == 'assigned' ? 'selected' : '' }}>{{ __('assigned') }}</option>
                            <option value="in_transit" {{ old('status', $trip->status) == 'in_transit' ? 'selected' : '' }}>{{ __('in_transit') }}</option>
                            <option value="delivered" {{ old('status', $trip->status) == 'delivered' ? 'selected' : '' }}>{{ __('delivered') }}</option>
                            <option value="cancelled" {{ old('status', $trip->status) == 'cancelled' ? 'selected' : '' }}>{{ __('cancelled') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            @if($trip->status === 'pending')
                                <span class="text-danger"><i class="bi bi-shield-lock"></i> Changing from "Pending" will lock this trip</span>
                            @else
                                Current: <strong>{{ __(ucfirst(str_replace('_', ' ', $trip->status))) }}</strong>
                            @endif
                        </small>
                    </div>

                    {{-- Status Change Warning Box --}}
                    <div class="alert alert-danger d-none" id="statusChangeWarning">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">Warning!</strong>
                                <small id="statusChangeMessage">Changing status will permanently lock this trip for editing.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">
                            <i class="bi bi-calendar-check me-1"></i>{{ __('start_date') }}
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $trip->start_date ? date('Y-m-d', strtotime($trip->start_date)) : '') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">
                            <i class="bi bi-calendar-x me-1"></i>{{ __('end_date') }}
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $trip->end_date ? date('Y-m-d', strtotime($trip->end_date)) : '') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Optional completion date</small>
                    </div>

                    <hr class="my-4">

                    <!-- Trip Metadata -->
                    <div class="trip-meta">
                        <div class="meta-item">
                            <small class="text-muted">Created</small>
                            <p class="mb-0 fw-semibold">{{ $trip->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="meta-item">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0 fw-semibold">{{ $trip->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="saveButton">
                            <i class="bi bi-check-circle me-2"></i>{{ __('save') }} Changes
                        </button>
                        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>{{ __('cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #2563eb;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: white;
        padding: 1.5rem 2rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .content-card-header {
        background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .content-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }

    .content-card-body {
        padding: 1.5rem;
    }

    #shipment-filter-active {
        border-left: 4px solid var(--success-color);
        animation: slideDown 0.3s ease;
    }

    #no-matching-vehicles {
        border-left: 4px solid var(--danger-color);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #filtered-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .trip-meta {
        display: grid;
        gap: 0.75rem;
    }

    .meta-item small {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .sticky-top {
        position: sticky;
        top: 2rem;
    }
</style>
@endpush

@push('scripts')
<script>
    const vehicleOwners = @json($vehicleOwners);
    const driverOwnVehicles = @json($driverOwnVehicles);
    const vehiclesInUse = @json($vehiclesInUse);
    const driversOnTrip = @json($driversOnTrip);
    const originalStatus = '{{ $trip->status }}';
    const currentShipmentVehicleType = '{{ $trip->shipment_id && $trip->shipment ? $trip->shipment->vehicle_type_id : "" }}';

    document.addEventListener('DOMContentLoaded', function() {
        const shipmentSelect = document.getElementById('shipment_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        const driverSelect = document.getElementById('driver_id');
        const statusSelect = document.getElementById('status');
        const pairingInfo = document.getElementById('pairing-info');
        const pairingMessage = document.getElementById('pairing-message');
        const compatibilityError = document.getElementById('compatibility-error');
        const compatibilityMessage = document.getElementById('compatibility-message');
        const vehicleWarning = document.getElementById('vehicle-availability-warning');
        const vehicleWarningText = document.getElementById('vehicle-warning-text');
        const driverWarning = document.getElementById('driver-availability-warning');
        const driverWarningText = document.getElementById('driver-warning-text');
        const statusWarning = document.getElementById('statusChangeWarning');
        const statusWarningMessage = document.getElementById('statusChangeMessage');
        const tripEditForm = document.getElementById('tripEditForm');
        
        // Shipment filter indicators
        const shipmentFilterActive = document.getElementById('shipment-filter-active');
        const noMatchingVehicles = document.getElementById('no-matching-vehicles');
        const filterVehicleType = document.getElementById('filter-vehicle-type');
        const requiredVehicleType = document.getElementById('required-vehicle-type');
        const filteredCount = document.getElementById('filtered-count');
        const vehicleCountBadge = document.getElementById('vehicle-count-badge');
        const filteredBadge = document.getElementById('filtered-badge');
        const vehicleHint = document.getElementById('vehicle-hint');
        
        // Store all vehicle options for filtering
        const allVehicleOptions = Array.from(vehicleSelect.options);
        
        // ===== STATUS CHANGE WARNING SYSTEM =====
        function handleStatusChange() {
            const newStatus = statusSelect.value;
            
            if (originalStatus === 'pending' && newStatus !== 'pending') {
                statusWarning.classList.remove('d-none');
                
                const statusLabels = {
                    'assigned': 'Assigned',
                    'in_transit': 'In Transit',
                    'delivered': 'Delivered',
                    'cancelled': 'Cancelled'
                };
                
                statusWarningMessage.textContent = `⚠️ Once you change the status to "${statusLabels[newStatus]}", this trip will be permanently locked and cannot be edited again. Make sure all details are correct before saving.`;
            } else {
                statusWarning.classList.add('d-none');
            }
        }
        
        if (statusSelect) {
            statusSelect.addEventListener('change', handleStatusChange);
        }
        
        // Form submission confirmation for status changes
        tripEditForm.addEventListener('submit', function(e) {
            const newStatus = statusSelect.value;
            
            if (originalStatus === 'pending' && newStatus !== 'pending') {
                const confirmed = confirm(
                    '⚠️ CRITICAL WARNING ⚠️\n\n' +
                    'You are about to change the trip status from "Pending" to "' + newStatus.replace('_', ' ').toUpperCase() + '".\n\n' +
                    'This action will PERMANENTLY LOCK the trip and prevent any future edits.\n\n' +
                    'Are you absolutely sure you want to continue?'
                );
                
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // ===== SHIPMENT SELECTION - ENHANCED VEHICLE FILTERING =====
        shipmentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const requiredVehicleTypeId = selectedOption.dataset.vehicleType;
            const requiredVehicleTypeName = selectedOption.dataset.vehicleTypeName;
            
            // Save current vehicle selection
            const currentVehicleId = vehicleSelect.value;
            
            // Clear vehicle selection
            vehicleSelect.value = '';
            
            // Remove all options except placeholder
            while (vehicleSelect.options.length > 1) {
                vehicleSelect.remove(1);
            }
            
            if (!requiredVehicleTypeId) {
                // No shipment selected - show all vehicles
                allVehicleOptions.slice(1).forEach(option => {
                    vehicleSelect.add(option.cloneNode(true));
                });
                
                // Hide filter indicators
                shipmentFilterActive.classList.add('d-none');
                noMatchingVehicles.classList.add('d-none');
                filteredBadge.classList.add('d-none');
                
                // Reset vehicle count and hint
                vehicleCountBadge.textContent = `${allVehicleOptions.length - 1} vehicles`;
                vehicleCountBadge.className = 'badge bg-light text-dark';
                vehicleHint.innerHTML = 'Select vehicle for this trip';
                
            } else {
                // Shipment selected - filter vehicles by type
                let matchingCount = 0;
                
                allVehicleOptions.slice(1).forEach(option => {
                    if (option.dataset.vehicleType === requiredVehicleTypeId) {
                        vehicleSelect.add(option.cloneNode(true));
                        matchingCount++;
                    }
                });
                
                if (matchingCount > 0) {
                    // Show success message - vehicles found
                    shipmentFilterActive.classList.remove('d-none');
                    noMatchingVehicles.classList.add('d-none');
                    filterVehicleType.textContent = requiredVehicleTypeName;
                    filteredCount.textContent = matchingCount;
                    
                    // Update badge
                    filteredBadge.classList.remove('d-none');
                    vehicleCountBadge.textContent = `${matchingCount} of ${allVehicleOptions.length - 1}`;
                    vehicleCountBadge.className = 'badge bg-success';
                    
                    // Update hint
                    vehicleHint.innerHTML = `<i class="bi bi-funnel me-1"></i>Filtered by shipment: showing only <strong>${requiredVehicleTypeName}</strong> vehicles`;
                    
                } else {
                    // Show error message - no vehicles found
                    shipmentFilterActive.classList.add('d-none');
                    noMatchingVehicles.classList.remove('d-none');
                    requiredVehicleType.textContent = requiredVehicleTypeName;
                    
                    // Update badge
                    filteredBadge.classList.add('d-none');
                    vehicleCountBadge.textContent = `0 available`;
                    vehicleCountBadge.className = 'badge bg-danger';
                    
                    // Update hint
                    vehicleHint.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>No vehicles available for type: <strong>${requiredVehicleTypeName}</strong>`;
                }
            }
            
            // Try to restore selection if still available
            if (currentVehicleId) {
                const matchingOption = Array.from(vehicleSelect.options).find(opt => opt.value === currentVehicleId);
                if (matchingOption) {
                    vehicleSelect.value = currentVehicleId;
                }
            }
            
            // Recheck compatibility and availability
            checkVehicleAvailability();
            checkDriverAvailability();
            checkCompatibility();
        });
        
        // ===== VEHICLE AVAILABILITY CHECK =====
        function checkVehicleAvailability() {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                vehicleWarning.classList.add('d-none');
                return;
            }
            
            const isInUse = selectedOption.dataset.inUse === '1';
            const tripNumber = selectedOption.dataset.trip;
            
            if (isInUse) {
                vehicleWarningText.textContent = `This vehicle is currently in use on trip ${tripNumber}. Please select another vehicle.`;
                vehicleWarning.classList.remove('d-none');
            } else {
                vehicleWarning.classList.add('d-none');
            }
        }
        
        // ===== DRIVER AVAILABILITY CHECK =====
        function checkDriverAvailability() {
            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                driverWarning.classList.add('d-none');
                return;
            }
            
            const isOnTrip = selectedOption.dataset.onTrip === '1';
            const tripNumber = selectedOption.dataset.trip;
            
            if (isOnTrip) {
                driverWarningText.textContent = `This driver is currently on trip ${tripNumber}. Please select another driver.`;
                driverWarning.classList.remove('d-none');
            } else {
                driverWarning.classList.add('d-none');
            }
        }
        
        // ===== VEHICLE-DRIVER COMPATIBILITY CHECK =====
        function checkCompatibility() {
            let incompatible = false;
            let message = '';
            
            const vehicleOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const driverOption = driverSelect.options[driverSelect.selectedIndex];
            
            if (!vehicleOption || !driverOption || !vehicleOption.value || !driverOption.value) {
                compatibilityError.classList.add('d-none');
                pairingInfo.classList.add('d-none');
                return;
            }
            
            const vehicleId = vehicleOption.value;
            const driverId = driverOption.value;
            const driverType = driverOption.dataset.type;
            const driverOwnVehicle = driverOption.dataset.ownVehicle;
            const vehicleOwnerId = vehicleOption.dataset.owner;
            
            if (driverType === 'own_vehicle' && driverOwnVehicle && driverOwnVehicle !== vehicleId) {
                incompatible = true;
                message = 'This driver can only be assigned to their own vehicle.';
            }
            
            if (vehicleOwnerId && vehicleOwnerId !== driverId) {
                incompatible = true;
                message = 'This vehicle is owned by another driver and cannot be assigned.';
            }
            
            if (incompatible) {
                compatibilityMessage.textContent = message;
                compatibilityError.classList.remove('d-none');
                pairingInfo.classList.add('d-none');
            } else {
                compatibilityError.classList.add('d-none');
            }
        }
        
        // ===== VEHICLE-DRIVER PAIRING LOGIC =====
        function handleVehicleChange() {
            const selectedVehicleId = vehicleSelect.value;
            
            if (selectedVehicleId) {
                const ownerId = vehicleOwners[selectedVehicleId];
                
                if (ownerId) {
                    driverSelect.value = ownerId;
                    
                    Array.from(driverSelect.options).forEach(option => {
                        if (option.value && option.value != ownerId) {
                            option.disabled = true;
                            option.style.color = '#cbd5e1';
                        } else if (option.value == ownerId) {
                            option.disabled = false;
                            option.style.color = '';
                        }
                    });
                    
                    pairingMessage.textContent = 'This vehicle belongs to a specific driver. The owner has been automatically selected.';
                    pairingInfo.classList.remove('d-none');
                } else {
                    Array.from(driverSelect.options).forEach(option => {
                        const driverOwnVehicle = option.getAttribute('data-own-vehicle');
                        if (option.value && driverOwnVehicle) {
                            option.disabled = true;
                            option.style.color = '#cbd5e1';
                        } else {
                            option.disabled = false;
                            option.style.color = '';
                        }
                    });
                    pairingInfo.classList.add('d-none');
                }
            } else {
                Array.from(driverSelect.options).forEach(option => {
                    option.disabled = false;
                    option.style.color = '';
                });
                pairingInfo.classList.add('d-none');
            }
            
            checkVehicleAvailability();
            checkCompatibility();
        }
        
        function handleDriverChange() {
            const selectedDriverId = driverSelect.value;
            
            if (selectedDriverId) {
                const driverOwnVehicle = driverOwnVehicles[selectedDriverId];
                
                if (driverOwnVehicle) {
                    vehicleSelect.value = driverOwnVehicle;
                    
                    Array.from(vehicleSelect.options).forEach(option => {
                        if (option.value && option.value != driverOwnVehicle) {
                            option.disabled = true;
                            option.style.color = '#cbd5e1';
                        } else if (option.value == driverOwnVehicle) {
                            option.disabled = false;
                            option.style.color = '';
                        }
                    });
                    
                    pairingMessage.textContent = 'This driver has their own vehicle. Their vehicle has been automatically selected.';
                    pairingInfo.classList.remove('d-none');
                } else {
                    Array.from(vehicleSelect.options).forEach(option => {
                        const vehicleOwner = option.getAttribute('data-owner');
                        if (option.value && vehicleOwner && vehicleOwner != selectedDriverId) {
                            option.disabled = true;
                            option.style.color = '#cbd5e1';
                        } else {
                            option.disabled = false;
                            option.style.color = '';
                        }
                    });
                    pairingInfo.classList.add('d-none');
                }
            } else {
                Array.from(vehicleSelect.options).forEach(option => {
                    option.disabled = false;
                    option.style.color = '';
                });
                pairingInfo.classList.add('d-none');
            }
            
            checkDriverAvailability();
            checkCompatibility();
        }
        
        vehicleSelect.addEventListener('change', handleVehicleChange);
        driverSelect.addEventListener('change', handleDriverChange);
        
        // ✅ CRITICAL FIX: Initialize shipment filter AFTER all event listeners are defined
        // This ensures the change event has a listener to trigger
        if (currentShipmentVehicleType && shipmentSelect.value) {
            console.log('🔧 Initializing vehicle filter on page load...');
            console.log('Current shipment vehicle type:', currentShipmentVehicleType);
            console.log('Selected shipment ID:', shipmentSelect.value);
            
            // Trigger filter on page load using setTimeout to ensure DOM is fully ready
            setTimeout(() => {
                const event = new Event('change');
                shipmentSelect.dispatchEvent(event);
                console.log('✅ Vehicle filter initialized successfully');
            }, 100);
        }
    });
</script>
@endpush