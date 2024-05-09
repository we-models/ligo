<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Configuration extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    protected $fillable = ['name', 'description' ,'default', 'type', 'custom_by_user'];

    /**
     * @var string
     */
    public string $singular = 'configuration';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'description', 'type'];

    /**
     * @var string
     */
    public $table = 'configurations';

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
            'custom_by_user' => [
                'properties' => ['width' => 6, 'label' => __('Customizable by user') ],
                'attributes' => ['type' => 'checkbox', 'class' => 'form-check-input']
            ],
            'description' => [
                'properties' => ['width' => 12, 'label' => __('Description')],
                'attributes' => ['type' => 'textarea']
            ],
            'type' => [
                'properties' => ['width' => 12, 'label' => __('Type')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'type',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new DataType())->publicAttributes()
                ]
            ],
            'default' => [
                'properties' => ['width' => 12, 'label' => __('Default')],
                'attributes' => ['type' => 'variable', 'decision' => 'type' , 'minlength' => 1,  'class' => 'form-control']
            ],
        ];
    }

    /**
     * @return array
     */
    public static function newObject(string $parameters = "") : array {
        $type = DataType::query()->first();
        return [
            'id' =>0,
            'name' => '',
            'description' => '',
            'custom_by_user' => false,
            'default' => '',
            'type' => $type
        ];
    }

    /**
     * @return HasOne
     */
    public function type(): HasOne {
        return $this->hasOne(DataType::class, 'id', 'type');
    }

    /**
     * @return HasOne
     */
    public function configuration():HasOne{
        $business =  Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
        return $this->hasOne(SystemConfiguration::class, 'configuration', 'id')
            ->where('business', '=', $business->id);
    }
}
