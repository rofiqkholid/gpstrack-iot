<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'type',
        'plate_number',
        'brand',
        'model',
        'year',
        'current_odometer',
    ];

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class)->orderByDesc('service_date');
    }

    public function getServiceSchedules()
    {
        return ServiceSchedule::where('vehicle_type', $this->type)->orderBy('interval_km')->get();
    }

    /**
     * Calculate total distance traveled from GPS location data using Haversine formula.
     * Returns distance in kilometers.
     */
    public function calculateGpsDistance(): float
    {
        if (!$this->device_id) {
            return (float) $this->current_odometer;
        }

        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) {
            return (float) $this->current_odometer;
        }

        $lines = file($filePath);
        $points = [];

        foreach ($lines as $line) {
            $data = $this->parseGpsLine($line);
            if ($data && $data['device_id'] === $this->device_id) {
                $points[] = $data;
            }
        }

        if (count($points) < 2) {
            return (float) $this->current_odometer;
        }

        $totalDistance = 0;
        for ($i = 1; $i < count($points); $i++) {
            $totalDistance += $this->haversine(
                (float)$points[$i - 1]['latitude'],
                (float)$points[$i - 1]['longitude'],
                (float)$points[$i]['latitude'],
                (float)$points[$i]['longitude']
            );
        }

        return round($totalDistance, 2);
    }

    /**
     * Haversine formula to calculate distance between two lat/lng points.
     * Returns distance in kilometers.
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

    /**
     * Sync current_odometer from GPS distance data.
     * Always replaces odometer with GPS distance when a device is linked.
     */
    public function syncOdometerFromGps(): void
    {
        if (!$this->device_id) {
            return;
        }

        $gpsDistance = $this->calculateGpsDistance();

        // GPS distance IS the odometer when device is linked
        $newOdometer = (int) round($gpsDistance);
        if ($newOdometer !== (int) $this->current_odometer) {
            $this->update(['current_odometer' => $newOdometer]);
        }
    }

    /**
     * Check if the vehicle has GPS data linked.
     */
    public function hasGps(): bool
    {
        return !empty($this->device_id);
    }

    /**
     * Check if the linked GPS device is currently online (file updated within last 5 seconds).
     */
    public function isDeviceOnline(): bool
    {
        if (!$this->device_id) {
            return false;
        }

        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) {
            return false;
        }

        // Check if file was modified in the last 5 seconds (device is actively sending data)
        $fileModifiedAt = filemtime($filePath);
        $fileSecondsAgo = time() - $fileModifiedAt;

        return $fileSecondsAgo <= 5;
    }

    /**
     * Get GPS statistics: total distance (km) and estimated speed (km/h).
     */
    public function getGpsStats(): array
    {
        $stats = ['total_distance_km' => 0, 'estimated_speed' => 0];

        if (!$this->device_id) {
            return $stats;
        }

        $filePath = storage_path('gps.txt');
        if (!file_exists($filePath)) {
            return $stats;
        }

        $lines = file($filePath);
        $points = [];

        foreach ($lines as $line) {
            $data = $this->parseGpsLine($line);
            if ($data && $data['device_id'] === $this->device_id) {
                $points[] = $data;
            }
        }

        if (count($points) < 2) {
            return $stats;
        }

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
            $estimatedSpeed = ($dist / $timeDiff) * 3600;
        }

        $stats['total_distance_km'] = round($totalDistance, 3);
        $stats['estimated_speed'] = round($estimatedSpeed, 1);

        return $stats;
    }

    private function parseGpsLine($line)
    {
        $line = trim($line);
        if (empty($line)) return null;

        $data = json_decode($line, true);
        if ($data && isset($data['device_id'], $data['latitude'], $data['longitude'])) {
            return $data;
        }

        if (preg_match('/\[(.*?)\] Device: (.*?), Lat: (.*?), Lng: (.*?)$/', $line, $matches)) {
            return [
                'device_id' => $matches[2],
                'latitude' => $matches[3],
                'longitude' => $matches[4],
                'created_at' => $matches[1]
            ];
        }

        if (preg_match('/Lat: (.*?) Lng: (.*?)$/', $line, $matches)) {
            return [
                'device_id' => 'IOT-DEV-01',
                'latitude' => $matches[1],
                'longitude' => $matches[2],
                'created_at' => null
            ];
        }

        return null;
    }

    /**
     * Get the last service odometer for a specific component.
     */
    public function getLastServiceOdometer(string $componentName): int
    {
        $lastRecord = $this->serviceRecords()
            ->whereHas('items', function ($q) use ($componentName) {
                $q->where('component_name', $componentName);
            })
            ->first();

        return $lastRecord ? $lastRecord->odometer_at_service : 0;
    }

    /**
     * Get service status for all scheduled components.
     * Returns array with component info, progress, and status color.
     */
    public function getServiceStatus(): array
    {
        $schedules = $this->getServiceSchedules();
        $statuses = [];

        // Gunakan jarak GPS jika terhubung
        $currentKm = $this->hasGps() ? round($this->getGpsStats()['total_distance_km']) : $this->current_odometer;

        foreach ($schedules as $schedule) {
            $lastServiceKm = $this->getLastServiceOdometer($schedule->component);
            $kmSinceService = max(0, $currentKm - $lastServiceKm);
            $kmRemaining = $schedule->interval_km - $kmSinceService;
            $progress = $schedule->interval_km > 0 ? min(100, max(0, ($kmSinceService / $schedule->interval_km) * 100)) : 100;

            if ($kmRemaining <= 0) {
                $status = 'danger';
            } elseif ($progress >= 75) {
                $status = 'warning';
            } else {
                $status = 'success';
            }

            $statuses[] = [
                'component' => $schedule->component,
                'interval_km' => $schedule->interval_km,
                'last_service_km' => $lastServiceKm,
                'km_since_service' => $kmSinceService,
                'km_remaining' => $kmRemaining,
                'progress' => round($progress),
                'status' => $status,
                'description' => $schedule->description,
            ];
        }

        return $statuses;
    }

    /**
     * Check if any component needs service.
     */
    public function needsService(): bool
    {
        foreach ($this->getServiceStatus() as $s) {
            if ($s['status'] === 'danger' || $s['status'] === 'warning') {
                return true;
            }
        }
        return false;
    }

    public function getUrgentCount(): int
    {
        $count = 0;
        foreach ($this->getServiceStatus() as $s) {
            if ($s['status'] === 'danger') {
                $count++;
            }
        }
        return $count;
    }
}
