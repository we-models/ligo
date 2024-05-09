<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Activity;

class Slider extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name' , 'description', 'role'];

    /**
     * @var string
     */
    public string $singular = 'slider';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'description', 'role'];


    /**
     * @var string
     */
    public $table = 'sliders';


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
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],

            'role' => [
                'properties' => ['width' => 6, 'label' => __('Role')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'role',
                    'required' => false,
                    'multiple' => false ,
                    'data' => (new NewRole())->publicAttributes()
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
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => '',
            'description' => '',
            'images' => null,
            'business' => self::getCurrentBusiness(),
            'role' => null
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    public function role(){
        return $this->hasOne(NewRole::class, 'id', 'role');
    }


}
