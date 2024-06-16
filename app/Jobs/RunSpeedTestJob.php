<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunSpeedTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        Log::debug('RunSpeedTestJob created');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try
        {
            $output = null;
            $resultCode = null;
            exec("/usr/bin/speedtest --format=json --accept-license --accept-gdpr", $output, $resultCode);

            if ($resultCode !== 0) {
                Log::error("Failed to run speed test: Result: " . implode("\n", $output));
                $this->fail();
            }
            $jsonOutput = implode("\n", $output);
            \App\Helpers\SpeedTestParser::parseAndSave($jsonOutput);
        } catch (\Exception $e)
        {
            Log::error("Failed to run speed test: " . $e->getMessage());
            $this->fail();
        }
    }
}
