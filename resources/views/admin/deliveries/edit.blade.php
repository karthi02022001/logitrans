@extends('layouts.app')

@section('title', __('edit') . ' ' . __('deliveries') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.deliveries.index') }}">{{ __('deliveries') }}</a></li>
                <li class="breadcrumb-item active">{{ __('edit') }}</li>
            </ol>
        </nav>
        <h2><i class="bi bi-pencil"></i> {{ __('edit') }} {{ __('deliveries') }}</h2>
    </div>
    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
    </a>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.deliveries.update', $delivery) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Trip Information (Read-only) -->
            <div class="mb-4">
                <h5 class="text-primary mb-3"><i class="bi bi-geo-alt"></i> {{ __('trip') }}</h5>
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('trip_number') }}</label>
                            <p class="mb-0"><strong>{{ $delivery->trip->trip_number }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('vehicle') }}</label>
                            <p class="mb-0">{{ $delivery->trip->vehicle->vehicle_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('driver') }}</label>
                            <p class="mb-0">{{ $delivery->trip->driver->name ?? 'N/A' }}</p>
                        </div>
                    </div>
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
                               value="{{ old('customer_name', $delivery->customer_name) }}" 
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
                               value="{{ old('customer_phone', $delivery->customer_phone) }}">
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
                               value="{{ old('customer_email', $delivery->customer_email) }}">
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
                               value="{{ old('delivered_at', \Carbon\Carbon::parse($delivery->delivered_at)->format('Y-m-d\TH:i')) }}" 
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
                              required>{{ old('delivery_address', $delivery->delivery_address) }}</textarea>
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
                            <option value="pending" {{ old('delivery_status', $delivery->delivery_status) === 'pending' ? 'selected' : '' }}>
                                {{ __('pending') }}
                            </option>
                            <option value="partial" {{ old('delivery_status', $delivery->delivery_status) === 'partial' ? 'selected' : '' }}>
                                {{ __('partial') }}
                            </option>
                            <option value="completed" {{ old('delivery_status', $delivery->delivery_status) === 'completed' ? 'selected' : '' }}>
                                {{ __('completed') }}
                            </option>
                            <option value="failed" {{ old('delivery_status', $delivery->delivery_status) === 'failed' ? 'selected' : '' }}>
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
                              rows="3">{{ old('delivery_remarks', $delivery->delivery_remarks) }}</textarea>
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
                        @if($delivery->signature_path)
                        <div class="mb-2">
                            <a href="{{ Storage::url($delivery->signature_path) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View Current Signature
                            </a>
                        </div>
                        @endif
                        <input type="file" 
                               class="form-control @error('signature') is-invalid @enderror" 
                               id="signature" 
                               name="signature" 
                               accept="image/jpeg,image/jpg,image/png">
                        <small class="text-muted">Max 2MB. Formats: JPG, PNG. Leave empty to keep existing file.</small>
                        @error('signature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pod_file" class="form-label">{{ __('pod_file') }}</label>
                        @if($delivery->pod_file_path)
                        <div class="mb-2">
                            <a href="{{ Storage::url($delivery->pod_file_path) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View Current POD
                            </a>
                        </div>
                        @endif
                        <input type="file" 
                               class="form-control @error('pod_file') is-invalid @enderror" 
                               id="pod_file" 
                               name="pod_file" 
                               accept="image/jpeg,image/jpg,image/png,application/pdf">
                        <small class="text-muted">Max 5MB. Formats: JPG, PNG, PDF. Leave empty to keep existing file.</small>
                        @error('pod_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> {{ __('save') }}
                </button>
                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection