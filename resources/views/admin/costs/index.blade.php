@extends('layouts.app')

@section('title', __('costs') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2><i class="bi bi-currency-dollar"></i> {{ __('trip_costs') }}</h2>
</div>

<!-- Search Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.costs.index') }}" class="row g-3">
            <div class="col-md-8">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="{{ __('search') }}... ({{ __('trip_number') }})"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> {{ __('search') }}
                </button>
                <a href="{{ route('admin.costs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> {{ __('reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Costs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>{{ __('trip_number') }}</th>
                        <th>{{ __('vehicle') }}</th>
                        <th>{{ __('driver') }}</th>
                        <th>{{ __('base_cost') }}</th>
                        <th>{{ __('toll_cost') }}</th>
                        <th>{{ __('driver_allowance') }}</th>
                        <th>{{ __('fuel_cost') }}</th>
                        <th>{{ __('other_costs') }}</th>
                        <th>{{ __('total_cost') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costs as $cost)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $cost->trip->trip_number }}</strong>
                            @if($cost->trip->shipment_reference)
                            <br><small class="text-muted">Ref: {{ $cost->trip->shipment_reference }}</small>
                            @endif
                        </td>
                        <td>
                            @if($cost->trip->vehicle)
                                <div>{{ $cost->trip->vehicle->vehicle_number }}</div>
                                <small class="text-muted">{{ $cost->trip->vehicle->vehicleType->name ?? '-' }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cost->trip->driver)
                                <div>{{ $cost->trip->driver->name }}</div>
                                <small class="text-muted">{{ $cost->trip->driver->phone }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ formatCurrency($cost->base_cost) }}</td>
                        <td>{{ formatCurrency($cost->toll_cost) }}</td>
                        <td>{{ formatCurrency($cost->driver_allowance) }}</td>
                        <td>{{ formatCurrency($cost->fuel_cost) }}</td>
                        <td>{{ formatCurrency($cost->other_costs) }}</td>
                        <td>
                            <strong class="text-success">{{ formatCurrency($cost->total_cost) }}</strong>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.costs.edit', $cost) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">{{ __('no_records') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($costs->count() > 0)
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">{{ __('total') }}:</td>
                        <td>{{ formatCurrency($costs->sum('base_cost')) }}</td>
                        <td>{{ formatCurrency($costs->sum('toll_cost')) }}</td>
                        <td>{{ formatCurrency($costs->sum('driver_allowance')) }}</td>
                        <td>{{ formatCurrency($costs->sum('fuel_cost')) }}</td>
                        <td>{{ formatCurrency($costs->sum('other_costs')) }}</td>
                        <td class="text-success">{{ formatCurrency($costs->sum('total_cost')) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination -->
        @if($costs->hasPages())
        <div class="mt-4">
            {{ $costs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection