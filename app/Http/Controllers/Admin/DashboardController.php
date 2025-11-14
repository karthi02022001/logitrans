<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Delivery;
use App\Models\Tripcost;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalVehicles = Vehicle::where('status', 'active')->count();
        $activeDrivers = Driver::where('status', 'active')->count();
        $activeTrips = Trip::whereIn('status', ['assigned', 'in_transit'])->count();
        $deliveriesThisMonth = Delivery::whereMonth('delivered_at', date('m'))
            ->whereYear('delivered_at', date('Y'))
            ->count();

        // Get recent trips
        $recentTrips = Trip::with(['vehicle', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        // Revenue & Trip Count Trend (Last 12 months)
        $revenueData = $this->getRevenueAndTripTrend();

        // Trip Status Distribution
        $tripDistribution = $this->getTripStatusDistribution();

        // Cost Breakdown
        $costBreakdown = $this->getCostBreakdown();

        // Vehicle Type Distribution
        $vehicleTypeDistribution = $this->getVehicleTypeDistribution();

        return view('admin.dashboard', compact(
            'totalVehicles',
            'activeDrivers',
            'activeTrips',
            'deliveriesThisMonth',
            'recentTrips',
            'revenueData',
            'tripDistribution',
            'costBreakdown',
            'vehicleTypeDistribution'
        ));
    }

    private function getRevenueAndTripTrend()
    {
        $months = [];
        $revenue = [];
        $tripCount = [];

        // Get data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $year = $date->format('Y');
            $month = $date->format('m');

            $months[] = $monthName;

            // Get total revenue for the month
            $monthlyRevenue = Tripcost::whereHas('trip', function ($query) use ($year, $month) {
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            })->sum(DB::raw('base_cost + toll_cost + driver_allowance + fuel_cost + other_costs'));

            $revenue[] = round($monthlyRevenue, 2);

            // Get trip count for the month
            $monthlyTrips = Trip::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $tripCount[] = $monthlyTrips;
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
            'tripCount' => $tripCount
        ];
    }

    private function getTripStatusDistribution()
    {
        $statuses = ['pending', 'assigned', 'in_transit', 'delivered', 'cancelled'];
        $data = [];

        foreach ($statuses as $status) {
            $count = Trip::where('status', $status)->count();
            $data[] = [
                'status' => $status,
                'count' => $count
            ];
        }

        return $data;
    }

    private function getCostBreakdown()
    {
        $costs = Tripcost::selectRaw('
            SUM(base_cost) as total_base,
            SUM(toll_cost) as total_toll,
            SUM(driver_allowance) as total_allowance,
            SUM(fuel_cost) as total_fuel,
            SUM(other_costs) as total_other
        ')->first();

        return [
            'labels' => ['Base Cost', 'Toll', 'Driver Allowance', 'Fuel', 'Other'],
            'values' => [
                round($costs->total_base ?? 0, 2),
                round($costs->total_toll ?? 0, 2),
                round($costs->total_allowance ?? 0, 2),
                round($costs->total_fuel ?? 0, 2),
                round($costs->total_other ?? 0, 2)
            ]
        ];
    }

    private function getVehicleTypeDistribution()
    {
        $vehicleTypes = Vehicletype::withCount('vehicles')->get();

        $labels = [];
        $values = [];

        foreach ($vehicleTypes as $type) {
            $labels[] = $type->name;
            $values[] = $type->vehicles_count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}
    