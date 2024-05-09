<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Channel as Room;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public Model $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $msg)
    {
        $message = Message::query()->where('id', $msg)->with('channel')->first();
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $msg = $this->message->toArray();
        return new PrivateChannel('private-chat.'.$msg['channel']['name']);
    }
}
