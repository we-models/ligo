<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Configuration;
use Illuminate\Database\Eloquent\Builder;

class ConfigurationRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'description', 'default', 'type'];

    /**
     * @var array
     */
    public array $includes = ['configuration', 'type'];

    public function getFieldsSearchable(): array
    {
        return $this->fields;
    }

    public function model():string
    {
        return Configuration::class;
    }

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'description' => ['name' => __('Description')],
            'type' => ['name' => __('Type')],
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
