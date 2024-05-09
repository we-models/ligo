<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Font;
use Illuminate\Database\Eloquent\Builder;

class FontRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'enable', 'url'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY];

    /**
     * @return array|string[]
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
        return Font::class;
    }


    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'enable' =>        ['name' => __('Enable')],
            'url'=>   ['name' => __('Url')],
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
