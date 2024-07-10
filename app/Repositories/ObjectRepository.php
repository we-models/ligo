<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\TheObject;
use Illuminate\Database\Eloquent\Builder;

class ObjectRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'description', 'excerpt', 'object_type', 'visible', 'owner', 'parent', 'internal_id'];

    /**
     * @var array
     */
    public array $includes = ['owner', 'parent', 'object_type', 'images'];

    /**
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return ['name', 'description', 'excerpt', 'internal_id'];
    }

    /**
     * @return string
     */
    public function model() :string
    {
        return TheObject::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'internal_id' =>          ['name' => 'Internal ID'],
            'name' =>        ['name' => __('Name')],
            'description' => ['name' => __('Description')],
            'object_type' =>        ['name' => __('Object type')],
            // 'owner' => ['name' => __('Owner')],
            'created_at'=>   ['name' => __('Created at')]
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
