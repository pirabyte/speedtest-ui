<?php

namespace App\Http\Controllers;

use App\Jobs\RunSpeedTestJob;
use App\Models\SpeedTestResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SpeedTestResultController extends Controller
{
    public function index(): \Inertia\Response
    {

        $dailyResults = SpeedTestResult::select([
            DB::raw("DATE_FORMAT(timestamp, CONCAT('%Y-%m-%d %H:', LPAD(FLOOR(MINUTE(timestamp) / 15) * 15, 2, '0'))) as time_group"),
            DB::raw('ROUND(AVG(download_speed) / 100000,2) as average_download_speed'),
            DB::raw('ROUND(AVG(upload_speed) / 100000,2) as average_upload_speed'),
            DB::raw('AVG(packet_loss) as average_packet_loss'),
            DB::raw('AVG(ping) as average_ping')
        ])
            ->where('timestamp', '>=', Carbon::now()->subDay())
            ->groupBy('time_group')
            ->orderBy('time_group')
            ->get();

        $lastWeekResults = SpeedTestResult::select([
            DB::raw("DAYOFWEEK(timestamp) as dayOfWeek"), // Group by day of the week
            DB::raw('ROUND(AVG(download_speed)/ 100000,2) as average_download_speed'),
            DB::raw('ROUND(AVG(upload_speed)/ 100000,2) as average_upload_speed'),
            DB::raw('AVG(packet_loss) as average_packet_loss'),
            DB::raw('AVG(ping) as average_ping')
        ])
            ->where('timestamp', '>=', Carbon::now()->subDays(7))
            ->groupBy('dayOfWeek')
            ->orderBy('dayOfWeek')
            ->get();

        $allTimeResults = SpeedTestResult::select([
            'timestamp',
            DB::raw('ROUND(download_speed/ 100000,2) as average_download_speed'),
            DB::raw('ROUND(upload_speed/ 100000,2) as average_upload_speed'),
            DB::raw('packet_loss as average_packet_loss'),
            DB::raw('ping as average_ping'),
        ])->orderByRaw('timestamp desc')->get();

        // Days of the week mapping
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return Inertia::render('Index', [
            'dayData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $dailyResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->time_group),
                                'y' => $item->average_ping
                            ];})->all()
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $dailyResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->time_group),
                                'y' => $item->average_upload_speed
                            ];})->all()
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $dailyResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->time_group),
                                'y' => $item->average_download_speed
                            ];})->all()
                    ],
                    [
                        'name' => 'Packet loss',
                        'data' => $dailyResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->time_group),
                                'y' => $item->average_packet_loss
                            ];})->all()
                    ],

                ],
            ],
            'weekData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('ping')->all()
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_upload_speed')->all()
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_download_speed')->all()
                    ],
                    [
                        'name' => 'Packet loss',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_packet_loss')->all()
                    ],

                ],
                'categories' => $daysOfWeek,
            ],
            'allData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $allTimeResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->timestamp),
                                'y' => $item->average_ping
                            ];})->all()
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $allTimeResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->timestamp),
                                'y' => $item->average_upload_speed];
                        })->all()
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $allTimeResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->timestamp),
                                'y' => $item->average_download_speed];
                        })->all()
                    ],
                    [
                        'name' => 'Packet loss',
                        'data' => $allTimeResults->map(function ($item) {
                            return [
                                'x' => Carbon::parse($item->timestamp),
                                'y' => $item->average_packet_loss];
                        })->all()
                    ],
                ]
            ]
        ]);
    }

    public function test(): \Illuminate\Http\JsonResponse
    {
        RunSpeedTestJob::dispatchSync();
        return response()->json(['message' => 'Speedtest completed']);
    }
}
