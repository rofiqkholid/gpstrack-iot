<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceSchedule;

class ServiceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // Motor
            ['vehicle_type' => 'motor', 'component' => 'Ganti Oli Mesin', 'interval_km' => 2500, 'description' => 'Pergantian oli mesin secara berkala untuk menjaga performa mesin'],
            ['vehicle_type' => 'motor', 'component' => 'Ganti V-Belt', 'interval_km' => 10000, 'description' => 'Pergantian V-Belt untuk transmisi matic yang optimal'],
            ['vehicle_type' => 'motor', 'component' => 'Ganti Roller', 'interval_km' => 10000, 'description' => 'Pergantian roller CVT untuk akselerasi yang halus'],
            ['vehicle_type' => 'motor', 'component' => 'Ganti Busi', 'interval_km' => 10000, 'description' => 'Pergantian busi untuk pembakaran yang sempurna'],
            ['vehicle_type' => 'motor', 'component' => 'Ganti Air Radiator', 'interval_km' => 10000, 'description' => 'Pergantian air radiator untuk sistem pendingin mesin'],
            ['vehicle_type' => 'motor', 'component' => 'Ganti Filter Udara', 'interval_km' => 15000, 'description' => 'Pergantian filter udara untuk aliran udara bersih ke mesin'],

            // Mobil
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Oli Mesin', 'interval_km' => 5000, 'description' => 'Pergantian oli mesin secara berkala untuk menjaga performa mesin'],
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Filter Oli', 'interval_km' => 10000, 'description' => 'Pergantian filter oli untuk menyaring kotoran dari oli mesin'],
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Filter Udara', 'interval_km' => 20000, 'description' => 'Pergantian filter udara untuk aliran udara bersih ke mesin'],
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Busi', 'interval_km' => 20000, 'description' => 'Pergantian busi untuk pembakaran yang sempurna'],
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Minyak Rem', 'interval_km' => 40000, 'description' => 'Pergantian minyak rem untuk keamanan sistem pengereman'],
            ['vehicle_type' => 'mobil', 'component' => 'Ganti Timing Belt', 'interval_km' => 80000, 'description' => 'Pergantian timing belt untuk sinkronisasi mesin yang tepat'],
        ];

        foreach ($schedules as $schedule) {
            ServiceSchedule::updateOrCreate(
                ['vehicle_type' => $schedule['vehicle_type'], 'component' => $schedule['component']],
                $schedule
            );
        }
    }
}
