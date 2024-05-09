<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Builder;

class SliderRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields = ['name', 'description', 'role'];
    /**
     * @var array
     */
    public  array $includes = ['images', BUSINESS_IDENTIFY, 'role'];


    public function getFieldsSearchable()
    {
        return $this->fields;
    }

    public function model():String
    {
        return Slider::class;
    }

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'created_at'=>   ['name' => __('Created at')],
            'role' => ['name' => __('Role')]
        ];
    }

    public function search($param): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery(queryGenerator($this->fields, $param)));
    }

    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
