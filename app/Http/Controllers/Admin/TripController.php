<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\TripFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['vehicle', 'driver']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('trip_number', 'like', "%{$search}%")
                    ->orWhere('shipment_reference', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $trips = $query->latest()->paginate(15);

        return view('admin.trips.index', compact('trips'));
    }

    public function create()
    {
        // Get pending shipments for optional assignment
        $shipments = Shipment::where('status', 'pending')
            ->with('vehicleType')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active vehicles
        $vehicles = Vehicle::where('status', 'active')
            ->with('vehicleType')
            ->orderBy('vehicle_number')
            ->get();

        // Store ALL vehicles for JavaScript filtering
        $allVehicles = $vehicles;

        // Get only AVAILABLE drivers (active status, not on trip)
        $drivers = Driver::where('status', 'active')
            ->with('ownVehicle')
            ->orderBy('name')
            ->get();

        // Store ALL drivers for reference
        $allDrivers = $drivers;

        // Create a map of vehicle IDs to their owner driver IDs
        $vehicleOwners = Driver::where('driver_type', 'own_vehicle')
            ->whereNotNull('own_vehicle_id')
            ->pluck('id', 'own_vehicle_id')
            ->toArray();

        // Create a map of driver IDs to their own vehicle IDs
        $driverOwnVehicles = Driver::where('driver_type', 'own_vehicle')
            ->whereNotNull('own_vehicle_id')
            ->pluck('own_vehicle_id', 'id')
            ->toArray();

        // ===== VEHICLE AVAILABILITY: Check which vehicles are currently in use =====
        $vehiclesInUse = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->pluck('vehicle_id')
            ->toArray();

        // Get trip numbers for vehicles in use (for display)
        $vehicleTrips = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->get()
            ->pluck('trip_number', 'vehicle_id')
            ->toArray();

        // ===== DRIVER AVAILABILITY: Check which drivers are currently on trips =====
        $driversOnTrip = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->pluck('driver_id')
            ->toArray();

        // Get trip numbers for drivers on trips
        $driverTrips = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->get()
            ->pluck('trip_number', 'driver_id')
            ->toArray();

        return view('admin.trips.create', compact(
            'shipments',
            'vehicles',
            'drivers',
            'allVehicles',
            'allDrivers',
            'vehicleOwners',
            'driverOwnVehicles',
            'vehiclesInUse',
            'vehicleTrips',
            'driversOnTrip',
            'driverTrips'
        ));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'shipment_reference' => 'nullable|string|max:100',
            'has_multiple_locations' => 'required|boolean',
            'trip_instructions' => 'nullable|string',
            // Removed status from validation (we force it)
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',

            // Location fields - REVERSED LOGIC
            'pickup_location' => 'required|string|max:500',
            'drop_location' => 'required_if:has_multiple_locations,0|nullable|string|max:500',
        ]);

        $driver = Driver::find($validated['driver_id']);

        // ===== VEHICLE AVAILABILITY CHECK =====
        $vehicleInUse = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->where('vehicle_id', $validated['vehicle_id'])
            ->first();

        if ($vehicleInUse) {
            return back()
                ->withErrors(['vehicle_id' => __('This vehicle is already in use on trip: ' . $vehicleInUse->trip_number)])
                ->withInput();
        }

        // Check if driver has own_vehicle
        if ($driver->driver_type === 'own_vehicle' && $driver->own_vehicle_id) {
            if ($driver->own_vehicle_id != $validated['vehicle_id']) {
                return back()
                    ->withErrors(['driver_id' => __('This driver can only be assigned to their own vehicle.')])
                    ->withInput();
            }
        }

        // Check if selected vehicle belongs to another own-vehicle driver
        $vehicleOwner = Driver::where('driver_type', 'own_vehicle')
            ->where('own_vehicle_id', $validated['vehicle_id'])
            ->first();

        if ($vehicleOwner && $vehicleOwner->id != $validated['driver_id']) {
            return back()
                ->withErrors(['vehicle_id' => __('This vehicle is owned by another driver and cannot be assigned to a different driver.')])
                ->withInput();
        }

        // Check if driver is available
        if (!$driver->isAvailable()) {
            return back()
                ->withErrors(['driver_id' => __('This driver is not available. They have an active trip.')])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate trip number
            $lastTrip = Trip::latest('id')->first();
            $tripNumber = 'TRIP-' . str_pad(($lastTrip ? $lastTrip->id + 1 : 1), 6, '0', STR_PAD_LEFT);

            // FORCE STATUS = ASSIGNED
            $validated['status'] = 'assigned';

            // Create trip
            $trip = Trip::create([
                'trip_number' => $tripNumber,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'shipment_id' => $validated['shipment_id'] ?? null,
                'shipment_reference' => $validated['shipment_reference'] ?? null,
                'has_multiple_locations' => $validated['has_multiple_locations'],
                'pickup_location' => $validated['pickup_location'],

                // REVERSED LOGIC: single = save drop, multiple = null
                'drop_location' => !$validated['has_multiple_locations']
                    ? ($validated['drop_location'] ?? null)
                    : null,

                'trip_instructions' => $validated['trip_instructions'],
                'status' => 'assigned', // forced
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'created_by' => Auth::id(),
            ]);

            // ===== UPDATE SHIPMENT IF SELECTED =====
            if ($validated['shipment_id']) {
                $shipment = Shipment::find($validated['shipment_id']);
                if ($shipment) {
                    $shipment->update([
                        'vehicle_id' => $validated['vehicle_id'],
                        'driver_id' => $validated['driver_id'],
                        'status' => 'assigned', // Change from pending to in_progress
                    ]);
                    Log::info("Shipment #{$shipment->id} assigned to vehicle #{$validated['vehicle_id']} and driver #{$validated['driver_id']}");
                }
            }

            // Always set driver on trip because status is always assigned
            $driver->update(['status' => 'on_trip']);
            Log::info("Driver #{$driver->id} status changed to 'on_trip'");

            DB::commit();

            return redirect()
                ->route('admin.trips.index')
                ->with('success', __('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trip creation failed: ' . $e->getMessage());

            return back()->with('error', __('error') . ': ' . $e->getMessage())
                ->withInput();
        }
    }


    public function show(Trip $trip)
    {
        $trip->load(['vehicle.vehicleType', 'driver', 'creator', 'files.uploader', 'cost', 'delivery']);
        return view('admin.trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        // ===== CRITICAL: Prevent editing trips that are no longer pending =====
        if ($trip->status !== 'pending') {
            return redirect()->route('admin.trips.show', $trip)
                ->with('error', __('Cannot edit trip once it has been assigned. Trip status: ') . __($trip->status));
        }

        // Get pending shipments for optional assignment (exclude current if already linked)
        $shipments = Shipment::where('status', 'pending')
            ->when($trip->shipment_id, function ($query) use ($trip) {
                // Include current shipment even if not pending
                $query->orWhere('id', $trip->shipment_id);
            })
            ->with('vehicleType')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active vehicles + current trip's vehicle
        $vehicles = Vehicle::where(function ($query) use ($trip) {
            $query->where('status', 'active')
                ->orWhere('id', $trip->vehicle_id);
        })
            ->with('vehicleType')
            ->orderBy('vehicle_number')
            ->get();

        // Store ALL vehicles for JavaScript filtering
        $allVehicles = $vehicles;

        // Get active drivers + current trip's driver
        $drivers = Driver::where(function ($query) use ($trip) {
            $query->where('status', 'active')
                ->orWhere('id', $trip->driver_id);
        })
            ->with('ownVehicle')
            ->orderBy('name')
            ->get();

        // Store ALL drivers for reference
        $allDrivers = $drivers;

        // Create a map of vehicle IDs to their owner driver IDs
        $vehicleOwners = Driver::where('driver_type', 'own_vehicle')
            ->whereNotNull('own_vehicle_id')
            ->pluck('id', 'own_vehicle_id')
            ->toArray();

        // Create a map of driver IDs to their own vehicle IDs
        $driverOwnVehicles = Driver::where('driver_type', 'own_vehicle')
            ->whereNotNull('own_vehicle_id')
            ->pluck('own_vehicle_id', 'id')
            ->toArray();

        // ===== VEHICLE AVAILABILITY: Check which vehicles are currently in use =====
        // Exclude current trip's vehicle from "in use" list
        $vehiclesInUse = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->where('id', '!=', $trip->id)
            ->pluck('vehicle_id')
            ->toArray();

        // Get trip numbers for vehicles in use
        $vehicleTrips = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->where('id', '!=', $trip->id)
            ->get()
            ->pluck('trip_number', 'vehicle_id')
            ->toArray();

        // ===== DRIVER AVAILABILITY: Check which drivers are currently on trips =====
        $driversOnTrip = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->where('id', '!=', $trip->id)
            ->pluck('driver_id')
            ->toArray();

        // Get trip numbers for drivers on trips
        $driverTrips = Trip::whereIn('status', ['assigned', 'in_transit'])
            ->where('id', '!=', $trip->id)
            ->get()
            ->pluck('trip_number', 'driver_id')
            ->toArray();

        return view('admin.trips.edit', compact(
            'trip',
            'shipments',
            'vehicles',
            'drivers',
            'allVehicles',
            'allDrivers',
            'vehicleOwners',
            'driverOwnVehicles',
            'vehiclesInUse',
            'vehicleTrips',
            'driversOnTrip',
            'driverTrips'
        ));
    }

    public function update(Request $request, Trip $trip)
    {
        // ===== CRITICAL: Prevent updating non-pending trips =====
        if ($trip->status !== 'pending') {
            return redirect()->route('admin.trips.show', $trip)
                ->with('error', __('Cannot update trip once it has been assigned. Trip status: ') . __($trip->status));
        }

        // Validation
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'shipment_reference' => 'nullable|string|max:100',
            'has_multiple_locations' => 'required|boolean',
            'trip_instructions' => 'nullable|string',
            'status' => 'required|in:pending,assigned,in_transit,delivered,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',

            // Location fields - REVERSED LOGIC
            // Single location (0) = pickup + drop (drop required)
            // Multiple locations (1) = pickup only (drop not required)
            'pickup_location' => 'required|string|max:500',
            'drop_location' => 'required_if:has_multiple_locations,0|nullable|string|max:500',
        ]);

        $driver = Driver::find($validated['driver_id']);
        $oldDriverId = $trip->driver_id;
        $oldStatus = $trip->status;
        $oldVehicleId = $trip->vehicle_id;
        $oldShipmentId = $trip->shipment_id;

        // ===== VEHICLE AVAILABILITY CHECK =====
        // Only check if changing vehicle AND status is active
        if ($oldVehicleId != $validated['vehicle_id'] && in_array($validated['status'], ['assigned', 'in_transit'])) {
            $vehicleInUse = Trip::whereIn('status', ['assigned', 'in_transit'])
                ->where('vehicle_id', $validated['vehicle_id'])
                ->where('id', '!=', $trip->id)
                ->first();

            if ($vehicleInUse) {
                return back()->withErrors(['vehicle_id' => __('This vehicle is already in use on trip: ' . $vehicleInUse->trip_number)])
                    ->withInput();
            }
        }

        // Check if driver has own_vehicle - must use only their own vehicle
        if ($driver->driver_type === 'own_vehicle' && $driver->own_vehicle_id) {
            if ($driver->own_vehicle_id != $validated['vehicle_id']) {
                return back()->withErrors(['driver_id' => __('This driver can only be assigned to their own vehicle.')])
                    ->withInput();
            }
        }

        // Check if selected vehicle is someone else's own vehicle
        $vehicleOwner = Driver::where('driver_type', 'own_vehicle')
            ->where('own_vehicle_id', $validated['vehicle_id'])
            ->first();

        if ($vehicleOwner && $vehicleOwner->id != $validated['driver_id']) {
            return back()->withErrors(['vehicle_id' => __('This vehicle is owned by another driver and cannot be assigned to a different driver.')])
                ->withInput();
        }

        // Check driver availability ONLY IF changing to a different driver
        if ($oldDriverId != $validated['driver_id']) {
            if (!$driver->isAvailable()) {
                return back()->withErrors(['driver_id' => __('This driver is not available. They have an active trip.')])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Update trip
            $trip->update([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'shipment_id' => $validated['shipment_id'] ?? null,
                'shipment_reference' => $validated['shipment_reference'] ?? null,
                'has_multiple_locations' => $validated['has_multiple_locations'],
                'pickup_location' => $validated['pickup_location'],
                // REVERSED LOGIC: single location (0) = save drop, multiple (1) = no drop
                'drop_location' => !$validated['has_multiple_locations'] ? ($validated['drop_location'] ?? null) : null,
                'trip_instructions' => $validated['trip_instructions'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // ===== UPDATE SHIPMENT STATUS BASED ON TRIP STATUS =====
            // Handle old shipment (if it was changed or removed)
            if ($oldShipmentId && $oldShipmentId != $validated['shipment_id']) {
                $oldShipment = Shipment::find($oldShipmentId);
                if ($oldShipment) {
                    // Reset old shipment back to pending
                    $oldShipment->update([
                        'vehicle_id' => null,
                        'driver_id' => null,
                        'status' => 'pending',
                    ]);
                    Log::info("Shipment #{$oldShipmentId} unassigned and reset to pending");
                }
            }

            // Handle new or existing shipment
            if ($validated['shipment_id']) {
                $shipment = Shipment::find($validated['shipment_id']);
                if ($shipment) {
                    $shipmentData = [
                        'vehicle_id' => $validated['vehicle_id'],
                        'driver_id' => $validated['driver_id'],
                    ];

                    // Map trip status to shipment status
                    switch ($validated['status']) {
                        case 'assigned':
                        case 'in_transit':
                            $shipmentData['status'] = 'assigned';
                            break;
                        case 'delivered':
                            $shipmentData['status'] = 'delivered';
                            break;
                        case 'cancelled':
                            $shipmentData['status'] = 'cancelled';
                            $shipmentData['vehicle_id'] = null;
                            $shipmentData['driver_id'] = null;
                            break;
                        case 'pending':
                            $shipmentData['status'] = 'pending';
                            $shipmentData['vehicle_id'] = null;
                            $shipmentData['driver_id'] = null;
                            break;
                        default:
                            $shipmentData['status'] = 'assigned';
                    }

                    $shipment->update($shipmentData);
                    Log::info("Shipment #{$shipment->id} updated: status={$shipmentData['status']}, vehicle={$shipmentData['vehicle_id']}, driver={$shipmentData['driver_id']}");
                }
            }

            // ===== Handle driver status changes when driver is changed =====
            if ($oldDriverId != $validated['driver_id']) {
                // ALWAYS free up the old driver
                if ($oldDriverId) {
                    $oldDriver = Driver::find($oldDriverId);
                    if ($oldDriver) {
                        $oldDriver->update(['status' => 'active']);
                        Log::info("Driver #{$oldDriverId} status changed to 'active' (replaced)");
                    }
                }

                // Set new driver status based on trip status
                if (in_array($validated['status'], ['assigned', 'in_transit'])) {
                    $driver->update(['status' => 'on_trip']);
                    Log::info("Driver #{$validated['driver_id']} status changed to 'on_trip'");
                } else {
                    $driver->update(['status' => 'active']);
                    Log::info("Driver #{$validated['driver_id']} kept as 'active'");
                }
            }
            // Handle driver status changes when ONLY trip status changes
            elseif ($oldStatus != $validated['status']) {
                if (in_array($validated['status'], ['assigned', 'in_transit'])) {
                    $driver->update(['status' => 'on_trip']);
                    Log::info("Driver #{$driver->id} status changed to 'on_trip' (status change)");
                } elseif (in_array($validated['status'], ['delivered', 'cancelled'])) {
                    $driver->update(['status' => 'active']);
                    Log::info("Driver #{$driver->id} status changed to 'active' (status change)");
                }
            }

            DB::commit();

            return redirect()->route('admin.trips.index')
                ->with('success', __('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trip update failed: ' . $e->getMessage());
            return back()->with('error', __('error') . ': ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Trip $trip)
    {
        // Only allow deletion of pending or cancelled trips
        if (!in_array($trip->status, ['pending', 'cancelled'])) {
            return back()->with('error', __('Cannot delete trip that is in progress or delivered.'));
        }

        DB::beginTransaction();
        try {
            // Reset shipment if linked
            if ($trip->shipment_id) {
                $shipment = Shipment::find($trip->shipment_id);
                if ($shipment) {
                    $shipment->update([
                        'vehicle_id' => null,
                        'driver_id' => null,
                        'status' => 'pending',
                    ]);
                    Log::info("Shipment #{$trip->shipment_id} reset to pending (trip deleted)");
                }
            }

            // Free up driver if assigned
            if ($trip->driver_id) {
                $driver = Driver::find($trip->driver_id);
                if ($driver && $driver->status === 'on_trip') {
                    $driver->update(['status' => 'active']);
                    Log::info("Driver #{$trip->driver_id} freed (trip deleted)");
                }
            }

            // Delete associated files from storage
            foreach ($trip->files as $file) {
                Storage::delete($file->file_path);
            }

            $trip->delete();

            DB::commit();

            return redirect()->route('admin.trips.index')
                ->with('success', __('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('error') . ': ' . $e->getMessage());
        }
    }

    public function uploadFile(Request $request, Trip $trip)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('trip-files', $fileName, 'public');

            TripFile::create([
                'trip_id' => $trip->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            return back()->with('success', __('File uploaded successfully.'));
        }

        return back()->with('error', __('No file uploaded.'));
    }
}
