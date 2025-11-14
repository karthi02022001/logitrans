@extends('layouts.app')

@section('title', __('edit_user') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left"></i> {{ __('back') }}
        </a>
        <h2><i class="bi bi-person-gear me-2"></i>{{ __('edit_user') }}: {{ $user->name }}</h2>
    </div>
</div>

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- User Information -->
            <h5 class="mb-3"><i class="bi bi-person me-2"></i>{{ __('user_information') }}</h5>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('name') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           placeholder="{{ __('enter_full_name') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('email') }} <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}"
                           placeholder="user@example.com"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="mobile" class="form-label">{{ __('mobile') }}</label>
                    <input type="text" 
                           class="form-control @error('mobile') is-invalid @enderror" 
                           id="mobile" 
                           name="mobile" 
                           value="{{ old('mobile', $user->mobile) }}"
                           placeholder="+1234567890">
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">{{ __('status') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status" 
                            required>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                            {{ __('active') }}
                        </option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                            {{ __('inactive') }}
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Password Section -->
            <h5 class="mb-3"><i class="bi bi-lock me-2"></i>{{ __('change_password') }} <small class="text-muted">({{ __('optional') }})</small></h5>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>{{ __('leave_blank_to_keep_current_password') }}
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label">{{ __('new_password') }}</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="{{ __('minimum_6_characters') }}">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">{{ __('confirm_new_password') }}</label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="{{ __('re_enter_password') }}">
                </div>
            </div>

            <hr class="my-4">

            <!-- Roles Section -->
            <h5 class="mb-3"><i class="bi bi-shield-lock me-2"></i>{{ __('assign_roles') }} <span class="text-danger">*</span></h5>
            
            @error('roles')
                <div class="alert alert-danger mb-3">{{ $message }}</div>
            @enderror

            @php
                $selectedRoles = old('roles', $user->roles->pluck('id')->toArray());
            @endphp

            <div class="row">
                @foreach($roles as $role)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="roles[]" 
                                       value="{{ $role->id }}" 
                                       id="role_{{ $role->id }}"
                                       {{ in_array($role->id, $selectedRoles) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    <strong>{{ $role->display_name }}</strong>
                                    @if($role->description)
                                        <br>
                                        <small class="text-muted">{{ $role->description }}</small>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>{{ __('update') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    {{ __('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection