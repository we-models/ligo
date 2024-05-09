<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\RatingType;
use Illuminate\Database\Eloquent\Builder;


class RatingTypeRepository  extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'object_type'];

    /**
     * @var array
     */
    public array $includes = ['object_type'];

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
        return RatingType::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'object_type' =>        ['name' => __('Object type')],
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
