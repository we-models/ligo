<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Message extends Model
{
    use HasFactory, SoftDeletes, Sortable, LogsActivity, CascadeSoftDeletes;

    public $fillable = ['transmitter', 'receiver', 'is_from_intermediary',  'channel', 'is_last', 'message', 'media'];

    /**
     * @var string[]
     */
    protected $casts = [ 'is_from_intermediary' => 'boolean' ];

    public function transmitter():HasOne{
        return $this->hasOne(User::class, 'id', 'transmitter');
    }

    public function receiver():HasOne{
        return $this->hasOne(User::class, 'id', 'receiver');
    }

    public function channel():HasOne{
        return $this->hasOne(Channel::class, 'id', 'channel');
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logAll();
    }
}
