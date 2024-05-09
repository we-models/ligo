<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 */
class NewRole extends Role implements  BaseModelInterface{

    use SoftDeletes, Sortable, LogsActivity;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'guard_name', 'description', 'is_admin', 'public', 'icon'];

    /**
     * @var string
     */
    public string $singular = 'role';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'guard_name', 'description', 'is_admin', 'public'];


    /**
     * @var string[]
     */
    protected $casts = [ 'is_admin' => 'boolean','public' => 'boolean' ];

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
    public function getFields(bool $self=false) : array
    {
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'guard_name' => [
                'properties' => ['width' => 6, 'label' => __('Guard') ],
                'attributes' => ['type' => 'text', 'value' =>'web', 'readonly'=>'', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'public' => [
                'properties' => ['width' => 3, 'label' => __('Public') ],
                'attributes' => ['type' => 'checkbox', 'class' => 'form-check-input']
            ],
            'is_admin' => [
                'properties' => ['width' => 3, 'label' => __('Is administrator') ],
                'attributes' => ['type' => 'checkbox', 'class' => 'form-check-input']
            ],
            'icon' => [
                'properties' => ['width' => 6, 'label' => __('Icon') ],
                'attributes' => ['type' => 'icon', 'required' => true]
            ],
            'description' => [
                'properties' => ['width' => 12, 'label' => __('Description')],
                'attributes' => ['type' => 'textarea']
            ]
        ];

    }

    /**
     */
    public static function newObject($pt = null) : array{
        return [
            'id' =>0,
            'name' => '',
            'guard_name' => '',
            'public' => false,
            'is_admin' => false,
            'icon' => '',
            'description' => ''
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
    public function getActivitylogOptions(): LogOptions {
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
            'url' => route($this->singular.'.all', app()->getLocale())
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
            'role_has_permissions',
            'role_id',
            'permission_id');
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
            ->wherePivot('model_type', '=', NewRole::class)
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
