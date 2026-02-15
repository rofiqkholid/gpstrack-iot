<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use Carbon\Carbon;

class SimulateGps extends Command
{
    protected $signature = 'simulate:gps
                            {device_id=DEVICE_001 : Device ID to simulate}
                            {--km=3000 : Total kilometers to simulate}
                            {--days=30 : Spread data over how many days}
                            {--clear : Clear existing location data for this device first}';

    protected $description = 'Simulate GPS tracking data for testing service feature. Generates realistic route data.';

    /**
     * Predefined routes in Indonesia (lat,lng waypoints).
     * The simulator will travel along these routes in a loop.
     */
    private array $routes = [
        // Jakarta - Bekasi - Karawang - Subang - Bandung loop (~200km)
        ['lat' => -6.2088, 'lng' => 106.8456],  // Jakarta (Monas)
        ['lat' => -6.2383, 'lng' => 106.9756],  // Bekasi
        ['lat' => -6.3067, 'lng' => 107.2964],  // Karawang
        ['lat' => -6.5706, 'lng' => 107.7620],  // Subang
        ['lat' => -6.9175, 'lng' => 107.6191],  // Bandung
        ['lat' => -6.7000, 'lng' => 107.0000],  // Purwakarta
        ['lat' => -6.4000, 'lng' => 106.9500],  // Cikarang
        ['lat' => -6.2088, 'lng' => 106.8456],  // Kembali ke Jakarta
    ];

    public function handle()
    {
        $deviceId = $this->argument('device_id');
        $targetKm = (int) $this->option('km');
        $days = (int) $this->option('days');

        if ($this->option('clear')) {
            $deleted = Location::where('device_id', $deviceId)->delete();
            $this->info("Cleared {$deleted} existing locations for {$deviceId}");
        }

        $this->info("=== GPS Simulator ===");
        $this->info("Device: {$deviceId}");
        $this->info("Target: {$targetKm} KM over {$days} days");
        $this->newLine();

        $totalDistance = 0;
        $pointCount = 0;
        $startDate = Carbon::now()->subDays($days);
        $routeIndex = 0;
        $batch = [];
        $batchSize = 500;

        // Start position
        $currentLat = $this->routes[0]['lat'];
        $currentLng = $this->routes[0]['lng'];
        $routeIndex = 1;

        $bar = $this->output->createProgressBar($targetKm);
        $bar->setFormat(' %current%/%max% KM [%bar%] %percent:3s%% | %elapsed:6s% | Points: %message%');
        $bar->setMessage('0');
        $bar->start();

        while ($totalDistance < $targetKm) {
            // Target next waypoint
            $targetLat = $this->routes[$routeIndex]['lat'];
            $targetLng = $this->routes[$routeIndex]['lng'];

            // Move toward target in small steps (~500m per step)
            $stepSize = 0.005; // roughly 500m
            $dLat = $targetLat - $currentLat;
            $dLng = $targetLng - $currentLng;
            $dist = sqrt($dLat * $dLat + $dLng * $dLng);

            if ($dist < $stepSize) {
                // Reached waypoint, go to next
                $routeIndex = ($routeIndex + 1) % count($this->routes);
                continue;
            }

            // Normalize and step
            $newLat = $currentLat + ($dLat / $dist) * $stepSize;
            $newLng = $currentLng + ($dLng / $dist) * $stepSize;

            // Add some randomness (road variation)
            $newLat += (mt_rand(-10, 10) / 100000);
            $newLng += (mt_rand(-10, 10) / 100000);

            // Calculate distance (Haversine)
            $segmentKm = $this->haversine($currentLat, $currentLng, $newLat, $newLng);
            $totalDistance += $segmentKm;

            // Calculate timestamp spread over the days
            $progress = min(1, $totalDistance / $targetKm);
            $timestamp = $startDate->copy()->addSeconds((int)($progress * $days * 86400));

            // Speed between 20-60 km/h
            $speed = round(mt_rand(2000, 6000) / 100, 2);

            $batch[] = [
                'device_id' => $deviceId,
                'latitude' => round($newLat, 6),
                'longitude' => round($newLng, 6),
                'speed' => $speed,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $currentLat = $newLat;
            $currentLng = $newLng;
            $pointCount++;

            // Insert in batches
            if (count($batch) >= $batchSize) {
                Location::insert($batch);
                $batch = [];
                $bar->setProgress(min($targetKm, (int) $totalDistance));
                $bar->setMessage((string) $pointCount);
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            Location::insert($batch);
        }

        $bar->setProgress($targetKm);
        $bar->setMessage((string) $pointCount);
        $bar->finish();

        $this->newLine(2);
        $this->info("✅ Simulasi selesai!");
        $this->table(
            ['Item', 'Value'],
            [
                ['Device ID', $deviceId],
                ['Total Points', number_format($pointCount)],
                ['Total Distance', number_format($totalDistance, 1) . ' KM'],
                ['Date Range', $startDate->format('d M Y') . ' - ' . Carbon::now()->format('d M Y')],
            ]
        );

        $this->newLine();
        $this->info("Sekarang buka http://localhost:8000/vehicles untuk cek odometer GPS!");
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
