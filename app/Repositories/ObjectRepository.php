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
    protected array $fields =['name', 'description', 'excerpt', 'object_type', 'visible', 'owner', 'parent'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'owner', 'parent', 'object_type', 'images'];

    /**
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return ['name', 'description', 'excerpt'];
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
            'name' =>        ['name' => __('Name')],
            'description' => ['name' => __('Description')],
            'excerpt' =>        ['name' => __('Excerpt')],
            'object_type' =>        ['name' => __('Object type')],
            'visible' =>      ['name' => __('Visible')],
            'owner' => ['name' => __('Owner')],
            'parent' => ['name' => __('Parent')],
            'created_at'=>   ['name' => __('Created at')],
            'business' =>   ['name' => __('Business')]
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
