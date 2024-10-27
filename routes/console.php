<?php

use Illuminate\Support\Facades\Schedule;

// Schedule the 'products:sync' command to run daily at midnight (00:00).
Schedule::command('products:sync')->dailyAt('00:00');

// Uncommenting the line below would schedule the 'products:sync' command to run every 5 seconds (Just for testing).
// Note: Scheduling commands to run every second is not supported out-of-the-box in Laravel's task scheduling.

// Schedule::command('products:sync')->everySecond(5);


/*
 * You can also define a closure that contains additional scheduling logic.
 * This example shows how to use the Schedule::call method to run a command.
 * Here, we are scheduling the 'products:sync' command to run daily at midnight (00:00) again,
 * but it's wrapped in a callable function (which is currently commented out)
 */

/*.
Schedule::call(function (Schedule $schedule) {
    $schedule->command('products:sync')->dailyAt('00:00');
});
*/
