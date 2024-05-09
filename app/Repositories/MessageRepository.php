<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;

class MessageRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields = ['transmitter', 'receiver', 'is_from_intermediary',  'channel', 'is_last', 'message', 'media'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'transmitter', 'receiver', 'channel' ];

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
        return Message::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'transmitter' => ['name' => 'Transmitter'],
            'receiver' =>        ['name' => __('receiver')],
            'is_from_intermediary' => ['name' => __('Is from intermediary')] ,
            'channel' =>        ['name' => __('Channel')],
            'is_last' => ['name' => __('Is last')],
            'message' => ['name' => __('Message')],
            'media' => ['name' => __('Media')]
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

