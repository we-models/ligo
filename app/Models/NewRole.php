<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 */
class NewRole extends Role implements  BaseModelInterface{

    use SoftDeletes, Sortable, LogsActivity;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'guard_name', 'description'];

    /**
     * @var string
     */
    public string $singular = 'role';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'guard_name', 'description'];


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
                'properties' => ['width' => 6, 'label' => 'Name'],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'description' => [
                'properties' => ['width' => 12, 'label' => 'Description'],
                'attributes' => ['type' => 'textarea']
            ]
        ];

    }

    /**
     */
    public static function newObject($parameters = null) : array{
        return [
            'id' =>0,
            'name' => '',
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
}
