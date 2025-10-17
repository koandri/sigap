<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Schedule automatic work order generation from maintenance schedules.
 * Runs daily at 00:00 Asia/Jakarta timezone to check for overdue maintenance schedules and create work orders.
 * 
 * NOTE: Currently disabled. Uncomment the lines below to enable automatic work order generation.
 */
// Schedule::command('maintenance:generate-work-orders')
//     ->dailyAt('00:00')
//     ->timezone('Asia/Jakarta')
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->onSuccess(function () {
//         Log::info('Maintenance work order generation completed successfully');
//     })
//     ->onFailure(function () {
//         Log::error('Maintenance work order generation failed');
//     });