<?php

namespace App\Console;

use App\Jobs\{MarkOverduePayments, ReleaseExpiredReservations};
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Muddati o'tgan bronlarni har 5 daqiqada avtomatik bo'shatish
        $schedule->job(new ReleaseExpiredReservations, 'default')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->name('release-expired-reservations');

        // Muddati o'tgan to'lovlarni har kuni 00:05 da belgilash
        $schedule->job(new MarkOverduePayments, 'default')
                 ->dailyAt('00:05')
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->name('mark-overdue-payments');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
