<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NotificationRead extends Model
{
    public $fillable = ['receiver', 'user', 'object', 'read'];
    use HasFactory;

    public function receiver(): HasOne
    {
        return $this->hasOne(NotificationReceiver::class, 'id', 'receiver');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user');
    }

    public function the_object(): HasOne
    {
        return $this->hasOne(TheObject::class, 'id', 'object');
    }
}
