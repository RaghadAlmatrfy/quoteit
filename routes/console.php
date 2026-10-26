<?php

use App\Models\Status;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete expired statuses (older than 24 hours) every hour
Schedule::command('model:prune', ['--model' => [Status::class]])->hourly();
