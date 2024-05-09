<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Activity;

/**
 *
 */
class Business extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    protected $fillable = ['name', 'code' ,'description'];

    /**
     * @var string
     */
    public string $singular = BUSINESS_IDENTIFY;

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'description'];

    /**
     * @var string
     */
    public $table = BUSINESS_IDENTIFY;

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
            'description' => [
                'properties' => ['width' => 12, 'label' => __('Description')],
                'attributes' => ['type' => 'textarea']
            ],
            'images' => [
                'properties' => ['width' => 6, 'label' => __('Image')],
                'attributes' => [
                    'type' => 'image',
                    'name' =>'image',
                    'required' => false,
                    'multiple' => false ,
                    'data' => (new ImageFile())->publicAttributes()
                ]
            ],
            'gallery' => [
                'properties' => ['width' => 6, 'label' => __('Gallery')],
                'attributes' => [
                    'type' => 'image',
                    'name' =>'gallery',
                    'required' => false,
                    'multiple' => true ,
                    'data' => (new ImageFile())->publicAttributes()
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => '',
            'description' => '',
            'images' => null,
            'contract' => null,
            'gallery' => null
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'model_has_business',
            'business',
            'model_id' )
            ->wherePivot('model_type', '=', User::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function gallery(): BelongsToMany{
        return $this->belongsToMany(
            ImageFile::class,
            'model_has_image',
            'model_id',
            'image' )
            ->wherePivot('model_type', '=', get_class($this))
            ->wherePivot('field', '=', 'gallery')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            NewRole::class,
            'model_has_business',
            'business',
            'model_id' )
            ->wherePivot('model_type', '=', NewRole::class)
            ->withTimestamps();
    }
    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'model_has_business',
            'business',
            'model_id' )
            ->wherePivot('model_type', '=', Group::class)
            ->withTimestamps();
    }
}
