<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FCMToken extends Model
{
    use HasFactory;


    protected $fillable = ['user', 'token' ,'device'];

    public $table = "fcm_tokens";

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user');
    }
}
