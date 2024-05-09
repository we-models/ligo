<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Activity;

class ObjectTypeRelation extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = [
        'name', 'slug','type', 'object_type', 'relation', 'order', 'tab',
        'enable', 'visible_in_app', 'editable', 'required' , 'filling_method',
        'description', 'instructions'
    ];

    /**
     * @var string
     */
    public string $singular = 'object_type_relation';

    protected $hidden = ['updated_at', 'deleted_at'];

    /**
     * @var array|string[]
     */

    public array $sortable = [
        'id', 'name', 'slug','type', 'object_type', 'relation', 'order', 'instructions',
        'tab', 'filling_method', 'required', 'description'];

    /**
     * @var string
     */
    public $table = 'object_type_relations';

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
            (new Prop('name', __('Name'), [], 4))->textInput(),
            (new Prop('slug', __('Slug'), [], 4))->textInput(['maxlength' => 20, 'required' => true]),
            (new Prop('type', __('Type'), [], 4))->selectInput(['unique'=> __('Unique'), 'multiple' => __('Multiple')]),


            (new Prop('description', __('Description'), [], 12, true))->textAreaInput(),

            (new Prop('object_type', __('Object type'), [],4))->objectInput(new ObjectType()),
            (new Prop('tab', __('Tab'), [
                ['field' => 'object_type', 'value' => null, 'operation' => '!=']
            ], 4, false))->objectInput(new Field('?tab=1'), false, [
                ['field'=>'object_type' ,'column'=>'object_type' ],
                ['field'=>'tab' ,'column'=>'tab' ]
            ]),

            (new Prop('relation', __('Relation'), [], 4))->objectInput(new ObjectType()),

            (new Prop('enable', __('Enable'), [], 3))->booleanInput(),
            (new Prop('visible_in_app', __('Visible in app'), [], 3))->booleanInput(),
            (new Prop('editable', __('Editable'), [], 3))->booleanInput(),
            (new Prop('required', __('Required'), [], 3))->booleanInput(),

            (new Prop('filling_method', __('Filling method'), [], 4))->selectInput([
                'selection' => __('Selection'),
                'creation'=>__('Creation'),
                'own_selection' => __('Own selection'),
                'all' => __('All')
            ]),
            (new Prop('order', __('Order'), [], 4))->intInput(),
            (new Prop('instructions', __('Instructions'), [], 8))->textAreaInput(),
            (new Prop('video', __('Video instructive'), [], 4))->fileInput('video')

        ];

        if($has_business){
            $response = array_merge(
                $response,[(new Prop('business', __('Business'), [], 12))->objectInput(new Business())]
            );
        }
        return $this->getMergedFields($response);
    }

    /**
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => '',
            'slug' => '',
            'video' => null,
            'description' => '',
            'order' => 1,
            'type' => 'unique',
            'object_type' => null,
            'filling_method' =>'selection',
            'relation' => null,
            'instructions' => '',
            'tab' =>null,
            'enable' => true,
            'visible_in_app' => true,
            'editable' =>true,
            'business' => self::getCurrentBusiness(),
            'required' =>false
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
     * @return HasOne
     */
    public function relation(): HasOne
    {
        return $this->hasOne(ObjectType::class, 'id', 'relation');
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS)) return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }


    public function video(){
        return $this->belongsToMany(
            File::class,
            'model_has_file',
            'model_id',
            'file' )
            ->wherePivot('model_type', '=', get_class($this))
            ->wherePivot('field', '=', 'video')
            ->withTimestamps();
    }

    /**
     * @return HasOne
     */
    public function tab(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'tab');
    }

}
