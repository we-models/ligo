<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Activity;

class TheObject extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name',  'description','excerpt', 'object_type', 'visible', 'owner', 'parent', 'wp_id'];

    /**
     * @var string
     */
    public string $singular = 'object';

    /**
     * @var array|string[]
     */
    public array $sortable = ['name', 'description', 'excerpt', 'visible', 'owner', 'parent', 'created_at'];

    protected $hidden = ['updated_at', 'deleted_at'];

    /**
     * @var string
     */
    public $table = 'objects';

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
        $has_business = self::getCurrentBusiness() != null;
        $response = [
            'name' => [
                'properties' => ['width' => 5, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'object_type' => [
                'properties' => ['width' => 4, 'label' => __('Object type')],
                'attributes' => [
                    'type' => 'object',
                    'readonly'=> 'true',
                    'name' =>'object_type',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new ObjectType())->publicAttributes()
                ]
            ],
            'visible' => [
                'properties' => ['width' => 3, 'label' => __('Visible') ],
                'attributes' => ['type' => 'checkbox', 'class' => 'form-check-input']
            ],
            'excerpt' => [
                'properties' => ['width' => 7, 'label' => __('Excerpt')],
                'attributes' => ['type' => 'text' , 'class' => 'form-control']
            ],
            'owner' => [
                'properties' => ['width' => 5, 'label' => __('Owner')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'owner',
                    'required' => false,
                    'multiple' => false ,
                    'data' => (new User())->publicAttributes()
                ]
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
        ];

        if($self){
            $response = array_merge($response, [
                'parent' => [
                    'properties' => ['width' => 6, 'label' => __('Parent')],
                    'attributes' => [
                        'type' => 'object',
                        'name' =>'parent',
                        'required' => false,
                        'multiple' => false ,
                        'data' => (new TheObject($this->query))->publicAttributes()
                    ]
                ],
            ]);
        }

        if($has_business){
            $response = array_merge($response, [
                'business' => [
                    'properties' => ['width' => 12, 'label' => __('Business')],
                    'attributes' => [
                        'type' => 'object',
                        'name' =>'business',
                        'required' => true,
                        'multiple' => false ,
                        'data' => (new Business())->publicAttributes()
                    ]
                ],
            ]);
        }
        return $response;
    }


    /**
     * @param $parameters
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        $object_type = null;

        $parameters = explode('&', $parameters);
        foreach ($parameters as $parameter){
            $parameter = explode('=', $parameter);
            if($parameter[0] == 'object_type'){
                $object_type = ObjectType::query()->where('id', $parameter[1] )->first();
            }
        }

        return [
            'id' =>0,
            'name' => '',
            'visible' => true,
            'description' => "",
            'object_type' => $object_type,
            'excerpt' => "",
            'images' => null,
            'owner' => null,
            'parent' => null,
            'business' => self::getCurrentBusiness(),
        ];
    }

    /**
     * @return HasOne
     */
    public function object_type(): HasOne
    {
        return $this->hasOne(ObjectType::class, 'id', 'object_type');
    }

    /**
     * @return BelongsToMany
     */
    public function field_value(): BelongsToMany
    {
        return $this->belongsToMany(
            Field::class,
            'object_field_value',
            'object',
            'field')->withPivot('value')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function relation_value(): BelongsToMany
    {
        return $this->belongsToMany(
            TheObject::class,
            'object_relations',
            'object',
            'relation')->withPivot(['relation_object'])->withTimestamps();
    }

    public function value_for_relation(){
        return $this->belongsToMany(
            TheObject::class,
            'object_relations',
            'relation',
            'object')->withPivot(['relation_object'])->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    /**
     * @return HasOne
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner');
    }

    /**
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(TheObject::class, 'id', 'parent');
    }

    public function comments(): HasMany{
        return $this->hasMany(Comment::class, 'object', 'id');
    }
}
