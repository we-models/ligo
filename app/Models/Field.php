<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Activity;


class Field extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = [
        'object_type', 'name', 'slug', 'layout', 'type', 'options' ,
        'default', 'tab', 'order' , 'accept', 'editable', 'required',
        'visible_in_app', 'format', 'by_line', 'description'];

    /**
     * @var string
     */
    public string $singular = 'field';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'slug', 'layout', 'type', 'tab', 'object_type', 'order','required', 'accept', 'format', 'by_line', 'description'];

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
     */
    public function getFields(bool $self = false) : array
    {
        $has_business = self::getCurrentBusiness() != null;

        $conditions = [['field'=>'layout', 'value'=> 'field', 'operation' => '=']];
        $tab_format = [['field'=>'layout', 'value'=> 'tab', 'operation' => '=']];

        $response = [
            (new Prop('name', __('Name'), [], 4))->textInput(),
            (new Prop('slug', __('Slug'), [], 4))->textInput(['maxlength' => 20 , 'required' => true]),
            (new Prop('object_type', __('Assigned to'), [],4))->objectInput(new ObjectType()),


            (new Prop('description', __('Description'), [], 12, true))->textAreaInput(),

            (new Prop('layout', __('Layout'), [], 6))->selectInput(['tab'=>'Tab', 'field' => 'Field']),
            (new Prop('type', __('Type'), $conditions, 6))->objectInput(new DataType()),
            (new Prop('enable', __('Enable'), [], 3))->booleanInput(),
            (new Prop('visible_in_app', __('Visible in app'), [], 3))->booleanInput(),
            (new Prop('required', __('Required'), [], 3))->booleanInput(),
            (new Prop('editable', __('Editable'), [], 3))->booleanInput(),
            (new Prop('accept', __('Accept'), [['field'=>'type', 'value'=> '15', 'operation' => '=']], 12))
                ->textInput(['type' => 'textarea', 'isquill' => false]),
            (new Prop('format', __('Format'),$tab_format, 4))->selectInput(['COLUMN'=> __('Column'), 'ROW' => __('Row')]),
            (new Prop('by_line', __('By line'), $tab_format, 3))->textInput(['type' => 'number', 'min'=>1, 'max'=>6])
        ];


        if($self){
            $response = array_merge(
                $response,
                [
                    (new Prop('tab', __('Tab'), array_merge($conditions, [
                        ['field' => 'object_type', 'value' => null, 'operation' => '!=']
                    ]), 10, false))->objectInput(new Field('?tab=1'), false, [['field'=>'object_type' ,'column'=>'object_type' ]]),
                    (new Prop('order', __('Order'), [], 2))->intInput()
                ]
            );
        }

        $response = array_merge(
            $response,
            [
                (new Prop('default', __('Default'), $conditions, 6, false))->variableInput('type'),
                (new Prop('options', __('Options'), $conditions))->optionsInput('type')
            ]
        );

        if($has_business){
            $response = array_merge(
                $response,
                [
                    (new Prop('business', __('Business'), [], 12))->objectInput(new Business()),
                ]
            );
        }

        return $this->getMergedFields($response);
    }


    /**
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
            'type' => $type,
            'options' => '[]',
            'default' => '',
            'visible_in_app' => true,
            'editable' =>true,
            'format' => 'COLUMN',
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
            'business' => self::getCurrentBusiness(),
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
    public function business(): BelongsToMany {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
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
