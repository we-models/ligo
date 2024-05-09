<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\ObjectType;
use Illuminate\Database\Eloquent\Builder;

class ObjectTypeRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'enable', 'description', 'type', 'slug', 'public', 'access_code'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'manipulated_by', 'fields'];

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
        return ObjectType::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'type' => ['name' => __('Type')],
            'enable' =>      ['name' => __('Enable')],
            'public' =>      ['name' => __('Is public')],
            'access_code' => ['name' => __('Access code') ],
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
