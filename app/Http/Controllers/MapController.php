<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Location;


class MapController extends Controller
{
    public function index()
    {
        return view('map');
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
        $fiveMinutesAgo = now()->subMinutes(5)->toDateTimeString();

        $devices = Location::select('device_id')
            ->selectRaw('MAX(created_at) as last_update')
            ->groupBy('device_id')
            ->orderByDesc('last_update')
            ->get()
            ->map(function ($device) use ($fiveMinutesAgo) {
                $device->is_active = $device->last_update > $fiveMinutesAgo ? 1 : 0;
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
}
