@extends('layouts.app')

@section('title', __('edit') . ' ' . __('vehicle') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('edit') }} {{ __('vehicle') }}: {{ $vehicle->vehicle_number }}</h2>
    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
    </a>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="vehicle_type_id" class="form-label">{{ __('vehicle_type') }} *</label>
                    <select class="form-select @error('vehicle_type_id') is-invalid @enderror" 
                            id="vehicle_type_id" name="vehicle_type_id" required>
                        <option value="">{{ __('select_option') }}</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}" 
                                    {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->capacity }})
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="vehicle_number" class="form-label">{{ __('vehicle_number') }} *</label>
                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" 
                           id="vehicle_number" name="vehicle_number" 
                           value="{{ old('vehicle_number', $vehicle->vehicle_number) }}" 
                           placeholder="e.g., MH-01-AB-1234" required>
                    @error('vehicle_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">{{ __('status') }} *</label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" name="status" required>
                        <option value="active" {{ old('status', $vehicle->status) === 'active' ? 'selected' : '' }}>
                            {{ __('active') }}
                        </option>
                        <option value="inactive" {{ old('status', $vehicle->status) === 'inactive' ? 'selected' : '' }}>
                            {{ __('inactive') }}
                        </option>
                        <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>
                            {{ __('maintenance') }}
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="registration_date" class="form-label">{{ __('registration_date') }}</label>
                    <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                           id="registration_date" name="registration_date" 
                           value="{{ old('registration_date', $vehicle->registration_date?->format('Y-m-d')) }}">
                    @error('registration_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="insurance_expiry" class="form-label">{{ __('insurance_expiry') }}</label>
                    <input type="date" class="form-control @error('insurance_expiry') is-invalid @enderror" 
                           id="insurance_expiry" name="insurance_expiry" 
                           value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}">
                    @error('insurance_expiry')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Keep track of insurance renewal dates</small>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">{{ __('notes') }}</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3" 
                          placeholder="Any additional information about the vehicle...">{{ old('notes', $vehicle->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Additional Information -->
            @if($vehicle->ownDriver)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Assigned Driver:</strong> {{ $vehicle->ownDriver->name }} 
                ({{ $vehicle->ownDriver->mobile }})
            </div>
            @endif

            @if($vehicle->trips()->whereIn('status', ['assigned', 'in_transit'])->exists())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Active Trips:</strong> This vehicle has active trips. 
                Be cautious when changing status.
            </div>
            @endif

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> {{ __('save') }}
                </button>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Vehicle History (Optional Section) -->
<div class="content-card mt-4">
    <div class="content-card-header">
        <h5><i class="bi bi-clock-history"></i> Vehicle History</h5>
    </div>
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2"><strong>Created:</strong> {{ $vehicle->created_at->format('M d, Y h:i A') }}</p>
                <p class="mb-2"><strong>Last Updated:</strong> {{ $vehicle->updated_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong>Total Trips:</strong> {{ $vehicle->trips()->count() }}</p>
                <p class="mb-2"><strong>Active Trips:</strong> {{ $vehicle->trips()->whereIn('status', ['assigned', 'in_transit'])->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection