<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with('ownVehicle');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        // Filter by driver type
        if ($request->filled('driver_type')) {
            $query->where('driver_type', $request->driver_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(15);

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        // Get vehicles that don't have an assigned driver (for own_vehicle type)
        $availableVehicles = Vehicle::where('status', 'active')
            ->whereDoesntHave('ownDriver')
            ->get();

        return view('admin.drivers.create', compact('availableVehicles'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:drivers,mobile',
            'password' => 'required|string|min:6',
            'license_number' => 'required|string|max:100|unique:drivers,license_number',
            'driver_type' => 'required|in:own_vehicle,driver_only',
            'own_vehicle_id' => 'required_if:driver_type,own_vehicle|nullable|exists:vehicles,id',
            'status' => 'required|in:active,inactive',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
        ]);

        // Hash password for mobile app login
        $validated['password'] = Hash::make($validated['password']);

        // If driver_only type, remove own_vehicle_id
        if ($validated['driver_type'] === 'driver_only') {
            $validated['own_vehicle_id'] = null;
        }

        // Check if vehicle is already assigned to another driver
        if ($validated['own_vehicle_id']) {
            $existingDriver = Driver::where('own_vehicle_id', $validated['own_vehicle_id'])->first();
            if ($existingDriver) {
                return back()->withErrors(['own_vehicle_id' => __('This vehicle is already assigned to another driver.')])
                    ->withInput();
            }
        }

        Driver::create($validated);

        return redirect()->route('admin.drivers.index')
            ->with('success', __('created_successfully'));
    }

    public function edit(Driver $driver)
    {
        // Get available vehicles (excluding currently assigned vehicle)
        $availableVehicles = Vehicle::where('status', 'active')
            ->where(function ($q) use ($driver) {
                $q->whereDoesntHave('ownDriver')
                    ->orWhere('id', $driver->own_vehicle_id);
            })
            ->get();

        return view('admin.drivers.edit', compact('driver', 'availableVehicles'));
    }

    public function update(Request $request, Driver $driver)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:drivers,mobile,' . $driver->id,
            'password' => 'nullable|string|min:6',
            'license_number' => 'required|string|max:100|unique:drivers,license_number,' . $driver->id,
            'driver_type' => 'required|in:own_vehicle,driver_only',
            'own_vehicle_id' => 'required_if:driver_type,own_vehicle|nullable|exists:vehicles,id',
            'status' => 'required|in:active,inactive,on_trip',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
        ]);

        // Hash password if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // If driver_only type, remove own_vehicle_id
        if ($validated['driver_type'] === 'driver_only') {
            $validated['own_vehicle_id'] = null;
        }

        // Check if vehicle is already assigned to another driver
        if (isset($validated['own_vehicle_id']) && $validated['own_vehicle_id'] != $driver->own_vehicle_id) {
            $existingDriver = Driver::where('own_vehicle_id', $validated['own_vehicle_id'])->first();
            if ($existingDriver) {
                return back()->withErrors(['own_vehicle_id' => __('This vehicle is already assigned to another driver.')])
                    ->withInput();
            }
        }

        $driver->update($validated);

        return redirect()->route('admin.drivers.index')
            ->with('success', __('updated_successfully'));
    }

    public function destroy(Driver $driver)
    {
        // Check if driver has active trips
        if ($driver->trips()->whereIn('status', ['assigned', 'in_transit'])->exists()) {
            return back()->with('error', __('Cannot delete driver that has active trips.'));
        }

        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', __('deleted_successfully'));
    }

    public function checkAvailability(Driver $driver)
    {
        $isAvailable = $driver->isAvailable();
        $activeTrip = $driver->trips()
            ->whereIn('status', ['assigned', 'in_transit'])
            ->with('vehicle')
            ->first();

        return response()->json([
            'available' => $isAvailable,
            'status' => $driver->status,
            'active_trip' => $activeTrip ? [
                'trip_number' => $activeTrip->trip_number,
                'vehicle' => $activeTrip->vehicle->vehicle_number,
                'status' => $activeTrip->status,
            ] : null,
        ]);
    }
}
