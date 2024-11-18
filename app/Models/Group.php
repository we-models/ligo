<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'icon'];

    /**
     * @var string
     */
    public string $singular = 'group';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'icon'];

    /**
     * @var string
     */
    public $table = 'groups';

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
            (new Prop('name', 'Name', [], 4))->textInput(),
            (new Prop('icon', 'Icon', [],4))->objectInput(new Icon()),
        ];

        return $this->getMergedFields($response);
    }

    /**

     */
    public static function newObject(string $parameters = "") : array   {
        return [
            'id' =>0,
            'name' => '',
            'icon' => null
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            NewRole::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', NewRole::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            NewPermission::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', NewPermission::class)
            ->withTimestamps();
    }

    public function icon(){
        return $this->hasOne(Icon::class, 'id', 'icon');
    }

    /**
     * @return BelongsToMany
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(
            Link::class,
            'model_has_group',
            'group',
            'model_id' )
            ->wherePivot('model_type', '=', Link::class)
            ->withTimestamps();
    }
}
