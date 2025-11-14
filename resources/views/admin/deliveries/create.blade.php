@extends('layouts.app')

@section('title', __('add_delivery') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-clipboard-check"></i> {{ __('add_delivery') }}</h2>
    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
    </a>
</div>

@if($trips->isEmpty())
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle fs-4 me-2"></i>
    <strong>{{ __('no_trips_available') }}</strong>
    <p class="mb-0 mt-2">
        To create a delivery, you need a completed trip without an existing delivery record. 
        Please ensure you have trips with "delivered" status.
    </p>
</div>
@endif

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.deliveries.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Trip Selection -->
            <div class="mb-4">
                <h5 class="text-primary mb-3"><i class="bi bi-geo-alt"></i> {{ __('trip') }}</h5>
                <div class="mb-3">
                    <label for="trip_id" class="form-label">{{ __('select_trip') }} *</label>
                    <select class="form-select @error('trip_id') is-invalid @enderror" 
                            id="trip_id" 
                            name="trip_id" 
                            required 
                            {{ $trips->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ __('select_option') }}</option>
                        @foreach($trips as $trip)
                        <option value="{{ $trip->id }}" {{ old('trip_id') == $trip->id ? 'selected' : '' }}>
                            {{ $trip->trip_number }} - 
                            {{ $trip->vehicle->vehicle_number ?? 'N/A' }} - 
                            {{ $trip->driver->name ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                    @error('trip_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Customer Information -->
            <div class="mb-4">
                <h5 class="text-primary mb-3"><i class="bi bi-person"></i> Customer Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_name" class="form-label">{{ __('customer_name') }} *</label>
                        <input type="text" 
                               class="form-control @error('customer_name') is-invalid @enderror" 
                               id="customer_name" 
                               name="customer_name" 
                               value="{{ old('customer_name') }}" 
                               required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="customer_phone" class="form-label">{{ __('customer_phone') }}</label>
                        <input type="text" 
                               class="form-control @error('customer_phone') is-invalid @enderror" 
                               id="customer_phone" 
                               name="customer_phone" 
                               value="{{ old('customer_phone') }}">
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_email" class="form-label">{{ __('customer_email') }}</label>
                        <input type="email" 
                               class="form-control @error('customer_email') is-invalid @enderror" 
                               id="customer_email" 
                               name="customer_email" 
                               value="{{ old('customer_email') }}">
                        @error('customer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="delivered_at" class="form-label">{{ __('delivered_at') }} *</label>
                        <input type="datetime-local" 
                               class="form-control @error('delivered_at') is-invalid @enderror" 
                               id="delivered_at" 
                               name="delivered_at" 
                               value="{{ old('delivered_at') }}" 
                               required>
                        @error('delivered_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="delivery_address" class="form-label">{{ __('delivery_address') }} *</label>
                    <textarea class="form-control @error('delivery_address') is-invalid @enderror" 
                              id="delivery_address" 
                              name="delivery_address" 
                              rows="3" 
                              required>{{ old('delivery_address') }}</textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Delivery Details -->
            <div class="mb-4">
                <h5 class="text-primary mb-3"><i class="bi bi-box-seam"></i> Delivery Details</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="delivery_status" class="form-label">{{ __('delivery_status') }} *</label>
                        <select class="form-select @error('delivery_status') is-invalid @enderror" 
                                id="delivery_status" 
                                name="delivery_status" 
                                required>
                            <option value="pending" {{ old('delivery_status') === 'pending' ? 'selected' : '' }}>
                                {{ __('pending') }}
                            </option>
                            <option value="partial" {{ old('delivery_status') === 'partial' ? 'selected' : '' }}>
                                {{ __('partial') }}
                            </option>
                            <option value="completed" {{ old('delivery_status') === 'completed' ? 'selected' : '' }}>
                                {{ __('completed') }}
                            </option>
                            <option value="failed" {{ old('delivery_status') === 'failed' ? 'selected' : '' }}>
                                {{ __('failed') }}
                            </option>
                        </select>
                        @error('delivery_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="delivery_remarks" class="form-label">{{ __('delivery_remarks') }}</label>
                    <textarea class="form-control @error('delivery_remarks') is-invalid @enderror" 
                              id="delivery_remarks" 
                              name="delivery_remarks" 
                              rows="3">{{ old('delivery_remarks') }}</textarea>
                    @error('delivery_remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Documents -->
            <div class="mb-4">
                <h5 class="text-primary mb-3"><i class="bi bi-file-earmark"></i> Documents</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="signature" class="form-label">{{ __('signature') }}</label>
                        <input type="file" 
                               class="form-control @error('signature') is-invalid @enderror" 
                               id="signature" 
                               name="signature" 
                               accept="image/jpeg,image/jpg,image/png">
                        <small class="text-muted">Max 2MB. Formats: JPG, PNG</small>
                        @error('signature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pod_file" class="form-label">{{ __('pod_file') }}</label>
                        <input type="file" 
                               class="form-control @error('pod_file') is-invalid @enderror" 
                               id="pod_file" 
                               name="pod_file" 
                               accept="image/jpeg,image/jpg,image/png,application/pdf">
                        <small class="text-muted">Max 5MB. Formats: JPG, PNG, PDF</small>
                        @error('pod_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" 
                        class="btn btn-primary" 
                        {{ $trips->isEmpty() ? 'disabled' : '' }}>
                    <i class="bi bi-check-circle"></i> {{ __('save') }}
                </button>
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection