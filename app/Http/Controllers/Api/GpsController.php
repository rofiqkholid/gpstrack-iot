<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Location;


class GpsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location saved successfully',
            'data' => $location
        ], 201);
    }
}
