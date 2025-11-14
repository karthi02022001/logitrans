@extends('layouts.app')

@section('title', __('create_trip') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.trips.index') }}">{{ __('trips') }}</a></li>
                <li class="breadcrumb-item active">{{ __('create_trip') }}</li>
            </ol>
        </nav>
        <h2><i class="bi bi-plus-circle"></i> {{ __('create_trip') }}</h2>
    </div>
    <div>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
        </a>
    </div>
</div>

<form action="{{ route('admin.trips.store') }}" method="POST" enctype="multipart/form-data" id="tripForm">
    @csrf
    
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Shipment Selection -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-box-seam me-2"></i>Shipment Assignment</h5>
                    <span class="badge bg-secondary">Optional</span>
                </div>
                <div class="content-card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>{{ __('optional_feature') }}:</strong> 
                        Assign this trip to an existing pending shipment. Vehicle type will be automatically filtered to match shipment requirements.
                    </div>

                    <div class="mb-0">
                        <label for="shipment_id" class="form-label">
                            <i class="bi bi-box-seam me-1"></i>{{ __('shipment') }}
                        </label>
                        <select name="shipment_id" 
                                id="shipment_id" 
                                class="form-select @error('shipment_id') is-invalid @enderror">
                            <option value="">-- {{ __('no_shipment') }} / {{ __('manual_trip') }} --</option>
                            @foreach($shipments as $shipment)
                            <option value="{{ $shipment->id }}" 
                                    data-vehicle-type="{{ $shipment->vehicle_type_id }}"
                                    data-shipment-number="{{ $shipment->shipment_number }}"
                                    {{ old('shipment_id') == $shipment->id ? 'selected' : '' }}>
                                {{ $shipment->shipment_number }} - 
                                {{ $shipment->vehicleType->name }} - 
                                {{ $shipment->cargo_weight }}kg -
                                <span class="badge-priority-{{ $shipment->priority }}">{{ ucfirst($shipment->priority) }}</span>
                            </option>
                            @endforeach
                        </select>
                        @error('shipment_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-lightbulb me-1"></i>
                            Selecting a shipment will automatically filter compatible vehicles by type
                        </small>
                    </div>
                </div>
            </div>

            <!-- Vehicle & Driver Assignment -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-truck-front me-2"></i>Vehicle & Driver Assignment</h5>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="vehicle_id" class="form-label">
                                {{ __('vehicle') }} <span class="text-danger">*</span>
                            </label>
                            <select name="vehicle_id" 
                                    id="vehicle_id" 
                                    class="form-select @error('vehicle_id') is-invalid @enderror" 
                                    required>
                                <option value="">{{ __('select_option') }}</option>
                                @foreach($allVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" 
                                        data-vehicle-type="{{ $vehicle->vehicle_type_id }}"
                                        data-owner="{{ $vehicleOwners[$vehicle->id] ?? '' }}"
                                        data-in-use="{{ in_array($vehicle->id, $vehiclesInUse) ? '1' : '0' }}"
                                        data-trip="{{ $vehicleTrips[$vehicle->id] ?? '' }}"
                                        {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_number }} - {{ $vehicle->vehicleType->name }}
                                    @if(isset($vehicleOwners[$vehicle->id]))
                                        <span class="text-info">[Owner's Vehicle]</span>
                                    @endif
                                    @if(in_array($vehicle->id, $vehiclesInUse))
                                        <span class="text-danger">[In Use: {{ $vehicleTrips[$vehicle->id] }}]</span>
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select vehicle for this trip</small>
                            
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
                                <option value="{{ $driver->id }}" 
                                        data-own-vehicle="{{ $driverOwnVehicles[$driver->id] ?? '' }}"
                                        data-type="{{ $driver->driver_type }}"
                                        data-on-trip="{{ in_array($driver->id, $driversOnTrip) ? '1' : '0' }}"
                                        data-trip="{{ $driverTrips[$driver->id] ?? '' }}"
                                        {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} - {{ __($driver->driver_type) }}
                                    @if($driver->driver_type === 'own_vehicle' && $driver->ownVehicle)
                                        <span class="text-info">[{{ $driver->ownVehicle->vehicle_number }}]</span>
                                    @endif
                                    @if(in_array($driver->id, $driversOnTrip))
                                        <span class="text-danger">[On Trip: {{ $driverTrips[$driver->id] }}]</span>
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select driver for this trip</small>
                            
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
                        <strong>Incompatible Selection:</strong> <span id="compatibility-message"></span>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-geo-alt me-2"></i>Location Details</h5>
                </div>
                <div class="content-card-body">
                    <!-- Location Type -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Location Type <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="location-type-card">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="has_multiple_locations" 
                                           id="single_location" 
                                           value="0" 
                                           {{ old('has_multiple_locations', '0') == '0' ? 'checked' : '' }}>
                                    <label class="location-type-label" for="single_location">
                                        <div class="location-icon-wrapper">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </div>
                                        <div>
                                            <div class="location-title">Single Location</div>
                                            <small class="text-muted">Pickup & drop-off</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="location-type-card">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="has_multiple_locations" 
                                           id="multiple_locations" 
                                           value="1" 
                                           {{ old('has_multiple_locations') == '1' ? 'checked' : '' }}>
                                    <label class="location-type-label" for="multiple_locations">
                                        <div class="location-icon-wrapper">
                                            <i class="bi bi-pin-map"></i>
                                        </div>
                                        <div>
                                            <div class="location-title">Multiple Locations</div>
                                            <small class="text-muted">Pickup only (multi-stop route)</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Locations -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pickup_location" class="form-label">
                                <i class="bi bi-pin-map me-1"></i>{{ __('pickup') }} Location <span class="text-danger">*</span>
                            </label>
                            <textarea name="pickup_location" 
                                      id="pickup_location" 
                                      rows="4" 
                                      class="form-control @error('pickup_location') is-invalid @enderror" 
                                      placeholder="Enter pickup address..."
                                      required>{{ old('pickup_location') }}</textarea>
                            @error('pickup_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="drop_location_container">
                            <label for="drop_location" class="form-label">
                                <i class="bi bi-geo-alt-fill me-1"></i>{{ __('drop') }} Location
                                <span class="text-danger" id="drop_required">*</span>
                            </label>
                            <textarea name="drop_location" 
                                      id="drop_location" 
                                      rows="4" 
                                      class="form-control @error('drop_location') is-invalid @enderror"
                                      placeholder="Enter drop-off address..."
                                      required>{{ old('drop_location') }}</textarea>
                            @error('drop_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="drop_hint">Required for single location trips</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5><i class="bi bi-file-text me-2"></i>Additional Information</h5>
                </div>
                <div class="content-card-body">
                    <div class="mb-3">
                        <label for="shipment_reference" class="form-label">
                            <i class="bi bi-tag me-1"></i>{{ __('shipment_reference') }}
                        </label>
                        <input type="text" 
                               name="shipment_reference" 
                               id="shipment_reference" 
                               class="form-control @error('shipment_reference') is-invalid @enderror"
                               value="{{ old('shipment_reference') }}"
                               placeholder="e.g., SH-2024-001">
                        @error('shipment_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="shipment_ref_hint">Optional shipment or order reference</small>
                    </div>

                    <div>
                        <label for="trip_instructions" class="form-label">
                            {{ __('trip_instructions') }}
                        </label>
                        <textarea name="trip_instructions" 
                                  id="trip_instructions" 
                                  rows="4" 
                                  class="form-control @error('trip_instructions') is-invalid @enderror"
                                  placeholder="Enter any special instructions for this trip...">{{ old('trip_instructions') }}</textarea>
                        @error('trip_instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- File Attachments -->
           {{--  <div class="content-card">
                <div class="content-card-header">
                    <h5><i class="bi bi-paperclip me-2"></i>File Attachments</h5>
                    <span class="badge bg-secondary">Optional</span>
                </div>
                <div class="content-card-body">
                    <!-- Upload Area -->
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-area-content">
                            <i class="bi bi-cloud-upload upload-icon"></i>
                            <h5 class="mb-2">Drag & drop files here</h5>
                            <p class="text-muted mb-3">or</p>
                            <label for="file_input" class="btn btn-outline-primary">
                                <i class="bi bi-folder2-open me-2"></i>Browse Files
                            </label>
                            <input type="file" 
                                   id="file_input" 
                                   name="files[]"
                                   multiple 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" 
                                   class="d-none">
                            <small class="d-block mt-3 text-muted">
                                Supported: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 5MB each)
                            </small>
                        </div>
                    </div>

                    <!-- File List -->
                    <div id="filePreviewList" class="file-preview-list mt-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Selected Files (<span id="fileCount">0</span>)</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllFiles">
                                <i class="bi bi-trash me-1"></i>Clear All
                            </button>
                        </div>
                        <div id="fileList" class="row g-2"></div>
                    </div>
                </div>
            </div> --}}
        </div>

        <!-- Right Column - Trip Details Sidebar -->
        <div class="col-lg-4">
            <div class="content-card sticky-top" style="top: 2rem;">
                <div class="content-card-header">
                    <h5><i class="bi bi-info-circle me-2"></i>Trip Details</h5>
                </div>
                <div class="content-card-body">
                   {{--  <div class="mb-3">
                        <label for="status" class="form-label">
                            {{ __('status') }} <span class="text-danger">*</span>
                        </label>
                        <select name="status" 
                                id="status" 
                                class="form-select @error('status') is-invalid @enderror" 
                                required>
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                            <option value="assigned" {{ old('status') == 'assigned' ? 'selected' : '' }}>{{ __('assigned') }}</option>
                            <option value="in_transit" {{ old('status') == 'in_transit' ? 'selected' : '' }}>{{ __('in_transit') }}</option>
                            <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>{{ __('delivered') }}</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('cancelled') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <div class="mb-3">
                        <label for="start_date" class="form-label">
                            <i class="bi bi-calendar-check me-1"></i>{{ __('start_date') }}
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}">
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
                               value="{{ old('end_date') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Optional estimated completion date</small>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>{{ __('create_trip') }}
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
        --secondary-color: #64748b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
    }

    /* Page Header */
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

    .page-header h2 {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        font-size: 1.75rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb-item a:hover {
        color: #1d4ed8;
    }

    .breadcrumb-item.active {
        color: var(--secondary-color);
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .content-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .content-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .content-card-header h5 {
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        font-size: 1rem;
        display: flex;
        align-items: center;
    }

    .content-card-body {
        padding: 1.5rem;
    }

    /* Form Styles */
    .form-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }

    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .form-text {
        font-size: 0.8125rem;
        color: var(--secondary-color);
        margin-top: 0.25rem;
        display: block;
    }

    textarea.form-control {
        resize: vertical;
    }

    /* Location Type Cards */
    .location-type-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        background: white;
        height: 100%;
    }

    .location-type-card:hover {
        border-color: var(--primary-color);
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    }

    .location-type-card input[type="radio"] {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: var(--primary-color);
    }

    .location-type-card input[type="radio"]:checked ~ .location-type-label {
        color: var(--primary-color);
    }

    .location-type-card input[type="radio"]:checked ~ .location-type-label .location-icon-wrapper {
        background: var(--primary-color);
        color: white;
    }

    .location-type-label {
        cursor: pointer;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-right: 2rem;
    }

    .location-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #dbeafe;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .location-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    /* Upload Area */
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 3rem 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-area:hover {
        border-color: var(--primary-color);
        background: #eff6ff;
    }
    
    .upload-area.drag-over {
        border-color: var(--primary-color);
        background: #dbeafe;
        border-style: solid;
    }
    
    .upload-icon {
        font-size: 3rem;
        color: var(--primary-color);
        opacity: 0.6;
        margin-bottom: 1rem;
    }
    
    .upload-area h5 {
        color: #334155;
        font-weight: 600;
        margin: 0;
    }
    
    /* File Preview */
    .file-preview-list {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .file-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }
    
    .file-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }
    
    .file-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .file-icon.pdf { background: #fee2e2; color: #dc2626; }
    .file-icon.doc { background: #dbeafe; color: #2563eb; }
    .file-icon.xls { background: #d1fae5; color: #059669; }
    .file-icon.img { background: #fce7f3; color: #db2777; }
    .file-icon.default { background: #f3f4f6; color: #6b7280; }
    
    .file-info {
        flex: 1;
        min-width: 0;
    }
    
    .file-name {
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.9375rem;
    }
    
    .file-size {
        color: #64748b;
        font-size: 0.8125rem;
    }
    
    .file-remove {
        padding: 0.375rem 0.75rem;
        border: none;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }
    
    .file-remove:hover {
        background: #fecaca;
    }

    /* Buttons */
    .btn {
        border-radius: 8px;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s ease;
        font-size: 0.9375rem;
    }

    .btn-lg {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }

    .btn-primary {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover:not(:disabled) {
        background: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:disabled {
        background: #cbd5e1;
        border-color: #cbd5e1;
        cursor: not-allowed;
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .btn-outline-secondary {
        color: var(--secondary-color);
        border-color: #cbd5e1;
    }

    .btn-outline-secondary:hover {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
        color: white;
    }

    .btn-outline-danger {
        color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .btn-outline-danger:hover {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }

    /* Alert */
    .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem 1.25rem;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Badge */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Sticky Sidebar */
    @media (min-width: 992px) {
        .sticky-top {
            position: sticky;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .location-type-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const vehicleOwners = @json($vehicleOwners);
    const driverOwnVehicles = @json($driverOwnVehicles);
    const vehiclesInUse = @json($vehiclesInUse);
    const driversOnTrip = @json($driversOnTrip);

    document.addEventListener('DOMContentLoaded', function() {
        const shipmentSelect = document.getElementById('shipment_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        const driverSelect = document.getElementById('driver_id');
        const pairingInfo = document.getElementById('pairing-info');
        const pairingMessage = document.getElementById('pairing-message');
        const compatibilityError = document.getElementById('compatibility-error');
        const compatibilityMessage = document.getElementById('compatibility-message');
        const vehicleWarning = document.getElementById('vehicle-availability-warning');
        const vehicleWarningText = document.getElementById('vehicle-warning-text');
        const driverWarning = document.getElementById('driver-availability-warning');
        const driverWarningText = document.getElementById('driver-warning-text');
        const submitBtn = document.getElementById('submitBtn');
        const shipmentReference = document.getElementById('shipment_reference');
        const shipmentRefHint = document.getElementById('shipment_ref_hint');
        
        // Store all vehicle options for filtering
        const allVehicleOptions = Array.from(vehicleSelect.options);
        
        // ===== SHIPMENT SELECTION - FILTER VEHICLES BY TYPE & AUTO-FILL REFERENCE =====
        shipmentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const requiredVehicleType = selectedOption.dataset.vehicleType;
            
            // Auto-fill shipment reference from selected shipment
            if (this.value) {
                const shipmentNumber = selectedOption.dataset.shipmentNumber;
                shipmentReference.value = shipmentNumber;
                shipmentReference.readOnly = true;
                shipmentReference.style.backgroundColor = '#f8fafc';
                shipmentReference.style.cursor = 'not-allowed';
                shipmentRefHint.textContent = 'Auto-filled from selected shipment (read-only)';
                shipmentRefHint.classList.remove('text-muted');
                shipmentRefHint.classList.add('text-primary');
            } else {
                // Clear and enable manual entry when no shipment selected
                shipmentReference.value = '';
                shipmentReference.readOnly = false;
                shipmentReference.style.backgroundColor = '';
                shipmentReference.style.cursor = '';
                shipmentRefHint.textContent = 'Optional shipment or order reference';
                shipmentRefHint.classList.remove('text-primary');
                shipmentRefHint.classList.add('text-muted');
            }
            
            // Save current vehicle selection
            const currentVehicleId = vehicleSelect.value;
            
            // Clear vehicle selection
            vehicleSelect.value = '';
            
            // Remove all options except placeholder
            while (vehicleSelect.options.length > 1) {
                vehicleSelect.remove(1);
            }
            
            // If no shipment selected, restore all vehicles
            if (!requiredVehicleType) {
                allVehicleOptions.slice(1).forEach(option => {
                    vehicleSelect.add(option.cloneNode(true));
                });
            } else {
                // Filter vehicles by type
                allVehicleOptions.slice(1).forEach(option => {
                    if (option.dataset.vehicleType === requiredVehicleType) {
                        vehicleSelect.add(option.cloneNode(true));
                    }
                });
            }
            
            // Try to restore selection if still available
            if (currentVehicleId) {
                const matchingOption = Array.from(vehicleSelect.options).find(opt => opt.value === currentVehicleId);
                if (matchingOption) {
                    vehicleSelect.value = currentVehicleId;
                }
            }
            
            // Recheck compatibility
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
                submitBtn.disabled = false;
                return;
            }
            
            const vehicleId = vehicleOption.value;
            const driverId = driverOption.value;
            const driverType = driverOption.dataset.type;
            const driverOwnVehicle = driverOption.dataset.ownVehicle;
            const vehicleOwnerId = vehicleOption.dataset.owner;
            
            // Check if driver has own vehicle but selected different vehicle
            if (driverType === 'own_vehicle' && driverOwnVehicle && driverOwnVehicle !== vehicleId) {
                incompatible = true;
                message = 'This driver can only be assigned to their own vehicle.';
            }
            
            // Check if vehicle is owned by another driver
            if (vehicleOwnerId && vehicleOwnerId !== driverId) {
                incompatible = true;
                message = 'This vehicle is owned by another driver and cannot be assigned.';
            }
            
            if (incompatible) {
                compatibilityMessage.textContent = message;
                compatibilityError.classList.remove('d-none');
                pairingInfo.classList.add('d-none');
                submitBtn.disabled = true;
            } else {
                compatibilityError.classList.add('d-none');
                submitBtn.disabled = false;
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
        
        // Location Type Toggle - REVERSED LOGIC
        const singleLocation = document.getElementById('single_location');
        const multipleLocations = document.getElementById('multiple_locations');
        const dropLocation = document.getElementById('drop_location');
        const dropRequired = document.getElementById('drop_required');
        const dropHint = document.getElementById('drop_hint');
        
        function toggleLocationFields() {
            if (multipleLocations.checked) {
                // Multiple locations = pickup only (no drop required)
                dropLocation.required = false;
                dropRequired.classList.add('d-none');
                dropHint.textContent = 'Not required for multiple location trips';
                dropLocation.value = ''; // Clear drop location
            } else {
                // Single location = pickup + drop (drop required)
                dropLocation.required = true;
                dropRequired.classList.remove('d-none');
                dropHint.textContent = 'Required for single location trips';
            }
        }
        
        singleLocation.addEventListener('change', toggleLocationFields);
        multipleLocations.addEventListener('change', toggleLocationFields);
        toggleLocationFields();
        
        // Initialize on page load
        if (vehicleSelect.value) handleVehicleChange();
        if (driverSelect.value) handleDriverChange();

        // ===== FILE UPLOAD =====
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('file_input');
        const filePreviewList = document.getElementById('filePreviewList');
        const fileList = document.getElementById('fileList');
        const fileCount = document.getElementById('fileCount');
        const clearAllBtn = document.getElementById('clearAllFiles');
        
        let selectedFiles = [];
        
        const fileTypeIcons = {
            'pdf': { icon: 'bi-file-earmark-pdf', class: 'pdf' },
            'doc': { icon: 'bi-file-earmark-word', class: 'doc' },
            'docx': { icon: 'bi-file-earmark-word', class: 'doc' },
            'xls': { icon: 'bi-file-earmark-excel', class: 'xls' },
            'xlsx': { icon: 'bi-file-earmark-excel', class: 'xls' },
            'jpg': { icon: 'bi-file-earmark-image', class: 'img' },
            'jpeg': { icon: 'bi-file-earmark-image', class: 'img' },
            'png': { icon: 'bi-file-earmark-image', class: 'img' }
        };
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            return fileTypeIcons[ext] || { icon: 'bi-file-earmark', class: 'default' };
        }
        
        function renderFilePreview() {
            if (selectedFiles.length === 0) {
                filePreviewList.classList.add('d-none');
                return;
            }
            
            filePreviewList.classList.remove('d-none');
            fileCount.textContent = selectedFiles.length;
            fileList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const iconInfo = getFileIcon(file.name);
                const fileItem = document.createElement('div');
                fileItem.className = 'col-md-6';
                fileItem.innerHTML = `
                    <div class="file-item">
                        <div class="file-icon ${iconInfo.class}">
                            <i class="bi ${iconInfo.icon}"></i>
                        </div>
                        <div class="file-info">
                            <span class="file-name" title="${file.name}">${file.name}</span>
                            <span class="file-size">${formatFileSize(file.size)}</span>
                        </div>
                        <button type="button" class="file-remove" onclick="removeFile(${index})">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;
                fileList.appendChild(fileItem);
            });
        }
        
        function handleFiles(files) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            Array.from(files).forEach(file => {
                if (file.size > maxSize) {
                    alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
                    return;
                }
                
                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedFiles.push(file);
                }
            });
            
            renderFilePreview();
            
            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }
        
        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            renderFilePreview();
            
            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        };
        
        clearAllBtn.addEventListener('click', function() {
            selectedFiles = [];
            fileInput.value = '';
            renderFilePreview();
        });
        
        fileInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
        
        uploadArea.addEventListener('click', function(e) {
            if (e.target === uploadArea || e.target.closest('.upload-area-content')) {
                fileInput.click();
            }
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('drag-over');
            handleFiles(e.dataTransfer.files);
        });
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            document.body.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
    });
</script>
@endpush