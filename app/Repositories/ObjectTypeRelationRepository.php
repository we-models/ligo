<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\ObjectTypeRelation;
use Illuminate\Database\Eloquent\Builder;

class ObjectTypeRelationRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'slug', 'type', 'object_type', 'relation', 'tab', 'order', 'filling_method', 'required', 'description', 'instructions'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'object_type', 'relation', 'manipulated_by', 'tab', 'video'];

    /**
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function model() :string
    {
        return ObjectTypeRelation::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>             ['name' => 'ID'],
            'name' =>           ['name' => __('Name')],
            'description' =>    ['name' => __('Description')],
            'instructions' => ['name' => __('Instructions')],
            'slug' =>           ['name' => __('Slug')],
            'type' =>           ['name' => __('Type')],
            'filling_method' => ['name' => __('Filling method')],
            'object_type' =>    ['name' => __('Object type')],
            'relation' =>       ['name' => __('Relation')],
            'required' => ['name' => __('Required')]
        ];
    }

    /**
     * @param $param
     * @return Builder
     */
    public function search($param): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery(queryGenerator($this->fields, $param)));
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
