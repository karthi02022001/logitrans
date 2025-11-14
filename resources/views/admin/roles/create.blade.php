@extends('layouts.app')

@section('title', __('add_role') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left"></i> {{ __('back') }}
        </a>
        <h2><i class="bi bi-shield-lock me-2"></i>{{ __('add_role') }}</h2>
    </div>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf

            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('role_name') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="e.g., fleet_manager"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('role_name_hint') }}</small>
                </div>

                <div class="col-md-6">
                    <label for="display_name" class="form-label">{{ __('display_name') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('display_name') is-invalid @enderror" 
                           id="display_name" 
                           name="display_name" 
                           value="{{ old('display_name') }}"
                           placeholder="e.g., Fleet Manager"
                           required>
                    @error('display_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="description" class="form-label">{{ __('description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3"
                              placeholder="{{ __('description_placeholder') }}">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Permissions Section -->
            <h5 class="mb-3">{{ __('assign_permissions') }} <span class="text-danger">*</span></h5>
            
            @error('permissions')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <div class="row">
                @foreach($permissions as $module => $modulePermissions)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="form-check">
                                <input class="form-check-input module-checkbox" 
                                       type="checkbox" 
                                       id="module_{{ $module }}"
                                       data-module="{{ $module }}">
                                <label class="form-check-label fw-bold" for="module_{{ $module }}">
                                    <i class="bi bi-folder me-2"></i>{{ __(ucfirst($module)) }}
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($modulePermissions as $permission)
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" 
                                       type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}" 
                                       id="permission_{{ $permission->id }}"
                                       data-module="{{ $module }}"
                                       {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                    {{ $permission->display_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>{{ __('save') }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Select all permissions in a module when module checkbox is clicked
document.querySelectorAll('.module-checkbox').forEach(function(moduleCheckbox) {
    moduleCheckbox.addEventListener('change', function() {
        const module = this.dataset.module;
        const isChecked = this.checked;
        
        document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`).forEach(function(permCheckbox) {
            permCheckbox.checked = isChecked;
        });
    });
});

// Update module checkbox state based on individual permissions
document.querySelectorAll('.permission-checkbox').forEach(function(permCheckbox) {
    permCheckbox.addEventListener('change', function() {
        const module = this.dataset.module;
        const moduleCheckbox = document.querySelector(`.module-checkbox[data-module="${module}"]`);
        const modulePermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
        const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:checked`);
        
        moduleCheckbox.checked = modulePermissions.length === checkedPermissions.length;
    });
});
</script>
@endpush
@endsection