<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleType::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('capacity', 'like', "%{$search}%");
            });
        }

        $vehicleTypes = $query->latest()->paginate(15);

        return view('admin.vehicle-types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('admin.vehicle-types.create');
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        VehicleType::create($validated);

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', __('created_successfully'));
    }

    public function edit(VehicleType $vehicleType)
    {
        return view('admin.vehicle-types.edit', compact('vehicleType'));
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $vehicleType->update($validated);

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', __('updated_successfully'));
    }

    public function destroy(VehicleType $vehicleType)
    {
        // Check if vehicle type is in use
        if ($vehicleType->vehicles()->exists()) {
            return back()->with('error', __('Cannot delete vehicle type that has vehicles assigned to it.'));
        }

        $vehicleType->delete();

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', __('deleted_successfully'));
    }
}
