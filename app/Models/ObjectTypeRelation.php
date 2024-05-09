<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ObjectTypeRelation extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = [
        'name', 'slug','type', 'object_type', 'relation', 'order', 'tab',
        'enable', 'editable', 'required' , 'filling_method',
        'description','type_relationship', 'width'
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
        'id', 'name', 'slug','type', 'object_type', 'relation', 'order',
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

        $response = [
            (new Prop('name', 'Name', [], 3))->textInput(),
            (new Prop('slug', 'Slug', [], 2))->textInput(['maxlength' => 20, 'required' => true]),
            (new Prop('type', 'Type', [], 2))->selectInput(['unique'=> 'Unique', 'multiple' => 'Multiple']),
            (new Prop('type_relationship','Type of relationship', [],2,true))->selectInput([
                'object' => __('object'),
                'user' => __('user'),
            ]),

            (new Prop('filling_method','Filling method', [
                [
                    /*
                      *show_only is activated when the value of field=>'type relationship' is equal to value=>'user'
                    */
                    'field'=> 'type_relationship',
                    'value'=> 'user',
                    'show_only' => [
                        'selection' => 'Selection',
                    ]
                ]

            ], 3))->selectInput([
                'selection' => 'Selection',
                'creation'=>'Creation',
                'all' => 'All'
            ]),

            (new Prop('object_type','Object type', [],3))->objectInput(new ObjectType()),


            (new Prop('tab','Tab', [
                ['field' => 'object_type', 'value' => null, 'operation' => '!=']
            ], 3, false))->objectInput(new Field('?tab=1'), false, [
                ['field'=>'object_type' ,'column'=>'object_type' ],
                ['field'=>'tab' ,'column'=>'tab' ]
            ]),

            (new Prop('relation', 'Relation', [
                ['field' => 'type_relationship', 'value' => 'user', 'operation' => '!=']
            ], 3))->objectInput(new ObjectType()),

            (new Prop('roles','Roles', [
                ['field' => 'type_relationship', 'value' => 'user', 'operation' => '=']
            ], 3, true))->objectInput(new NewRole(), true, []),


            (new Prop('description', 'Description', [], 4, true))->textAreaInput(),
            (new Prop('enable','Enable', [], 2))->booleanInput(),
            (new Prop('editable', 'Editable', [], 2))->booleanInput(),
            (new Prop('required','Required', [], 2))->booleanInput(),
            (new Prop('order','Order', [], 2))->intInput(),
            (new Prop('width', 'Width', [], 3))->intInput(['max' => 12]),
        ];

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
            'type_relationship'=> 'object',
            'description' => '',
            'order' => 1,
            'type' => 'unique',
            'object_type' => null,
            'filling_method' =>'selection',
            'relation' => null,
            'tab' =>null,
            'enable' => true,
            'editable' =>true,
            'required' =>false,
            'width' => 4
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
     * @return HasOne
     */
    public function tab(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'tab');
    }


    public function roles():BelongsToMany{
        return $this->belongsToMany(
            NewRole::class,
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }

}
