<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\DataType;
use Illuminate\Database\Eloquent\Builder;

class DataTypeRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name'];

    /**
     * @var array
     */
    public  array $includes = [];

    public function getFieldsSearchable():array
    {
        return $this->fields;
    }

    public function model():string
    {
        return DataType::class;
    }

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')]
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
