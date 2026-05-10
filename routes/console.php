<?php
// © Atia Hegazy — atiaeno.com

use App\Jobs\AnonymizeIpJob;
use App\Jobs\DeleteOldClicksJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run IP anonymization daily at 02:00
Schedule::job(new AnonymizeIpJob)->dailyAt('02:00');

// GDPR data retention: hard-delete click records older than configured days (monthly)
Schedule::job(new DeleteOldClicksJob)->monthlyOn(1, '03:00');

// Update disposable email domains list weekly
Schedule::command('disposable:update')->weekly();

// Affiliate: sync earnings + prune old visits daily at 01:00
Schedule::command('affiliates:daily-sync')->dailyAt('01:00');

// Aggregate link analytics daily at 03:30
Schedule::command('analytics:aggregate')->dailyAt('03:30');

// SEO Indexer: process queue and submit URLs to search engines hourly
Schedule::command('indexer:run')->hourly();
