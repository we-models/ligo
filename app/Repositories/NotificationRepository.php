<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'content', 'link', 'type'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'type', 'roles', 'images', 'users'];

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'content' =>      ['name' => __('Content')],
            'link' =>     ['name' => __('link')],
            'type' =>      ['name' => __('Type')]
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
        return Notification::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
