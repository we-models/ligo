<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NotificationReceiver extends Model
{
    use HasFactory;

    public function role(){
        return $this->hasOne(NewRole::class, 'id', 'role');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user');
    }


    public function reads(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(
            NotificationRead::class,
            'id',
            'receiver'
        );
    }

    public function notification(): HasOne
    {
        return $this->hasOne(Notification::class, 'notification', 'id');
    }

}
