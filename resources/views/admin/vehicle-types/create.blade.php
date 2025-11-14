@extends('layouts.app')

@section('title', __('add_vehicle_type') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('add_vehicle_type') }}</h2>
    <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cancel') }}
    </a>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.vehicle-types.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ __('name') }} *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label">{{ __('capacity') }} *</label>
                    <input type="text" class="form-control @error('capacity') is-invalid @enderror" 
                           id="capacity" name="capacity" value="{{ old('capacity') }}" 
                           placeholder="e.g., 10 Tons" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="base_price" class="form-label">{{ __('base_price') }} ($) *</label>
                    <input type="number" class="form-control @error('base_price') is-invalid @enderror" 
                           id="base_price" name="base_price" value="{{ old('base_price') }}" 
                           step="0.01" min="0" required>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">{{ __('status') }} *</label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" name="status" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
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
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">{{ __('description') }}</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> {{ __('save') }}
                </button>
                <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection