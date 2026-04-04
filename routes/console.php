<?php

use App\Jobs\RunSpeedTestJob;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    RunSpeedTestJob::dispatchSync();
})->everyThreeMinutes();
