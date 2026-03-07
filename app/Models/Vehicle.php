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

        $locations = Location::where('device_id', $this->device_id)
            ->orderBy('created_at', 'asc')
            ->select('latitude', 'longitude')
            ->get();

        if ($locations->count() < 2) {
            return (float) $this->current_odometer;
        }

        $totalDistance = 0;

        for ($i = 1; $i < $locations->count(); $i++) {
            $totalDistance += $this->haversine(
                $locations[$i - 1]->latitude,
                $locations[$i - 1]->longitude,
                $locations[$i]->latitude,
                $locations[$i]->longitude
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
     * Check if the linked GPS device is currently online (last update <= 15 seconds ago).
     */
    public function isDeviceOnline(): bool
    {
        if (!$this->device_id) {
            return false;
        }

        $lastLocation = Location::where('device_id', $this->device_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastLocation) {
            return false;
        }

        return $lastLocation->created_at->diffInSeconds(now()) <= 15;
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

        foreach ($schedules as $schedule) {
            $lastServiceKm = $this->getLastServiceOdometer($schedule->component);
            $kmSinceService = $this->current_odometer - $lastServiceKm;
            $kmRemaining = $schedule->interval_km - $kmSinceService;
            $progress = min(100, max(0, ($kmSinceService / $schedule->interval_km) * 100));

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
