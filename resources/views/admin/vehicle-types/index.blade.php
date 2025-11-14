@extends('layouts.app')

@section('title', __('vehicle_types') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('vehicle_types') }}</h2>
    <a href="{{ route('admin.vehicle-types.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_vehicle_type') }}
    </a>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h5>{{ __('vehicle_types') }}</h5>
        <div class="search-box">
            <form action="{{ route('admin.vehicle-types.index') }}" method="GET">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="{{ __('search') }}..." value="{{ request('search') }}">
            </form>
        </div>
    </div>
    <div class="content-card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('name') }}</th>
                        <th>{{ __('capacity') }}</th>
                        <th>{{ __('base_price') }}</th>
                        <th>{{ __('description') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleTypes as $vehicleType)
                    <tr>
                        <td><strong>{{ $vehicleType->name }}</strong></td>
                        <td>{{ $vehicleType->capacity }}</td>
                        <td>${{ number_format($vehicleType->base_price, 2) }}</td>
                        <td>{{ $vehicleType->description ?? '-' }}</td>
                        <td>
                            @if($vehicleType->status === 'active')
                                <span class="badge bg-success">{{ __('active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.vehicle-types.edit', $vehicleType) }}" class="btn-action btn-edit" title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.vehicle-types.destroy', $vehicleType) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete delete-confirm" title="{{ __('delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            {{ __('no_records') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($vehicleTypes->hasPages())
        <div class="mt-4">
            {{ $vehicleTypes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection