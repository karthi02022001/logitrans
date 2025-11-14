@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>{{ __('Create New Shipment') }}</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.shipments.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Shipment Number -->
                    <div class="col-md-6 mb-3">
                        <label for="shipment_number" class="form-label">{{ __('Shipment Number') }} <span class="text-danger">*</span></label>
                        <input type="text" name="shipment_number" id="shipment_number" class="form-control @error('shipment_number') is-invalid @enderror" value="{{ old('shipment_number') }}" placeholder="SH-2025-0001" required>
                        @error('shipment_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('Enter a unique shipment number (e.g., SH-2025-0001)') }}</small>
                    </div>

                    <!-- Vehicle Type -->
                    <div class="col-md-6 mb-3">
                        <label for="vehicle_type_id" class="form-label">{{ __('Vehicle Type') }} <span class="text-danger">*</span></label>
                        <select name="vehicle_type_id" id="vehicle_type_id" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select Vehicle Type') }}</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}" {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->capacity }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('This determines which vehicles can be assigned to this shipment') }}</small>
                    </div>
                </div>

                <div class="row">
                    <!-- Cargo Weight -->
                    <div class="col-md-6 mb-3">
                        <label for="cargo_weight" class="form-label">{{ __('Cargo Weight (Tons)') }}</label>
                        <input type="number" step="0.01" min="0" name="cargo_weight" id="cargo_weight" class="form-control @error('cargo_weight') is-invalid @enderror" value="{{ old('cargo_weight') }}" placeholder="0.00">
                        @error('cargo_weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="col-md-6 mb-3">
                        <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ __('Create Shipment') }}
                        </button>
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection