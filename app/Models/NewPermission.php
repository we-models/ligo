<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\ArrayShape;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;

/**
 *
 */
class NewPermission extends Permission implements  BaseModelInterface
{
    use SoftDeletes, Sortable, LogsActivity;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'guard_name', 'detail', 'show_in_menu', 'identifier'];

    /**
     * @var string
     */
    public string $singular = 'permission';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'guard_name', 'detail', 'group', 'show_in_menu'];

    /**
     * @var string[]
     */
    protected $casts = [ 'show_in_menu' => 'boolean', 'group' => 'json' ];

    /**
     * @var string
     */
    public string $query = "";

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }


    /**
     * @param bool $self
     * @return array
     */
    public function getFields(bool $self = false) : array
    {
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'identifier' => [
                'properties' => ['width' => 6, 'label' => __('Identifier')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'guard_name' => [
                'properties' => ['width' => 6, 'label' => __('Guard') ],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'value' =>'web', 'readonly'=>'', 'required' => true, 'class' => 'form-control']
            ],
            'show_in_menu' => [
                'properties' => ['width' => 6, 'label' => __('Show in menu') ],
                'attributes' => ['type' => 'checkbox', 'class' => 'form-check-input']
            ],
            'detail' => [
                'properties' => ['width' => 12, 'label' => __('Detail')],
                'attributes' => ['type' => 'textarea']
            ]
        ];
    }


    /**
     * @return array
     */
    public static function newObject($pt = null) : array
    {
        return [
            'id' =>0,
            'name' => '',
            'identifier' => '',
            'guard_name' => '',
            'detail' => '',
            'show_in_menu' => false
        ];
    }

    /**
     * @return array
     */
    public function getPermissionsForModel(): array
    {
        return requestPermission($this->singular);
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * @param Activity $activity
     * @param string $eventName
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        saveLogForBusiness($activity);
    }

    /**
     * @return array
     */
    public function publicAttributes():array
    {
        return [
            'fields' => $this->getFields(),
            'values' => self::newObject(),
            'url' => route( $this->singular .'.all', app()->getLocale())
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'model_has_group',
            'model_id',
            'group' )
            ->wherePivot('model_type', '=', NewPermission::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function all_business(): BelongsToMany
    {
        return $this->belongsToMany(
            Business::class,
            'model_has_business',
            'model_id',
            'business' )
            ->wherePivot('model_type', '=', NewPermission::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS) || session(BUSINESS_IDENTIFY) == null){
            return $this->all_business();
        }
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }
}
