<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatUnlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $tripId,
        public array $seatNumbers,
        public User $user,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("trip.{$this->tripId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'seat-unlocked';
    }

    public function broadcastWith(): array
    {
        return [
            'trip_id' => $this->tripId,
            'seat_numbers' => $this->seatNumbers,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
        ];
    }
}
