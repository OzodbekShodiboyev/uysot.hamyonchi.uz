<?php

namespace App\Jobs;

use App\Events\ApartmentStatusChanged;
use App\Models\{ActivityLog, Apartment};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ReleaseExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $expired = Apartment::query()
            ->where('status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->get();

        foreach ($expired as $apt) {
            $apt->update([
                'status'         => 'free',
                'reserved_until' => null,
                'reserved_by'    => null,
            ]);

            ActivityLog::log(
                'apartment.auto_released',
                $apt,
                "Xonadon №{$apt->number} muddati o'tganligi sababli avtomatik bo'shatildi"
            );

            broadcast(new ApartmentStatusChanged($apt));
        }
    }
}
