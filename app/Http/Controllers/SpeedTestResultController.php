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
        $expr = $this->getDbExpressions();

        $dailyResults = SpeedTestResult::select([
            DB::raw("{$expr['time_group']} as time_group"),
            DB::raw("ROUND(AVG(download_speed) / 100000.0, 2) as average_download_speed"),
            DB::raw("ROUND(AVG(upload_speed) / 100000.0, 2) as average_upload_speed"),
            DB::raw('AVG(packet_loss) as average_packet_loss'),
            DB::raw('AVG(ping) as average_ping')
        ])
            ->where('timestamp', '>=', Carbon::now()->subDay())
            ->groupBy('time_group')
            ->orderBy('time_group')
            ->get();

        $lastWeekResults = SpeedTestResult::select([
            DB::raw("{$expr['day_of_week']} as dayOfWeek"),
            DB::raw("ROUND(AVG(download_speed) / 100000.0, 2) as average_download_speed"),
            DB::raw("ROUND(AVG(upload_speed) / 100000.0, 2) as average_upload_speed"),
            DB::raw('AVG(packet_loss) as average_packet_loss'),
            DB::raw('AVG(ping) as average_ping')
        ])
            ->where('timestamp', '>=', Carbon::now()->subDays(7))
            ->groupBy('dayOfWeek')
            ->orderBy('dayOfWeek')
            ->get();

        $allTimeResults = SpeedTestResult::select([
            'timestamp',
            DB::raw('ROUND(download_speed / 100000.0, 2) as average_download_speed'),
            DB::raw('ROUND(upload_speed / 100000.0, 2) as average_upload_speed'),
            DB::raw('packet_loss as average_packet_loss'),
            DB::raw('ping as average_ping'),
        ])->orderBy('timestamp', 'desc')->get();

        // Days of the week mapping (0=Mon, 1=Tue, ... 6=Sun)
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return Inertia::render('Index', [
            'dayData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $dailyResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->time_group),
                            'y' => $item->average_ping,
                        ])->all(),
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $dailyResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->time_group),
                            'y' => $item->average_upload_speed,
                        ])->all(),
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $dailyResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->time_group),
                            'y' => $item->average_download_speed,
                        ])->all(),
                    ],
                    [
                        'name' => 'Packet Loss',
                        'data' => $dailyResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->time_group),
                            'y' => $item->average_packet_loss,
                        ])->all(),
                    ],
                ],
            ],
            'weekData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_ping')->all(),
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_upload_speed')->all(),
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_download_speed')->all(),
                    ],
                    [
                        'name' => 'Packet Loss',
                        'data' => $lastWeekResults->sortBy('dayOfWeek')->pluck('average_packet_loss')->all(),
                    ],
                ],
                'categories' => $daysOfWeek,
            ],
            'allData' => [
                'series' => [
                    [
                        'name' => 'Ping',
                        'data' => $allTimeResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->timestamp),
                            'y' => $item->average_ping,
                        ])->all(),
                    ],
                    [
                        'name' => 'Upload Speed',
                        'data' => $allTimeResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->timestamp),
                            'y' => $item->average_upload_speed,
                        ])->all(),
                    ],
                    [
                        'name' => 'Download Speed',
                        'data' => $allTimeResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->timestamp),
                            'y' => $item->average_download_speed,
                        ])->all(),
                    ],
                    [
                        'name' => 'Packet Loss',
                        'data' => $allTimeResults->map(fn ($item) => [
                            'x' => Carbon::parse($item->timestamp),
                            'y' => $item->average_packet_loss,
                        ])->all(),
                    ],
                ],
            ],
        ]);
    }

    public function test(): \Illuminate\Http\JsonResponse
    {
        RunSpeedTestJob::dispatchSync();
        return response()->json(['message' => 'Speedtest completed']);
    }

    /**
     * Get database-specific SQL expressions for time grouping and day-of-week.
     * Normalizes day-of-week to 0=Mon, 1=Tue, ..., 6=Sun across all drivers.
     */
    private function getDbExpressions(): array
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => [
                'time_group' => "strftime('%Y-%m-%d %H:', timestamp) || substr('0' || ((cast(strftime('%M', timestamp) as integer) / 15) * 15), -2)",
                'day_of_week' => "(cast(strftime('%w', timestamp) as integer) + 6) % 7",
            ],
            'pgsql' => [
                'time_group' => "to_char(date_trunc('hour', timestamp) + (floor(extract(minute from timestamp) / 15) * interval '15 minutes'), 'YYYY-MM-DD HH24:MI')",
                'day_of_week' => "(extract(dow from timestamp)::integer + 6) % 7",
            ],
            default => [ // mysql, mariadb
                'time_group' => "DATE_FORMAT(timestamp, CONCAT('%Y-%m-%d %H:', LPAD(FLOOR(MINUTE(timestamp) / 15) * 15, 2, '0')))",
                'day_of_week' => "(DAYOFWEEK(timestamp) + 5) % 7",
            ],
        };
    }
}
