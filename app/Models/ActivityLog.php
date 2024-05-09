<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Activity;

/**
 *
 */
class ActivityLog extends Activity
{
    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        return $this->belongsToMany(
            Business::class,
            'model_has_business',
            'model_id',
            'business' )
            ->wherePivot('model_type', '=', Activity::class)
            ->withTimestamps();
    }
}
