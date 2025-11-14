@extends('layouts.app')

@section('title', __('add_driver') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('add_driver') }}</h2>
    <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
    </a>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.drivers.store') }}" method="POST">
            @csrf

            <!-- Basic Information -->
            <h6 class="mb-3 text-primary"><i class="bi bi-person-circle"></i> Basic Information</h6>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ __('driver_name') }} *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" 
                           placeholder="Full Name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="mobile" class="form-label">{{ __('mobile') }} *</label>
                    <input type="tel" class="form-control @error('mobile') is-invalid @enderror" 
                           id="mobile" name="mobile" value="{{ old('mobile') }}" 
                           placeholder="+91 98765 43210" required>
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">{{ __('password') }} *</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">For mobile app login (min 6 characters)</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="license_number" class="form-label">{{ __('license_number') }} *</label>
                    <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                           id="license_number" name="license_number" value="{{ old('license_number') }}" 
                           placeholder="DL-123456789" required>
                    @error('license_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Driver Type & Vehicle Assignment -->
            <h6 class="mb-3 text-primary"><i class="bi bi-truck-front"></i> Driver Type & Vehicle</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="driver_type" class="form-label">{{ __('driver_type') }} *</label>
                    <select class="form-select @error('driver_type') is-invalid @enderror" 
                            id="driver_type" name="driver_type" required>
                        <option value="">{{ __('select_option') }}</option>
                        <option value="driver_only" {{ old('driver_type') === 'driver_only' ? 'selected' : '' }}>
                            {{ __('driver_only') }} - Driver without own vehicle
                        </option>
                        <option value="own_vehicle" {{ old('driver_type') === 'own_vehicle' ? 'selected' : '' }}>
                            {{ __('own_vehicle') }} - Driver with own vehicle
                        </option>
                    </select>
                    @error('driver_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3" id="vehicle_field" style="display: none;">
                    <label for="own_vehicle_id" class="form-label">{{ __('vehicle') }} *</label>
                    <select class="form-select @error('own_vehicle_id') is-invalid @enderror" 
                            id="own_vehicle_id" name="own_vehicle_id">
                        <option value="">{{ __('select_option') }}</option>
                        @foreach($availableVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" 
                                    {{ old('own_vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_number }} - {{ $vehicle->vehicleType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('own_vehicle_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Only vehicles without assigned drivers</small>
                </div>
            </div>

            @if($availableVehicles->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Note:</strong> No vehicles available for assignment. All vehicles are already assigned to drivers.
            </div>
            @endif

            <hr class="my-4">

            <!-- Status & Contact -->
            <h6 class="mb-3 text-primary"><i class="bi bi-gear"></i> Status & Contact</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">{{ __('status') }} *</label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" name="status" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                            {{ __('active') }}
                        </option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            {{ __('inactive') }}
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="emergency_contact" class="form-label">{{ __('emergency_contact') }}</label>
                    <input type="tel" class="form-control @error('emergency_contact') is-invalid @enderror" 
                           id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}" 
                           placeholder="+91 98765 00000">
                    @error('emergency_contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">{{ __('address') }}</label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" name="address" rows="3" 
                          placeholder="Full address...">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> {{ __('save') }}
                </button>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const driverType = document.getElementById('driver_type');
    const vehicleField = document.getElementById('vehicle_field');
    const vehicleSelect = document.getElementById('own_vehicle_id');

    function toggleVehicleField() {
        if (driverType.value === 'own_vehicle') {
            vehicleField.style.display = 'block';
            vehicleSelect.required = true;
        } else {
            vehicleField.style.display = 'none';
            vehicleSelect.required = false;
            vehicleSelect.value = '';
        }
    }

    // Initial check
    toggleVehicleField();

    // Listen for changes
    driverType.addEventListener('change', toggleVehicleField);
});
</script>
@endpush