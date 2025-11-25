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

/**
 * Schedule automatic cleaning task generation from cleaning schedules.
 * Runs daily at 00:00 Asia/Jakarta timezone to:
 * - Generate cleaning tasks for today
 * - Mark yesterday's uncompleted tasks as missed
 * - Flag random submissions for review
 * - Release inactive locked tasks
 * 
 * NOTE: Currently disabled. Uncomment the lines below to enable automatic task generation.
 */
// Schedule::command('cleaning:generate-tasks')
//     ->dailyAt('00:00')
//     ->timezone('Asia/Jakarta')
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->onSuccess(function () {
//         Log::info('Cleaning task generation completed successfully');
//     })
//     ->onFailure(function () {
//         Log::error('Cleaning task generation failed');
//     });

/**
 * Schedule cleaning task reminders.
 * Runs every 2 hours during working hours (08:00 to 18:00) to send reminders
 * for tasks scheduled within the next 2 hours.
 * Sends notifications via WhatsApp with Pushover fallback.
 * 
 * NOTE: Currently disabled. Uncomment the lines below to enable automatic reminders.
 */
// Schedule::command('cleaning:send-reminders --hours=2')
//     ->twiceDaily(8, 14)
//     ->timezone('Asia/Jakarta')
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->onSuccess(function () {
//         Log::info('Cleaning task reminders sent successfully');
//     })
//     ->onFailure(function () {
//         Log::error('Cleaning task reminder sending failed');
//     });

/**
 * Schedule cleanup of expired document access requests.
 * Runs daily at 01:00 Asia/Jakarta timezone to revoke expired access grants.
 */
Schedule::command('dms:cleanup-expired-access')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Document access cleanup completed successfully');
    })
    ->onFailure(function () {
        Log::error('Document access cleanup failed');
    });

/**
 * Schedule recalculation of asset lifetime metrics.
 * Runs monthly on the 1st at 02:00 Asia/Jakarta timezone.
 */
Schedule::command('assets:recalculate-lifetime-metrics --all')
    ->monthlyOn(1, '02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Asset lifetime metrics recalculation completed successfully');
    })
    ->onFailure(function () {
        Log::error('Asset lifetime metrics recalculation failed');
    });