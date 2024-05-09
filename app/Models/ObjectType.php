<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Traits\HasRoles;

class ObjectType extends BaseModel implements BaseModelInterface
{
    use HasRoles;

    /**
     * @var string[]
     */
    public $fillable = ['name', 'enable', 'slug', 'description', 'type', 'public', 'access_code'];

    /**
     * @var string
     */
    public string $singular = 'object_type';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'enable', 'description', 'type', 'slug', 'public', 'access_code'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @var string
     */
    public $table = 'object_types';

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
            (new Prop('name', __('Name'), [], 5))->textInput(),
            (new Prop('type', __('Type'), [], 5))->selectInput(['post'=>__('Post'), 'taxonomy' => __('Taxonomy')]),
            (new Prop('enable', __('Enable'), [], 2))->booleanInput(),
            (new Prop('access_code', __('Access code'), [], 12))->textInput(['readonly' => true]),
            (new Prop('slug', __('Slug'), [], 5))->textInput(['maxlength' => 20]),
            (new Prop('public', __('Is public'), [], 2))->booleanInput(),
            (new Prop('description', __('Description'), [], 12))->textAreaInput(),
        ];

        if($has_business){
            $response = array_merge(
                $response,[(new Prop('business', __('Business'), [], 12))->objectInput(new Business())]
            );
        }
        return $this->getMergedFields($response);
    }

    /**
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => '',
            'slug' => '',
            'type' => 'post',
            'access_code' => generateRandomString(32),
            'enable' => true,
            'public' => true,
            'description' => '',
            'business' => self::getCurrentBusiness(),
        ];
    }

    /**
     * @return HasMany
     */
    public function fields(): hasMany
    {
        return $this->hasMany(Field::class, 'object_type', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS)) return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    /**
     * @return HasMany
     */
    public function relations_with(){
        return $this->hasMany(ObjectTypeRelation::class, 'object_type','id');
    }

    public function rating_types(){
        return $this->hasMany(RatingType::class, 'object_type', 'id');
    }

}
