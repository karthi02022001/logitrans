@extends('layouts.app')

@section('title', __('deliveries') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-clipboard-check"></i> {{ __('deliveries') }}</h2>
    @if(auth()->user()->hasPermission('deliveries.create'))
    <a href="{{ route('admin.deliveries.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('add_delivery') }}
    </a>
    @endif
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="avatar-sm bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-clipboard-check fs-3"></i>
            </div>
            <div>
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">{{ __('total_deliveries') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="avatar-sm bg-info bg-opacity-10 text-info">
                <i class="bi bi-calendar-check fs-3"></i>
            </div>
            <div>
                <h3 class="mb-0">{{ $stats['this_month'] }}</h3>
                <p class="text-muted mb-0">{{ __('this_month') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="avatar-sm bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle fs-3"></i>
            </div>
            <div>
                <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                <p class="text-muted mb-0">{{ __('completed_deliveries') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="avatar-sm bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock fs-3"></i>
            </div>
            <div>
                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">{{ __('pending_deliveries') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h5>{{ __('deliveries') }}</h5>
        <div class="search-box">
            <form action="{{ route('admin.deliveries.index') }}" method="GET" id="searchForm">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('search') }}..." 
                       value="{{ request('search') }}">
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="content-card-body border-bottom">
        <form action="{{ route('admin.deliveries.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">{{ __('status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('select_option') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                        {{ __('pending') }}
                    </option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>
                        {{ __('partial') }}
                    </option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                        {{ __('completed') }}
                    </option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>
                        {{ __('failed') }}
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('from_date') }}</label>
                <input type="date" name="from_date" class="form-control" 
                       value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('to_date') }}</label>
                <input type="date" name="to_date" class="form-control" 
                       value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> {{ __('filter') }}
                </button>
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-secondary">
                    {{ __('reset') }}
                </a>
            </div>
        </form>
    </div>

    <div class="content-card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('trip_number') }}</th>
                        <th>{{ __('customer_name') }}</th>
                        <th>{{ __('phone') }}</th>
                        <th>{{ __('vehicle') }}</th>
                        <th>{{ __('driver') }}</th>
                        <th>{{ __('delivered_at') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $delivery->trip->trip_number ?? 'N/A' }}</strong>
                        </td>
                        <td>
                            <strong>{{ $delivery->customer_name }}</strong>
                            @if($delivery->customer_email)
                                <br><small class="text-muted">{{ $delivery->customer_email }}</small>
                            @endif
                        </td>
                        <td>{{ $delivery->customer_phone ?? '-' }}</td>
                        <td>
                            @if($delivery->trip && $delivery->trip->vehicle)
                                {{ $delivery->trip->vehicle->vehicle_number }}
                                <br><small class="text-muted">{{ $delivery->trip->vehicle->vehicleType->name ?? '' }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($delivery->trip && $delivery->trip->driver)
                                {{ $delivery->trip->driver->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($delivery->delivered_at)->format('M d, Y') }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'partial' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$delivery->delivery_status] ?? 'secondary' }}">
                                {{ __($delivery->delivery_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if(auth()->user()->hasPermission('deliveries.view'))
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                   class="btn-action btn-edit" 
                                   title="{{ __('view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endif

                                @if(auth()->user()->hasPermission('deliveries.edit'))
                                <a href="{{ route('admin.deliveries.edit', $delivery) }}" 
                                   class="btn-action btn-edit" 
                                   title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif

                                @if(auth()->user()->hasPermission('deliveries.delete'))
                                <form action="{{ route('admin.deliveries.destroy', $delivery) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-action btn-delete delete-confirm" 
                                            title="{{ __('delete') }}"
                                            onclick="return confirm('{{ __('confirm_delete') }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            {{ __('no_records') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($deliveries->hasPages())
        <div class="mt-4">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection