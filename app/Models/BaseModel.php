<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BaseModel extends Model {
    use HasFactory, SoftDeletes, Sortable, LogsActivity, CascadeSoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [];

    /**
     * @var string
     */
    public string $singular = '';

    /**
     * @var string[]
     */
    public array $sortable = ['id', 'name'];

    /**
     * @var string
     */
    public string $query = "";

    /**
     * @param bool $self
     * @return array
     */
    public function getFields(bool $self = false): array {
        return [];
    }

    /**
     * @param string $parameters
     * @return array
     */
    public static function newObject(string $parameters = ""): array
    {
        return [
            'id' => 0,
            'name' => ""
        ];
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getMergedFields(array $fields): array {
        $response = [];
        foreach ($fields as $field){
            $response = array_merge($response, $field);
        }
        return $response;
    }

    /**
     * @return array
     */
    public function publicAttributes(): array {

        if(!str_starts_with($this->query, '?')){
            $this->query = '?'. $this->query;
        }

        return [
            'fields' => $this->getFields(),
            'values' => $this->newObject(),
            'url' => route($this->singular .'.all', app()->getLocale()) . $this->query,
            'index' => route($this->singular .'.index', app()->getLocale()) . $this->query
        ];
    }

    /**
     * @return array
     */
    public function getPermissionsForModel(): array {
        return requestPermission($this->singular);
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logAll();
    }

    /**
     * @return string
     */
    public function getTable(): string {
        return parent::getTable();
    }

    /**
     * @return BelongsToMany
     */
    public function images(): BelongsToMany {
        return $this->belongsToMany(
            ImageFile::class,
            'model_has_image',
            'model_id',
            'image' )
            ->wherePivot('model_type', '=', get_class($this))
            ->wherePivot('field', '=', 'images')
            ->withTimestamps();
    }


    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany {
        return $this->belongsToMany(
            File::class,
            'model_has_file',
            'model_id',
            'file' )
            ->wherePivot('model_type', '=', get_class($this))
            ->wherePivot('field', '=', 'files')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function manipulated_by(): BelongsToMany {
        return $this->belongsToMany(
            User::class,
            'user_manipulations',
            'model_id',
            'user' )
            ->wherePivot('model_type', '=', get_class($this))
            ->withTimestamps();
    }
}
