<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Shipment;
use App\Models\VehicleType;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    /**
     * Display a listing of shipments
     */
    public function index(Request $request)
    {
        $query = Shipment::with(['vehicleType', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Vehicle type filter
        if ($request->filled('vehicle_type_id')) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(15);
        $vehicleTypes = VehicleType::where('status', 'active')->get();

        return view('admin.shipments.index', compact('shipments', 'vehicleTypes'));
    }

    /**
     * Show the form for creating a new shipment
     */
    public function create()
    {
        $vehicleTypes = VehicleType::where('status', 'active')->get();
        return view('admin.shipments.create', compact('vehicleTypes'));
    }

    /**
     * Store a newly created shipment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_number' => 'required|string|max:50|unique:shipments,shipment_number',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'cargo_weight' => 'nullable|numeric|min:0',
            'priority' => 'required|in:normal,high,urgent',
        ], [
            'shipment_number.required' => __('Shipment number is required'),
            'shipment_number.unique' => __('This shipment number already exists. Please use a different number.'),
            'shipment_number.max' => __('Shipment number cannot exceed 50 characters'),
        ]);

        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $shipment = Shipment::create($validated);

        return redirect()->route('admin.shipments.index')
            ->with('success', __('Shipment created successfully'));
    }

    /**
     * Display the specified shipment
     */
    public function show(Shipment $shipment)
    {
        $shipment->load(['vehicleType', 'creator', 'trips.vehicle', 'trips.driver']);

        // Get available vehicles for this shipment's vehicle type
        $availableVehicles = Vehicle::where('vehicle_type_id', $shipment->vehicle_type_id)
            ->where('status', 'active')
            ->with('vehicleType')
            ->get();

        return view('admin.shipments.show', compact('shipment', 'availableVehicles'));
    }

    /**
     * Show the form for editing the specified shipment
     */
    public function edit(Shipment $shipment)
    {
        // Can only edit pending shipments
        if ($shipment->status !== 'pending') {
            return redirect()->route('admin.shipments.index')
                ->with('error', __('Cannot edit shipment that is not pending'));
        }

        $vehicleTypes = VehicleType::where('status', 'active')->get();
        return view('admin.shipments.edit', compact('shipment', 'vehicleTypes'));
    }

    /**
     * Update the specified shipment
     */
    public function update(Request $request, Shipment $shipment)
    {
        // Can only edit pending shipments
        if ($shipment->status !== 'pending') {
            return redirect()->route('admin.shipments.index')
                ->with('error', __('Cannot edit shipment that is not pending'));
        }

        $validated = $request->validate([
            'shipment_number' => 'required|string|max:50|unique:shipments,shipment_number,' . $shipment->id,
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'cargo_weight' => 'nullable|numeric|min:0',
            'priority' => 'required|in:normal,high,urgent',
        ], [
            'shipment_number.required' => __('Shipment number is required'),
            'shipment_number.unique' => __('This shipment number already exists. Please use a different number.'),
            'shipment_number.max' => __('Shipment number cannot exceed 50 characters'),
        ]);

        $shipment->update($validated);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', __('Shipment updated successfully'));
    }

    /**
     * Remove the specified shipment
     */
    public function destroy(Shipment $shipment)
    {
        // Can only delete pending or cancelled shipments
        if (!in_array($shipment->status, ['pending', 'cancelled'])) {
            return redirect()->route('admin.shipments.index')
                ->with('error', __('Cannot delete shipment that has been assigned or delivered'));
        }

        $shipment->delete();

        return redirect()->route('admin.shipments.index')
            ->with('success', __('Shipment deleted successfully'));
    }

    /**
     * Show available vehicles for assignment (called from shipment show page)
     */
    public function assignVehicle(Shipment $shipment)
    {
        // Check if shipment is already assigned
        if ($shipment->status !== 'pending') {
            return redirect()->route('admin.shipments.show', $shipment)
                ->with('error', __('This shipment has already been assigned'));
        }

        // Get available vehicles matching the shipment's vehicle type
        $availableVehicles = Vehicle::where('vehicle_type_id', $shipment->vehicle_type_id)
            ->where('status', 'active')
            ->with(['vehicleType', 'ownDriver'])
            ->get();

        // Get available drivers (those without active trips)
        $availableDrivers = Driver::where('status', 'active')
            ->with('ownVehicle')
            ->get()
            ->filter(function ($driver) {
                return !$driver->hasActiveTrip();
            });

        return view('admin.shipments.assign-vehicle', compact('shipment', 'availableVehicles', 'availableDrivers'));
    }

    /**
     * Store the vehicle and driver assignment (create trip)
     */
    public function storeAssignment(Request $request, Shipment $shipment)
    {
        // Validate
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'pickup_location' => 'required|string|max:500',
            'drop_location' => 'nullable|string|max:500',
            'has_multiple_locations' => 'boolean',
            'trip_instructions' => 'nullable|string',
        ]);

        // Verify vehicle matches shipment's vehicle type
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if ($vehicle->vehicle_type_id !== $shipment->vehicle_type_id) {
            return redirect()->back()
                ->with('error', __('Selected vehicle does not match the required vehicle type'));
        }

        // Verify driver is available
        $driver = Driver::findOrFail($validated['driver_id']);
        if ($driver->hasActiveTrip()) {
            return redirect()->back()
                ->with('error', __('Selected driver is not available'));
        }

        // Check driver-vehicle compatibility
        if ($driver->driver_type === 'own_vehicle') {
            if ($driver->own_vehicle_id !== $vehicle->id) {
                return redirect()->back()
                    ->with('error', __('This driver can only be assigned to their own vehicle'));
            }
        }

        DB::beginTransaction();
        try {
            // Generate trip number
            $tripNumber = Trip::generateTripNumber();

            // Create the trip
            $trip = Trip::create([
                'trip_number' => $tripNumber,
                'shipment_id' => $shipment->id,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'shipment_reference' => $shipment->shipment_number,
                'pickup_location' => $validated['pickup_location'],
                'drop_location' => $validated['drop_location'] ?? null,
                'has_multiple_locations' => $validated['has_multiple_locations'] ?? 0,
                'trip_instructions' => $validated['trip_instructions'] ?? null,
                'status' => 'assigned',
                'created_by' => Auth::id(),
            ]);

            // Update shipment status
            $shipment->update(['status' => 'assigned']);

            DB::commit();

            return redirect()->route('admin.shipments.show', $shipment)
                ->with('success', __('Vehicle and driver assigned successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('Failed to assign vehicle and driver: ') . $e->getMessage());
        }
    }

    /**
     * Cancel a shipment
     */
    public function cancel(Shipment $shipment)
    {
        if ($shipment->status === 'delivered') {
            return redirect()->route('admin.shipments.show', $shipment)
                ->with('error', __('Cannot cancel a delivered shipment'));
        }

        $shipment->update(['status' => 'cancelled']);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', __('Shipment cancelled successfully'));
    }
}
