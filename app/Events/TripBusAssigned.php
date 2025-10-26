<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripBusAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trip $trip
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('trip.'.$this->trip->id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'trip.bus.assigned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'trip_id' => $this->trip->id,
            'bus_id' => $this->trip->bus_id,
            'bus' => [
                'id' => $this->trip->bus->id,
                'name' => $this->trip->bus->name,
                'registration_number' => $this->trip->bus->registration_number,
                'layout' => [
                    'id' => $this->trip->bus->busLayout->id,
                    'name' => $this->trip->bus->busLayout->name,
                    'total_seats' => $this->trip->bus->busLayout->total_seats,
                ],
            ],
            'status' => $this->trip->status,
            'timestamp' => now()->toISOString(),
        ];
    }
}
