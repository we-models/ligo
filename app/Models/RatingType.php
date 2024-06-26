<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RatingType extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'object_type'];

    /**
     * @var string
     */
    public string $singular = 'rating_type';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'object_type'];

    /**
     * @var string
     */
    public $table = 'rating_types';

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
        $response = [
            (new Prop('name', 'Name', [], 8))->textInput(),
            (new Prop('object_type', 'Object type', [],4))->objectInput(new ObjectType()),
        ];
        return $this->getMergedFields($response);
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
            'object_type' => null
        ];
    }

    /**
     * @return HasOne
     */
    public function object_type(): HasOne
    {
        return $this->hasOne(ObjectType::class, 'id', 'object_type');
    }
}
