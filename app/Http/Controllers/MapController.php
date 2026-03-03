<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Location;


class MapController extends Controller
{
    public function index()
    {
        $totalVehicles = \App\Models\Vehicle::count();

        // Count service notifications using Vehicle model methods
        $serviceAlertCount = 0;
        $vehicles = \App\Models\Vehicle::all();
        foreach ($vehicles as $v) {
            $serviceAlertCount += $v->getUrgentCount();
            // Also count warnings
            foreach ($v->getServiceStatus() as $s) {
                if ($s['status'] === 'warning') {
                    $serviceAlertCount++;
                }
            }
        }

        // Build device -> vehicle mapping for legend
        $deviceVehicleMap = [];
        foreach ($vehicles as $v) {
            if ($v->device_id) {
                $deviceVehicleMap[$v->device_id] = [
                    'name' => $v->name,
                    'type' => $v->type,
                    'plate' => $v->plate_number,
                    'odometer' => $v->current_odometer,
                ];
            }
        }

        return view('map', compact('totalVehicles', 'serviceAlertCount', 'deviceVehicleMap'));
    }

    public function devicesPage()
    {
        return view('devices');
    }

    public function historyPage()
    {
        return view('history');
    }

    public function latest()
    {
        $latestLocations = Location::select('locations.*')
            ->join(
                \Illuminate\Support\Facades\DB::raw('(SELECT device_id, MAX(created_at) as latest_at FROM locations GROUP BY device_id) as latest_locations'),
                function ($join) {
                    $join->on('locations.device_id', '=', 'latest_locations.device_id')
                        ->on('locations.created_at', '=', 'latest_locations.latest_at');
                }
            )
            ->get();

        return response()->json($latestLocations);
    }

    public function devices()
    {
        // Menggunakan waktu PHP mungkin tidak sinkron dengan waktu MySQL.
        // Kita bandingkan selisih waktu langsung di query database
        $devices = Location::select('device_id')
            ->selectRaw('MAX(created_at) as last_update')
            ->selectRaw('TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) as seconds_ago')
            ->groupBy('device_id')
            ->orderByDesc('last_update')
            ->get()
            ->map(function ($device) {
                // Jika data terakhir kurang dari atau sama dengan 15 detik yang lalu,
                // atau detik kembaliannya negatif (PHP lebih lambat dari MySQL), anggap Online
                $device->is_active = ($device->seconds_ago !== null && $device->seconds_ago <= 15) ? 1 : 0;
                return $device;
            });

        return response()->json($devices);
    }

    public function history($deviceId)
    {
        $sevenDaysAgo = now()->subDays(7);

        $history = Location::where('device_id', $deviceId)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        return response()->json($history);
    }

    public function stats()
    {
        $totalLocations = Location::count();
        $totalDevices = Location::distinct('device_id')->count('device_id');

        return response()->json([
            'total_locations' => $totalLocations,
            'total_devices' => $totalDevices,
        ]);
    }

    /**
     * Get recent trail/route data for all devices (last 200 points each)
     */
    public function deviceTrails()
    {
        $deviceIds = Location::distinct()->pluck('device_id');
        $trails = [];

        foreach ($deviceIds as $deviceId) {
            $points = Location::where('device_id', $deviceId)
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get(['latitude', 'longitude', 'created_at'])
                ->reverse()
                ->values();

            $trails[$deviceId] = $points->map(fn($p) => [
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
            ]);
        }

        return response()->json($trails);
    }
}
