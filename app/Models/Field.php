<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Field extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = [
        'object_type', 'name', 'slug', 'layout', 'type', 'options' ,
        'default', 'tab', 'order' , 'accept', 'editable', 'required',
        'description', 'format', 'show_tab_name', 'width'];

    /**
     * @var string
     */
    public string $singular = 'field';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'slug', 'layout', 'type', 'tab', 'object_type', 'order','required', 'accept', 'description'];

    protected $hidden = ['updated_at', 'deleted_at'];

    /**
     * @var string
     */
    public $table = 'fields';

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

        $conditions = [['field'=>'layout', 'value'=> 'field', 'operation' => '=']];

        $response = [
            (new Prop('name', 'Name', [], 3))->textInput(),
            (new Prop('slug', 'Slug', [], 3))->textInput(['maxlength' => 20 , 'required' => true]),
            (new Prop('object_type', 'Assigned to', [],3))->objectInput(new ObjectType()),
            (new Prop('layout', 'Layout', [], 3))->selectInput(['tab'=>'Tab', 'field' => 'Field']),

            (new Prop('description', 'Description', [], 3, true))->textAreaInput(),
            (new Prop('type', 'Type', $conditions, 3))->objectInput(new DataType()),
            (new Prop('enable', 'Enable', [], 2))->booleanInput(),
            (new Prop('required', 'Required', [], 2))->booleanInput(),
            (new Prop('editable', 'Editable', [], 2))->booleanInput(),
        ];


        if($self){
            $response = array_merge(
                $response,
                [
                    (new Prop('tab', 'Tab', array_merge($conditions, [
                        ['field' => 'object_type', 'value' => null, 'operation' => '!=']
                    ]), 3, false))->objectInput(new Field('?tab=1'), false, [['field'=>'object_type' ,'column'=>'object_type' ]]),

                    (new Prop('format','Format', [],2,true))->selectInput([
                        'collapse' => __('Collapse'),
                        'section' => __('Section'),
                    ]),

                    (new Prop('show_tab_name', 'Show Tab name', [], 2))->booleanInput(),

                    (new Prop('order', 'Order', [], 3))->intInput(),
                    (new Prop('width', 'Width', [], 3))->intInput(['max' => 12]),
                ]
            );
        }

        $response = array_merge(
            $response,
            [
                (new Prop('default', 'Default', $conditions, 3, false))->variableInput('type'),
                (new Prop('accept', 'Accept', [['field'=>'type', 'value'=> '15', 'operation' => '=']], 12))
                    ->textInput(['type' => 'textarea', 'isquill' => false])
            ]
        );


        return $this->getMergedFields($response);
    }


    /**
     * @param string $parameters
     * @return array
     */
    public static function newObject(string $parameters = "") : array{
        $type = DataType::query()->first();
        return [
            'id' =>0,
            'object_type' => null,
            'description' => '',
            'tab' => null,
            'name' => '',
            'slug' => '',
            'layout' => 'field',
            'format' => 'collapse',
            'show_tab_name' => true,
            'type' => $type,
            'options' => '[]',
            'default' => '',
            'editable' =>true,
            'width' => 4,
            'by_line' => 1,
            'accept' => 'application/vnd.rar, application/zip, application/gzip,
                    application/x-7z-compressed,
                    font/* ,audio/*, video/*, text/*, image/svg+xml, application/sql,
                    application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    application/vnd.oasis.opendocument.text,
                    application/vnd.openxmlformats-officedocument.presentationml.presentation,
                    application/vnd.oasis.opendocument.presentation,
                    application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                    application/vnd.oasis.opendocument.spreadsheet,
                    application/xml, application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/pdf',
            'enable' => true,
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
    public function tab(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'tab');
    }

    /**
     * @return HasMany
     */
    public function fields() : HasMany{
        return $this->hasMany(Field::class, 'tab' , 'id');
    }

    public function relations() : HasMany{
        return $this->hasMany(ObjectTypeRelation::class, 'tab', 'id');
    }

    /**
     * @return HasOne
     */
    public function type(): HasOne {
        return $this->hasOne(DataType::class, 'id', 'type');
    }

    /**
     * @return BelongsToMany
     */
    public function field_value(): BelongsToMany
    {
        return $this->belongsToMany(
            TheObject::class,
            'object_field_value',
            'id',
            'field')->withTimestamps();
    }
}
