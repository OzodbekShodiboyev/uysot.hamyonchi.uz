<?php

namespace App\Events;

use App\Models\Apartment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApartmentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Apartment $apartment)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('apartments');
    }

    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->apartment->id,
            'block_id'    => $this->apartment->block_id,
            'number'      => $this->apartment->number,
            'floor'       => $this->apartment->floor,
            'status'      => $this->apartment->status,
            'statusLabel' => $this->apartment->status_label,
            'statusColor' => $this->apartment->status_color,
            'statusBg'    => $this->apartment->status_bg,
        ];
    }
}
