<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use App\Models\ServiceRecordItem;
use App\Models\ServiceSchedule;

class ServiceController extends Controller
{
    public function notificationsPage()
    {
        $vehicles = Vehicle::whereNotNull('device_id')->orderByDesc('updated_at')->get();

        // Sync all GPS odometers
        foreach ($vehicles as $vehicle) {
            $vehicle->syncOdometerFromGps();
        }

        $vehicles = Vehicle::orderByDesc('updated_at')->get();

        // Collect all alerts grouped by vehicle
        $alerts = [];
        foreach ($vehicles as $vehicle) {
            $statuses = $vehicle->getServiceStatus();
            $vehicleAlerts = [];
            foreach ($statuses as $status) {
                if ($status['status'] === 'danger' || $status['status'] === 'warning') {
                    $vehicleAlerts[] = $status;
                }
            }
            if (!empty($vehicleAlerts)) {
                $alerts[] = [
                    'vehicle' => $vehicle,
                    'items' => $vehicleAlerts,
                ];
            }
        }

        return view('service.notifications', compact('alerts'));
    }

    public function vehiclesPage()
    {
        $vehicles = Vehicle::orderByDesc('updated_at')->get();

        // Sync odometer from GPS for all vehicles with linked devices
        foreach ($vehicles as $vehicle) {
            $vehicle->syncOdometerFromGps();
        }

        // Refresh after sync
        $vehicles = Vehicle::orderByDesc('updated_at')->get();
        return view('service.vehicles', compact('vehicles'));
    }

    public function vehicleDetail($id)
    {
        $vehicle = Vehicle::with(['serviceRecords.items'])->findOrFail($id);

        // Sync odometer from GPS before showing detail
        $vehicle->syncOdometerFromGps();
        $vehicle->refresh();

        $serviceStatus = $vehicle->getServiceStatus();
        $serviceRecords = $vehicle->serviceRecords()->with('items')->get();

        return view('service.vehicle-detail', compact('vehicle', 'serviceStatus', 'serviceRecords'));
    }

    public function createVehicle()
    {
        // Get available device_ids from locations table
        $devices = \App\Models\Location::select('device_id')
            ->groupBy('device_id')
            ->orderBy('device_id')
            ->pluck('device_id');

        return view('service.vehicle-form', compact('devices'));
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:motor,mobil',
            'plate_number' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:2099',
            'current_odometer' => 'nullable|integer|min:0',
            'device_id' => 'nullable|string|max:255',
        ]);

        // Default odometer to 0 if not provided
        $validated['current_odometer'] = $validated['current_odometer'] ?? 0;

        $vehicle = Vehicle::create($validated);

        // Auto-sync odometer from GPS if device is linked
        $vehicle->syncOdometerFromGps();

        return redirect('/vehicles')->with('success', 'Kendaraan berhasil ditambahkan!');
    }

    public function editVehicle($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Sync before editing
        $vehicle->syncOdometerFromGps();
        $vehicle->refresh();

        $devices = \App\Models\Location::select('device_id')
            ->groupBy('device_id')
            ->orderBy('device_id')
            ->pluck('device_id');

        return view('service.vehicle-form', compact('vehicle', 'devices'));
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:motor,mobil',
            'plate_number' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:2099',
            'current_odometer' => 'nullable|integer|min:0',
            'device_id' => 'nullable|string|max:255',
        ]);

        // If device_id changed, reset odometer to recalculate from GPS
        $oldDeviceId = $vehicle->device_id;
        $vehicle->update($validated);

        // Auto-sync from GPS if device is linked
        if ($vehicle->device_id) {
            $vehicle->syncOdometerFromGps();
        }

        return redirect("/vehicles/{$id}")->with('success', 'Kendaraan berhasil diperbarui!');
    }

    public function createServiceRecord($vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        // Sync odometer from GPS before showing form
        $vehicle->syncOdometerFromGps();
        $vehicle->refresh();

        $schedules = $vehicle->getServiceSchedules();

        return view('service.service-form', compact('vehicle', 'schedules'));
    }

    public function storeServiceRecord(Request $request, $vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $validated = $request->validate([
            'service_date' => 'required|date',
            'odometer_at_service' => 'required|integer|min:0',
            'workshop_name' => 'nullable|string|max:255',
            'technician_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.cost' => 'required|integer|min:0',
        ]);

        // Calculate total cost
        $totalCost = collect($validated['items'])->sum('cost');

        $record = ServiceRecord::create([
            'vehicle_id' => $vehicle->id,
            'service_date' => $validated['service_date'],
            'odometer_at_service' => $validated['odometer_at_service'],
            'workshop_name' => $validated['workshop_name'],
            'technician_name' => $validated['technician_name'],
            'notes' => $validated['notes'],
            'total_cost' => $totalCost,
        ]);

        foreach ($validated['items'] as $item) {
            ServiceRecordItem::create([
                'service_record_id' => $record->id,
                'component_name' => $item['component_name'],
                'description' => $item['description'] ?? null,
                'cost' => $item['cost'],
            ]);
        }

        // Update vehicle odometer if service odometer is higher
        if ($validated['odometer_at_service'] > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $validated['odometer_at_service']]);
        }

        return redirect("/vehicles/{$vehicleId}")->with('success', 'Riwayat service berhasil disimpan!');
    }

    public function deleteVehicle($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect('/vehicles')->with('success', 'Kendaraan berhasil dihapus!');
    }
}
