<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('vehicleType');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('vehicle_number', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->latest()->paginate(15);

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::where('status', 'active')->get();
        return view('admin.vehicles.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_number' => 'required|string|max:50|unique:vehicles,vehicle_number',
            'status' => 'required|in:active,inactive,maintenance',
            'registration_date' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Vehicle::create($validated);

        return redirect()->route('admin.vehicles.index')
            ->with('success', __('created_successfully'));
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicleTypes = VehicleType::where('status', 'active')->get();
        return view('admin.vehicles.edit', compact('vehicle', 'vehicleTypes'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Validation
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_number' => 'required|string|max:50|unique:vehicles,vehicle_number,' . $vehicle->id,
            'status' => 'required|in:active,inactive,maintenance',
            'registration_date' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles.index')
            ->with('success', __('updated_successfully'));
    }

    public function destroy(Vehicle $vehicle)
    {
        // Check if vehicle is in use
        if ($vehicle->trips()->whereIn('status', ['assigned', 'in_transit'])->exists()) {
            return back()->with('error', __('Cannot delete vehicle that has active trips.'));
        }

        // Check if vehicle has an assigned driver
        if ($vehicle->ownDriver) {
            return back()->with('error', __('Cannot delete vehicle that has an assigned driver.'));
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', __('deleted_successfully'));
    }
}
