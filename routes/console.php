<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Quicket nightly delta
|--------------------------------------------------------------------------
|
| Separate jobs per Quicket category (do not merge into one QUICKET_CATEGORIES
| list — tagging fallback uses the first category id). Each walks the first
| 3 pages for newly listed gigs in QUICKET_PROVINCES. Full historic seed is
| a one-off manual: quicket:pull --apply --max-pages=NN --categories=N
| (Music 1, Sports 5, Family 30, Arts & Culture 9; Other 64 manual-only).
|
*/

Schedule::command('quicket:pull --apply --page=1 --max-pages=3 --sleep=2 --categories=1')
    ->dailyAt('03:20')
    ->timezone('Africa/Johannesburg')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/quicket-pull-music.log'));

Schedule::command('quicket:pull --apply --page=1 --max-pages=3 --sleep=2 --categories=5')
    ->dailyAt('03:35')
    ->timezone('Africa/Johannesburg')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/quicket-pull-sports.log'));

Schedule::command('quicket:pull --apply --page=1 --max-pages=3 --sleep=2 --categories=30')
    ->dailyAt('03:50')
    ->timezone('Africa/Johannesburg')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/quicket-pull-family.log'));

Schedule::command('quicket:pull --apply --page=1 --max-pages=3 --sleep=2 --categories=9')
    ->dailyAt('04:05')
    ->timezone('Africa/Johannesburg')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/quicket-pull-arts.log'));
