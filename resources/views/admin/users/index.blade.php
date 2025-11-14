@extends('layouts.app')

@section('title', __('users') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-people me-2"></i>{{ __('users') }}</h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_user') }}
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

<!-- Search Filter -->
<div class="content-card mb-4">
    <div class="content-card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-10">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="{{ __('search_users') }}..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> {{ __('search') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users List -->
<div class="content-card">
    <div class="content-card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('name') }}</th>
                        <th>{{ __('email') }}</th>
                        <th>{{ __('mobile') }}</th>
                        <th>{{ __('roles') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('created_at') }}</th>
                        <th width="150">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>
                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                        </td>
                        <td>
                            @if($user->mobile)
                                <i class="bi bi-phone me-1"></i>{{ $user->mobile }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->display_name }}</span>
                            @empty
                                <span class="badge bg-secondary">{{ __('no_role') }}</span>
                            @endforelse
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge bg-success">{{ __('active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('inactive') }}</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" 
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
            {{ $users->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-people" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">{{ __('no_users_found') }}</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('add_user') }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection