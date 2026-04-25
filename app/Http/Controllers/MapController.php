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
        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) return response()->json([]);

        $lines = file($filePath);
        $latestLocations = [];
        $devicesLastSeen = [];

        // Parse from end to get latest for each device
        foreach (array_reverse($lines) as $line) {
            $data = $this->parseLine($line);
            if (!$data) continue;

            $deviceId = $data['device_id'];
            if (!isset($devicesLastSeen[$deviceId])) {
                $devicesLastSeen[$deviceId] = true;
                $latestLocations[] = $data;
            }
        }

        return response()->json($latestLocations);
    }

    public function devices()
    {
        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) return response()->json([]);

        // Check if file was modified in the last 5 seconds (device is actively sending data)
        $fileModifiedAt = filemtime($filePath);
        $fileSecondsAgo = time() - $fileModifiedAt;
        $isDeviceOnline = ($fileSecondsAgo <= 5);

        $lines = file($filePath);
        
        // Collect all points per device (in order) for distance calculation
        $devicePoints = [];
        foreach ($lines as $line) {
            $data = $this->parseLine($line);
            if (!$data) continue;
            $deviceId = $data['device_id'];
            if (!isset($devicePoints[$deviceId])) $devicePoints[$deviceId] = [];
            $devicePoints[$deviceId][] = $data;
        }

        $devices = [];
        foreach ($devicePoints as $deviceId => $points) {
            $lastPoint = end($points);
            $lastUpdate = $lastPoint['created_at'] ?? now()->toIso8601String();

            // Calculate total distance using Haversine
            $totalDistance = 0;
            for ($i = 1; $i < count($points); $i++) {
                $totalDistance += $this->haversine(
                    (float)$points[$i - 1]['latitude'],
                    (float)$points[$i - 1]['longitude'],
                    (float)$points[$i]['latitude'],
                    (float)$points[$i]['longitude']
                );
            }

            // Estimate speed from last 2 points (km/h)
            $estimatedSpeed = 0;
            $count = count($points);
            if ($count >= 2) {
                $p1 = $points[$count - 2];
                $p2 = $points[$count - 1];
                $dist = $this->haversine(
                    (float)$p1['latitude'], (float)$p1['longitude'],
                    (float)$p2['latitude'], (float)$p2['longitude']
                );
                $t1 = isset($p1['created_at']) ? strtotime($p1['created_at']) : 0;
                $t2 = isset($p2['created_at']) ? strtotime($p2['created_at']) : 0;
                $timeDiff = $t2 - $t1;
                if ($timeDiff > 0) {
                    $estimatedSpeed = ($dist / $timeDiff) * 3600; // km/h
                }
            }

            $devices[$deviceId] = [
                'device_id' => $deviceId,
                'last_update' => $lastUpdate,
                'seconds_ago' => $fileSecondsAgo,
                'is_active' => $isDeviceOnline ? 1 : 0,
                'total_distance_km' => round($totalDistance, 3),
                'estimated_speed' => round($estimatedSpeed, 1),
            ];
        }

        return response()->json(array_values($devices));
    }

    public function history($deviceId)
    {
        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) return response()->json([]);

        $lines = file($filePath);
        $history = [];

        foreach (array_reverse($lines) as $line) {
            $data = $this->parseLine($line);
            if (!$data || ($data['device_id'] ?? '') !== $deviceId) continue;
            
            $history[] = $data;
            if (count($history) >= 500) break;
        }

        return response()->json($history);
    }

    public function stats()
    {
        $filePath = storage_path('gps.txt');
        $totalLines = file_exists($filePath) ? count(file($filePath)) : 0;
        
        $devices = [];
        if ($totalLines > 0) {
            foreach (file($filePath) as $line) {
                $data = $this->parseLine($line);
                if ($data) {
                    $devices[$data['device_id']] = true;
                }
            }
        }

        return response()->json([
            'total_locations' => $totalLines,
            'total_devices' => count($devices),
        ]);
    }

    /**
     * Get recent trail/route data for all devices (last 200 points each)
     */
    public function deviceTrails()
    {
        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) return response()->json([]);

        $lines = file($filePath);
        $trails = [];

        foreach ($lines as $line) {
            $data = $this->parseLine($line);
            if (!$data) continue;

            $deviceId = $data['device_id'];
            if (!isset($trails[$deviceId])) $trails[$deviceId] = [];
            
            $trails[$deviceId][] = [
                'lat' => (float)$data['latitude'],
                'lng' => (float)$data['longitude']
            ];

            if (count($trails[$deviceId]) > 200) {
                array_shift($trails[$deviceId]);
            }
        }

        return response()->json($trails);
    }

    private function parseLine($line)
    {
        $line = trim($line);
        if (empty($line)) return null;

        // Try JSON first
        $data = json_decode($line, true);
        if ($data && isset($data['device_id'], $data['latitude'], $data['longitude'])) {
            return $data;
        }

        // Try legacy format 2: [2026-03-15 01:17:13] Device: IOT-DEV-01, Lat: -6.152074, Lng: 107.248421
        if (preg_match('/\[(.*?)\] Device: (.*?), Lat: (.*?), Lng: (.*?)$/', $line, $matches)) {
            return [
                'device_id' => $matches[2],
                'latitude' => (float)$matches[3],
                'longitude' => (float)$matches[4],
                'created_at' => $matches[1]
            ];
        }

        // Try legacy format 1: Lat: -6.152049 Lng: 107.248489
        if (preg_match('/Lat: (.*?) Lng: (.*?)$/', $line, $matches)) {
            return [
                'device_id' => 'IOT-DEV-01', // Default for oldest logs
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2],
                'created_at' => null
            ];
        }

        return null;
    }

    /**
     * Haversine formula: calculate distance (km) between two lat/lng points.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
