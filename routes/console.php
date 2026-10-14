<?php

use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * FR-NTF-005/UC-40: there is no supervisor/Redis in this stack — the cron
 * scheduler is what actually drains the database queue. It stops as soon
 * as the queue is empty so it never overlaps the next minute's run.
 */
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

/** FR-CRT-007: guest carts older than the configured window are purged. */
Schedule::call(function (): void {
    $days = (int) Setting::get('checkout', 'guest_cart_purge_days', 30);

    Cart::query()->whereNull('user_id')->where('last_activity_at', '<', now()->subDays($days))->delete();
})->daily()->name('carts:purge-guest');
