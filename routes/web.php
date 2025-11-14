<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\CostController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ShipmentController;

// Language switcher (no authentication required)
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'es', 'hi', 'ta'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// Guest routes (only accessible when NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes (all admin routes)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // VEHICLE TYPES - Requires vehicle_types permissions
    // ============================================
    Route::middleware(['permission:vehicle_types.view'])->group(function () {
        Route::get('/vehicle-types', [VehicleTypeController::class, 'index'])->name('vehicle-types.index');
    });

    Route::middleware(['permission:vehicle_types.create'])->group(function () {
        Route::get('/vehicle-types/create', [VehicleTypeController::class, 'create'])->name('vehicle-types.create');
        Route::post('/vehicle-types', [VehicleTypeController::class, 'store'])->name('vehicle-types.store');
    });

    Route::middleware(['permission:vehicle_types.edit'])->group(function () {
        Route::get('/vehicle-types/{vehicleType}/edit', [VehicleTypeController::class, 'edit'])->name('vehicle-types.edit');
        Route::put('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'update'])->name('vehicle-types.update');
    });

    Route::middleware(['permission:vehicle_types.delete'])->group(function () {
        Route::delete('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'destroy'])->name('vehicle-types.destroy');
    });

    // ============================================
    // VEHICLES - Requires vehicles permissions
    // ============================================
    Route::middleware(['permission:vehicles.view'])->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    });

    Route::middleware(['permission:vehicles.create'])->group(function () {
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    });

    Route::middleware(['permission:vehicles.edit'])->group(function () {
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    });

    Route::middleware(['permission:vehicles.delete'])->group(function () {
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // ============================================
    // DRIVERS - Requires drivers permissions
    // ============================================
    Route::middleware(['permission:drivers.view'])->group(function () {
        Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('/drivers/check-availability/{driver}', [DriverController::class, 'checkAvailability'])->name('drivers.check-availability');
    });

    Route::middleware(['permission:drivers.create'])->group(function () {
        Route::get('/drivers/create', [DriverController::class, 'create'])->name('drivers.create');
        Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
    });

    Route::middleware(['permission:drivers.edit'])->group(function () {
        Route::get('/drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
    });

    Route::middleware(['permission:drivers.delete'])->group(function () {
        Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');
    });

    // ============================================
    // TRIPS - Requires trips permissions
    // FIXED: Specific routes BEFORE parameterized routes
    // ============================================
    Route::middleware(['permission:trips.create'])->group(function () {
        Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create'); // ← MOVED UP
        Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
        Route::post('/trips/{trip}/upload-file', [TripController::class, 'uploadFile'])->name('trips.upload-file');
    });

    Route::middleware(['permission:trips.edit'])->group(function () {
        Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
        Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    });

    Route::middleware(['permission:trips.view'])->group(function () {
        Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
        Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show'); // ← MOVED DOWN
    });

    Route::middleware(['permission:trips.delete'])->group(function () {
        Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
    });

    // ============================================
    // DELIVERIES - Requires deliveries permissions
    // FIXED: Specific routes BEFORE parameterized routes
    // ============================================
    Route::middleware(['permission:deliveries.create'])->group(function () {
        Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create'); // ← MOVED UP
        Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    });

    Route::middleware(['permission:deliveries.edit'])->group(function () {
        Route::get('/deliveries/{delivery}/edit', [DeliveryController::class, 'edit'])->name('deliveries.edit');
        Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');
    });

    Route::middleware(['permission:deliveries.view'])->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show'); // ← MOVED DOWN
        Route::get('/deliveries/{delivery}/download-pod', [DeliveryController::class, 'downloadPod'])->name('deliveries.download-pod');
        Route::get('/deliveries/{delivery}/download-signature', [DeliveryController::class, 'downloadSignature'])->name('deliveries.download-signature');
    });

    Route::middleware(['permission:deliveries.delete'])->group(function () {
        Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('deliveries.destroy');
    });

    // ============================================
    // SHIPMENTS - Requires shipments permissions
    // FIXED: Specific routes BEFORE parameterized routes
    // ============================================
    Route::middleware(['permission:shipments.create'])->group(function () {
        Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create'); // ← MOVED UP
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    });

    Route::middleware(['permission:shipments.edit'])->group(function () {
        Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::patch('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
    });

    Route::middleware(['permission:shipments.assign'])->group(function () {
        Route::get('/shipments/{shipment}/assign-vehicle', [ShipmentController::class, 'assignVehicle'])->name('shipments.assign-vehicle');
        Route::post('/shipments/{shipment}/store-assignment', [ShipmentController::class, 'storeAssignment'])->name('shipments.store-assignment');
    });

    Route::middleware(['permission:shipments.view'])->group(function () {
        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show'); // ← MOVED DOWN
    });

    Route::middleware(['permission:shipments.delete'])->group(function () {
        Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');
    });

    // ============================================
    // COSTS - Uses trips permissions (costs are part of trips)
    // ============================================
    Route::middleware(['permission:trips.view'])->group(function () {
        Route::get('/costs', [CostController::class, 'index'])->name('costs.index');
    });

    Route::middleware(['permission:trips.create'])->group(function () {
        Route::post('/costs', [CostController::class, 'store'])->name('costs.store');
    });

    Route::middleware(['permission:trips.edit'])->group(function () {
        Route::get('/costs/{cost}/edit', [CostController::class, 'edit'])->name('costs.edit');
        Route::put('/costs/{cost}', [CostController::class, 'update'])->name('costs.update');
    });

    // ============================================
    // USERS - Requires users permissions
    // ============================================
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware(['permission:users.edit'])->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::middleware(['permission:users.delete'])->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ============================================
    // ROLES & PERMISSIONS - Requires roles permissions
    // ============================================
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware(['permission:roles.create'])->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware(['permission:roles.edit'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware(['permission:roles.delete'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});
