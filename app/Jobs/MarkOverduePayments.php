<?php

namespace App\Jobs;

use App\Models\{ActivityLog, PaymentSchedule};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class MarkOverduePayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        $updated = PaymentSchedule::query()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        if ($updated > 0) {
            ActivityLog::log(
                'schedules.overdue_marked',
                null,
                "{$updated} ta to'lov muddati o'tgan deb belgilandi"
            );
        }
    }
}
