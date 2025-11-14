@extends('layouts.app')

@section('title', __('dashboard') . ' - ' . __('app_name'))

@section('content')
<div class="page-header">
    <h2>{{ __('dashboard') }}</h2>
    <div>
        <span class="text-muted">{{ __('welcome') }}, <strong>{{ auth()->user()->name }}</strong></span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-truck-front text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $totalVehicles }}</h3>
                        <p class="text-muted mb-0">{{ __('total_vehicles') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-person-badge text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $activeDrivers }}</h3>
                        <p class="text-muted mb-0">{{ __('active_drivers') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-geo-alt text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $activeTrips }}</h3>
                        <p class="text-muted mb-0">{{ __('active_trips') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="bi bi-clipboard-check text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $deliveriesThisMonth }}</h3>
                        <p class="text-muted mb-0">{{ __('deliveries_month') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue & Trip Count Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('revenue_trip_trend') }}</h5>
                <div style="height: 350px;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Status Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('trip_distribution') }}</h5>
                <div style="height: 350px;">
                    <canvas id="tripDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Charts Row -->
<div class="row g-4 mb-4">
    <!-- Cost Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('cost_breakdown') }}</h5>
                <div style="height: 300px;">
                    <canvas id="costBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Type Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('vehicle_type_distribution') }}</h5>
                <div style="height: 300px;">
                    <canvas id="vehicleTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Trips -->
<div class="content-card">
    <div class="content-card-header">
        <h5>{{ __('recent_trips') }}</h5>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-sm btn-primary">
            {{ __('view_all') }}
        </a>
    </div>
    <div class="content-card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('trip_number') }}</th>
                        <th>{{ __('vehicle') }}</th>
                        <th>{{ __('driver') }}</th>
                        <th>{{ __('shipment_reference') }}</th>
                        <th>{{ __('status') }}</th>
                        <th>{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTrips as $trip)
                    <tr>
                        <td><strong>{{ $trip->trip_number }}</strong></td>
                        <td>{{ $trip->vehicle->vehicle_number }}</td>
                        <td>{{ $trip->driver->name }}</td>
                        <td>{{ $trip->shipment_reference ?? '-' }}</td>
                        <td>
                            <span class="badge {{ statusBadgeClass($trip->status) }}">
                                {{ __($trip->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.trips.show', $trip) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> {{ __('view') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">{{ __('no_records') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js Configuration
    const chartColors = {
        primary: '#6366f1',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        purple: '#8b5cf6',
        pink: '#ec4899',
        orange: '#f97316',
        teal: '#14b8a6',
        cyan: '#06b6d4'
    };

    // Revenue & Trip Count Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueTrendCtx, {
        type: 'line',
        data: {
            labels: @json($revenueData['months']),
            datasets: [
                {
                    label: '{{ __("revenue") }} ($)',
                    data: @json($revenueData['revenue']),
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    fill: true,
                    yAxisID: 'y',
                    tension: 0.4
                },
                {
                    label: '{{ __("trip_count") }}',
                    data: @json($revenueData['tripCount']),
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    fill: true,
                    yAxisID: 'y1',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '{{ __("revenue") }} ($)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '{{ __("trip_count") }}'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Trip Status Distribution Chart (Donut)
    const tripDistributionCtx = document.getElementById('tripDistributionChart').getContext('2d');
    new Chart(tripDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_map(function($item) { return __($item['status']); }, $tripDistribution)),
            datasets: [{
                data: @json(array_column($tripDistribution, 'count')),
                backgroundColor: [
                    chartColors.warning,
                    chartColors.primary,
                    chartColors.info,
                    chartColors.success,
                    chartColors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Cost Breakdown Chart (Donut)
    const costBreakdownCtx = document.getElementById('costBreakdownChart').getContext('2d');
    new Chart(costBreakdownCtx, {
        type: 'doughnut',
        data: {
            labels: @json($costBreakdown['labels']),
            datasets: [{
                data: @json($costBreakdown['values']),
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.orange,
                    chartColors.info,
                    chartColors.purple
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Vehicle Type Distribution Chart (Bar)
    const vehicleTypeCtx = document.getElementById('vehicleTypeChart').getContext('2d');
    new Chart(vehicleTypeCtx, {
        type: 'bar',
        data: {
            labels: @json($vehicleTypeDistribution['labels']),
            datasets: [{
                label: '{{ __("vehicles") }}',
                data: @json($vehicleTypeDistribution['values']),
                backgroundColor: chartColors.primary,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush