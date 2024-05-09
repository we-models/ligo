<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Link extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'url'];

    /**
     * @var string
     */
    public string $singular = 'link';

    /**
     * @var array|string[]
     */
    public array $sortable = ['name', 'url', 'group'];

    /**
     * @var string
     */
    public $table = 'links';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }

    /**
     * @param bool $self
     * @return array[]
     */
    public function getFields(bool $self = false) : array
    {
        $has_business = self::getCurrentBusiness() != null;
        $response = [
            'name' => [
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'group' => [
                'properties' => ['width' => 6, 'label' => __('Group')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'group',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new Group())->publicAttributes()
                ]
            ],
            'url' => [
                'properties' => ['width' => 12, 'label' => __('Url')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
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
            'url' => 'https://',
            'group' => null,
            'business' => self::getCurrentBusiness()
        ];
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
     * @return BelongsToMany
     */
    public function group(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'model_has_group',
            'model_id',
            'group' )
            ->wherePivot('model_type', '=', Link::class)
            ->withTimestamps();
    }

}
