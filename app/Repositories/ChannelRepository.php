<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Builder;

class ChannelRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields = ['user1', 'user2', 'intermediary', 'name', 'profile_user1', 'profile_user2'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'user1', 'user2', 'intermediary' , 'profile_user1', 'profile_user2'];

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
    public function model():string
    {
        return Channel::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'user1' => ['name' => 'User 1'],
            'user2' =>        ['name' => __('User 2')],
            'intermediary' => ['name' => __('Intermediary')] ,
            'name' =>        ['name' => __('Channel')],
            'profile_user1' => ['name' => __('Profile 1')],
            'profile_user2' => ['name' => __('Profile 2')]
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
