<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule webhook retry command to run every 5 minutes
Schedule::command('webhooks:retry')->everyFiveMinutes();

// Create the next period's subscription + invoice for anything past end_date
Schedule::command('subscriptions:renew')->dailyAt('06:00');
