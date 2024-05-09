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
        'accept', 'required', 'description', 'format', 'show_tab_name',
        'width'
    ];

    /**
     * @var array
     */
    public array $includes = ['type', 'object_type', 'tab' ];

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
            'format' =>        ['name' => __('Format')],
            'show_tab_name' =>    ['name' => __('Show tab name')],
            'slug' => ['name' => __('Slug')] ,
            'layout' =>        ['name' => __('Layout')],
            'type' =>        ['name' => __('Type')],
            'accept' => ['name' => __('Accept')],
            'options' =>        ['name' => __('Options')],
            'tab' =>        ['name' => __('Tab')],
            'created_at'=>   ['name' => __('Created at')],
            'required' => ['name' => __('Required')],
            'width' =>        ['name' => __('Width')],
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
