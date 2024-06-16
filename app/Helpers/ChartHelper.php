<?php

namespace App\Helpers;

use App\Models\SpeedTestResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChartHelper
{
    public static function getTodaysResultForEvery5Minutes()
    {
        return SpeedTestResult::select([
            DB::raw("DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i') as time_group"), // Formatieren zu Jahr-Monat-Tag Stunde:Minute
            DB::raw('ROUND(AVG(download_speed), 2) as average_download_speed'),
            DB::raw('ROUND(AVG(upload_speed), 2) as average_upload_speed'),
            DB::raw('ROUND(AVG(ping), 2) as average_ping')
        ])
            ->where('timestamp', '>=', Carbon::now()->subDay())
            ->whereRaw("MINUTE(timestamp) % 5 = 0") // Nur Daten, deren Minuten durch 5 teilbar sind
            ->groupBy('time_group')
            ->orderBy('time_group')
            ->get();
    }
}
