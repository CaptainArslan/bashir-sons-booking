<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SeatLocked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $seatNumbers;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(int $tripId, array $seatNumbers, User $user)
    {
        $this->tripId = $tripId;
        $this->seatNumbers = $seatNumbers;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("trip.{$this->tripId}"),
        ];
    }
}
