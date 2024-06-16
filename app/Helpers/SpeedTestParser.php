<?php

namespace App\Helpers;

use App\Models\SpeedTestResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SpeedTestParser
{
    public static function parseAndSave($jsonOutput): void
    {
        try
        {
            $data = json_decode($jsonOutput);
            $model = SpeedTestResult::create([
                'server_id' => $data->server->id,
                'download_speed' => $data->download->bandwidth,
                'upload_speed' => $data->upload->bandwidth,
                'timestamp' => Carbon::parse($data->timestamp)->format('Y-m-d H:i:s'),
                'ping' => $data->ping->latency,
                'user_isp' => $data->isp,
                'user_ip' => $data->interface->externalIp,
                'packet_loss' => $data->packetLoss
            ]);
            Log::debug('Crated new speed test result: ' . $model->id);
        }
        catch (\Exception $e)
        {
            Log::error("Failed to parse and save speed test result: " . $e->getMessage());
        }
    }
}
