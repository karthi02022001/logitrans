@extends('layouts.app')

@section('title', __('edit_cost') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-currency-dollar"></i> {{ __('edit_cost') }}</h2>
    <a href="{{ route('admin.costs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('back') }}
    </a>
</div>

<!-- Trip Information Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('trip_details') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>{{ __('trip_number') }}:</strong>
                <p class="text-primary">{{ $cost->trip->trip_number }}</p>
            </div>
            <div class="col-md-3">
                <strong>{{ __('vehicle') }}:</strong>
                <p>{{ $cost->trip->vehicle->vehicle_number ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong>{{ __('driver') }}:</strong>
                <p>{{ $cost->trip->driver->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong>{{ __('status') }}:</strong>
                <p>
                    <span class="badge {{ statusBadgeClass($cost->trip->status) }}">
                        {{ __($cost->trip->status) }}
                    </span>
                </p>
            </div>
        </div>
        @if($cost->trip->shipment_reference)
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>{{ __('shipment_reference') }}:</strong>
                <p>{{ $cost->trip->shipment_reference }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Cost Edit Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> {{ __('cost_breakdown') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.costs.update', $cost) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <!-- Base Cost -->
                <div class="col-md-6">
                    <label for="base_cost" class="form-label">
                        {{ __('base_cost') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('base_cost') is-invalid @enderror" 
                               id="base_cost" 
                               name="base_cost" 
                               step="0.01"
                               min="0"
                               value="{{ old('base_cost', $cost->base_cost) }}" 
                               required>
                        @error('base_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('base_transportation_cost') }}</small>
                </div>

                <!-- Toll Cost -->
                <div class="col-md-6">
                    <label for="toll_cost" class="form-label">
                        {{ __('toll_cost') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('toll_cost') is-invalid @enderror" 
                               id="toll_cost" 
                               name="toll_cost" 
                               step="0.01"
                               min="0"
                               value="{{ old('toll_cost', $cost->toll_cost) }}" 
                               required>
                        @error('toll_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('highway_toll_charges') }}</small>
                </div>

                <!-- Driver Allowance -->
                <div class="col-md-6">
                    <label for="driver_allowance" class="form-label">
                        {{ __('driver_allowance') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('driver_allowance') is-invalid @enderror" 
                               id="driver_allowance" 
                               name="driver_allowance" 
                               step="0.01"
                               min="0"
                               value="{{ old('driver_allowance', $cost->driver_allowance) }}" 
                               required>
                        @error('driver_allowance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('daily_allowance_for_driver') }}</small>
                </div>

                <!-- Fuel Cost -->
                <div class="col-md-6">
                    <label for="fuel_cost" class="form-label">
                        {{ __('fuel_cost') }}
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('fuel_cost') is-invalid @enderror" 
                               id="fuel_cost" 
                               name="fuel_cost" 
                               step="0.01"
                               min="0"
                               value="{{ old('fuel_cost', $cost->fuel_cost) }}">
                        @error('fuel_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('fuel_expenses') }}</small>
                </div>

                <!-- Other Costs -->
                <div class="col-md-12">
                    <label for="other_costs" class="form-label">
                        {{ __('other_costs') }}
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('other_costs') is-invalid @enderror" 
                               id="other_costs" 
                               name="other_costs" 
                               step="0.01"
                               min="0"
                               value="{{ old('other_costs', $cost->other_costs) }}">
                        @error('other_costs')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('miscellaneous_expenses') }}</small>
                </div>

                <!-- Notes -->
                <div class="col-md-12">
                    <label for="notes" class="form-label">{{ __('notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" 
                              name="notes" 
                              rows="3">{{ old('notes', $cost->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('additional_cost_notes') }}</small>
                </div>
            </div>

            <!-- Total Cost Display -->
            <div class="alert alert-info mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5"><i class="bi bi-calculator"></i> {{ __('total_cost') }}:</span>
                    <span class="fs-4 fw-bold" id="totalCost">{{ formatCurrency($cost->total_cost) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.costs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> {{ __('cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> {{ __('update') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseCost = document.getElementById('base_cost');
    const tollCost = document.getElementById('toll_cost');
    const driverAllowance = document.getElementById('driver_allowance');
    const fuelCost = document.getElementById('fuel_cost');
    const otherCosts = document.getElementById('other_costs');
    const totalCostDisplay = document.getElementById('totalCost');

    function calculateTotal() {
        const base = parseFloat(baseCost.value) || 0;
        const toll = parseFloat(tollCost.value) || 0;
        const allowance = parseFloat(driverAllowance.value) || 0;
        const fuel = parseFloat(fuelCost.value) || 0;
        const other = parseFloat(otherCosts.value) || 0;

        const total = base + toll + allowance + fuel + other;
        totalCostDisplay.textContent = '$' + total.toFixed(2);
    }

    // Add event listeners to all cost inputs
    [baseCost, tollCost, driverAllowance, fuelCost, otherCosts].forEach(input => {
        input.addEventListener('input', calculateTotal);
    });

    // Calculate initial total
    calculateTotal();
});
</script>
@endpush