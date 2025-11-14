<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{

    public function index(Request $request)
    {
        $query = Delivery::with(['trip.vehicle', 'trip.driver', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('trip', function ($q2) use ($search) {
                        $q2->where('trip_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('delivered_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivered_at', '<=', $request->to_date);
        }

        $deliveries = $query->latest('delivered_at')->paginate(15);

        // Statistics for dashboard
        $stats = [
            'total' => Delivery::count(),
            'this_month' => Delivery::thisMonth()->count(),
            'completed' => Delivery::completed()->count(),
            'pending' => Delivery::pending()->count(),
        ];

        return view('admin.deliveries.index', compact('deliveries', 'stats'));
    }

    public function create()
    {
        // Get only completed trips that don't have deliveries yet
        $trips = Trip::whereNotIn('id', function ($query) {
            $query->select('trip_id')->from('deliveries');
        })
            ->where('status', 'delivered')
            ->with(['vehicle', 'driver'])
            ->orderBy('end_date', 'desc')
            ->get();

        return view('admin.deliveries.create', compact('trips'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string',
            'delivered_at' => 'required|date',
            'signature' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'pod_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'delivery_remarks' => 'nullable|string',
            'delivery_status' => 'required|in:pending,partial,completed,failed',
        ]);

        // Check if delivery already exists for this trip
        $existingDelivery = Delivery::where('trip_id', $validated['trip_id'])->first();
        if ($existingDelivery) {
            return back()
                ->withInput()
                ->with('error', __('Delivery already exists for this trip.'));
        }

        // Upload signature if provided
        $signaturePath = null;
        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('signatures', 'public');
        }

        // Upload POD file if provided
        $podFilePath = null;
        if ($request->hasFile('pod_file')) {
            $podFilePath = $request->file('pod_file')->store('pod-files', 'public');
        }

        // Create delivery
        $delivery = Delivery::create([
            'trip_id' => $validated['trip_id'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'delivery_address' => $validated['delivery_address'],
            'delivered_at' => $validated['delivered_at'],
            'signature_path' => $signaturePath,
            'pod_file_path' => $podFilePath,
            'delivery_remarks' => $validated['delivery_remarks'],
            'delivery_status' => $validated['delivery_status'],
            'created_by' => Auth::id(),
        ]);

        // Update trip status to delivered (only if status is completed)
        if ($validated['delivery_status'] === 'completed') {
            $trip = Trip::find($validated['trip_id']);
            $trip->update(['status' => 'delivered']);

            // Update driver status to active
            $trip->driver->update(['status' => 'active']);
        }

        return redirect()->route('admin.deliveries.index')
            ->with('success', __('created_successfully'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['trip.vehicle', 'trip.driver', 'creator']);
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load(['trip.vehicle', 'trip.driver']);

        return view('admin.deliveries.edit', compact('delivery'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        // Validation
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string',
            'delivered_at' => 'required|date',
            'signature' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'pod_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'delivery_remarks' => 'nullable|string',
            'delivery_status' => 'required|in:pending,partial,completed,failed',
        ]);

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($delivery->signature_path) {
                Storage::disk('public')->delete($delivery->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        // Handle POD file upload
        if ($request->hasFile('pod_file')) {
            // Delete old POD file if exists
            if ($delivery->pod_file_path) {
                Storage::disk('public')->delete($delivery->pod_file_path);
            }
            $validated['pod_file_path'] = $request->file('pod_file')->store('pod-files', 'public');
        }

        // Update delivery
        $delivery->update($validated);

        // Update trip and driver status if delivery status changed to completed
        if ($validated['delivery_status'] === 'completed' && $delivery->trip->status !== 'delivered') {
            $delivery->trip->update(['status' => 'delivered']);
            $delivery->trip->driver->update(['status' => 'active']);
        }

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', __('updated_successfully'));
    }

    public function destroy(Delivery $delivery)
    {
        // Delete files if they exist
        if ($delivery->signature_path) {
            Storage::disk('public')->delete($delivery->signature_path);
        }
        if ($delivery->pod_file_path) {
            Storage::disk('public')->delete($delivery->pod_file_path);
        }

        // Reset trip status if needed
        $trip = $delivery->trip;
        if ($trip->status === 'delivered') {
            $trip->update(['status' => 'in_transit']);
        }

        $delivery->delete();

        return redirect()->route('admin.deliveries.index')
            ->with('success', __('deleted_successfully'));
    }

    /**
     * Download POD file
     */
    public function downloadPod(Delivery $delivery)
    {
        if (!$delivery->pod_file_path) {
            return back()->with('error', __('POD file not found'));
        }

        $filePath = storage_path('app/public/' . $delivery->pod_file_path);

        if (!file_exists($filePath)) {
            return back()->with('error', __('POD file not found'));
        }

        return response()->download($filePath);
    }

    /**
     * Download signature
     */
    public function downloadSignature(Delivery $delivery)
    {
        if (!$delivery->signature_path) {
            return back()->with('error', __('Signature file not found'));
        }

        $filePath = storage_path('app/public/' . $delivery->signature_path);

        if (!file_exists($filePath)) {
            return back()->with('error', __('Signature file not found'));
        }

        return response()->download($filePath);
    }
}
