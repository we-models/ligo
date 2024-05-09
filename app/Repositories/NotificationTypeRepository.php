<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Builder;

class NotificationTypeRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
    * @var array|string[]
     */
    protected array $fields =['name', 'background', 'color', 'sound'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY];

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'sound' => ['name' => __('Sound')],
            'background' => ['name' => __('Background')],
            'color' => ['name' => __('Color')]
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
        return NotificationType::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
