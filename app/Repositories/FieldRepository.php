<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Field;
use Illuminate\Database\Eloquent\Builder;

class FieldRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields = [
        'name', 'object_type', 'layout', 'type',
        'options' , 'default', 'tab', 'order', 'slug',
        'accept', 'required', 'format', 'by_line', 'description'
    ];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'type', 'object_type', 'tab' ];

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'object_type' => ['name' => 'Object type'],
            'name' =>        ['name' => __('Name')],
            'description' =>        ['name' => __('Description')],
            'slug' => ['name' => __('Slug')] ,
            'layout' =>        ['name' => __('Layout')],
            'type' =>        ['name' => __('Type')],
            'accept' => ['name' => __('Accept')],
            'options' =>        ['name' => __('Options')],
            'tab' =>        ['name' => __('Tab')],
            'format' => ['name' => __('Format')],
            'by_line' => ['name' => __('By line')],
            'created_at'=>   ['name' => __('Created at')],
            'business' =>   ['name' => __('Business')],
            'required' => ['name' => __('Required')]
        ];
    }
    /**
     * @return array|string[]
     */
    public function getFieldsSearchable(): array
    {
        return $this->fields;
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
     * @return string
     */
    public function model() :string
    {
        return Field::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
