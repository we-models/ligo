<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\ArrayShape;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Group extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'icon', 'business'];

    /**
     * @var string
     */
    public string $singular = 'group';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'icon'];

    /**
     * @var string
     */
    public $table = 'groups';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }


    /**
     * @param bool $self
     * @return array[]
     */
    public function getFields(bool $self = false) : array
    {
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'icon' => [
                'properties' => ['width' => 6, 'label' => __('Icon') ],
                'attributes' => ['type' => 'icon', 'required' => true]
            ]
        ];
    }

    /**

     */
    public static function newObject(string $parameters = "") : array   {
        return [
            'id' =>0,
            'name' => '',
            'icon' => '',
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            NewRole::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', NewRole::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            NewPermission::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', NewPermission::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(
            Link::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', Link::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }
}
