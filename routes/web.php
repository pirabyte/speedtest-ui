<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeedTestResultController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ SpeedTestResultController::class, 'index' ]);
Route::get('/test', [ SpeedTestResultController::class, 'test' ]);

require __DIR__.'/auth.php';
