<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
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
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => 'Name'],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'group' => [
                'properties' => ['width' => 6, 'label' => 'Group'],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'group',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new Group())->publicAttributes()
                ]
            ],
            'url' => [
                'properties' => ['width' => 12, 'label' => 'Url'],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],

        ];
    }

    /**
     * @param string $parameters
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => '',
            'url' => 'https://',
            'group' => null
        ];
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
