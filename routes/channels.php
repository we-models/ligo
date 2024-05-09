<?php

use App\Models\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('new-room', function ($user) {
    if(!Auth::check()) return false;
    return true;
});

Broadcast::channel('private-chat.{roomId}', function ($user, $roomId) {
    $channel = Channel::query()
        ->select('id')
        ->where('name', $roomId)
        ->where(function ($query) use ($user) {
            $query->orWhere('user1', $user->id)
                ->orWhere('user2', $user->id)
                ->orWhere('intermediary', $user->id);
        })
        ->limit(1)
        ->first();

    return $channel !=  null;
});
