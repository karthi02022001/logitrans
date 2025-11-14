<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCost;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostController extends Controller
{
    public function index(Request $request)
    {
        $query = TripCost::with(['trip.vehicle', 'trip.driver']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trip', function ($q) use ($search) {
                $q->where('trip_number', 'like', "%{$search}%");
            });
        }

        $costs = $query->latest()->paginate(15);

        return view('admin.costs.index', compact('costs'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'base_cost' => 'required|numeric|min:0',
            'toll_cost' => 'required|numeric|min:0',
            'driver_allowance' => 'required|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if cost already exists for this trip
        $existingCost = TripCost::where('trip_id', $validated['trip_id'])->first();
        if ($existingCost) {
            return back()->with('error', __('Cost already exists for this trip. Please edit instead.'));
        }

        // Calculate total cost
        $totalCost = $validated['base_cost'] +
            $validated['toll_cost'] +
            $validated['driver_allowance'] +
            ($validated['fuel_cost'] ?? 0) +
            ($validated['other_costs'] ?? 0);

        // Create cost
        TripCost::create([
            'trip_id' => $validated['trip_id'],
            'base_cost' => $validated['base_cost'],
            'toll_cost' => $validated['toll_cost'],
            'driver_allowance' => $validated['driver_allowance'],
            'fuel_cost' => $validated['fuel_cost'] ?? 0,
            'other_costs' => $validated['other_costs'] ?? 0,
            'total_cost' => $totalCost,
            'notes' => $validated['notes'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.costs.index')
            ->with('success', __('created_successfully'));
    }

    public function edit(TripCost $cost)
    {
        $cost->load('trip');
        return view('admin.costs.edit', compact('cost'));
    }

    public function update(Request $request, TripCost $cost)
    {
        // Validation
        $validated = $request->validate([
            'base_cost' => 'required|numeric|min:0',
            'toll_cost' => 'required|numeric|min:0',
            'driver_allowance' => 'required|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate total cost
        $totalCost = $validated['base_cost'] +
            $validated['toll_cost'] +
            $validated['driver_allowance'] +
            ($validated['fuel_cost'] ?? 0) +
            ($validated['other_costs'] ?? 0);

        // Update cost
        $cost->update([
            'base_cost' => $validated['base_cost'],
            'toll_cost' => $validated['toll_cost'],
            'driver_allowance' => $validated['driver_allowance'],
            'fuel_cost' => $validated['fuel_cost'] ?? 0,
            'other_costs' => $validated['other_costs'] ?? 0,
            'total_cost' => $totalCost,
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('admin.costs.index')
            ->with('success', __('updated_successfully'));
    }
}
