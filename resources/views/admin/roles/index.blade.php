@extends('layouts.app')

@section('title', __('roles') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-shield-lock me-2"></i>{{ __('roles') }}</h2>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_role') }}
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Roles List -->
<div class="content-card">
    <div class="content-card-body">
        @if($roles->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('name') }}</th>
                        <th>{{ __('display_name') }}</th>
                        <th>{{ __('description') }}</th>
                        <th>{{ __('users_count') }}</th>
                        <th>{{ __('permissions_count') }}</th>
                        <th>{{ __('created_at') }}</th>
                        <th width="150">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        </td>
                        <td><strong>{{ $role->display_name }}</strong></td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $role->users_count }} {{ __('users') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $role->permissions->count() }} {{ __('permissions') }}</span>
                        </td>
                        <td>{{ $role->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.roles.edit', $role) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($role->name !== 'super_admin')
                                <form action="{{ route('admin.roles.destroy', $role) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('are_you_sure') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="{{ __('delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $roles->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-shield-lock" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">{{ __('no_roles_found') }}</p>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('add_role') }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection