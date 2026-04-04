<?php

use App\Http\Controllers\SpeedTestResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpeedTestResultController::class, 'index']);
Route::get('/test', [SpeedTestResultController::class, 'test']);
