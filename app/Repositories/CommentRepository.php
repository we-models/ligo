<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

class CommentRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['comment', 'object', 'user'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY,  'object', 'user'];

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
        return Comment::class;
    }

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'comment' =>        ['name' => __('Comment')],
            'object' =>        ['name' => __('Object')],
            'user' =>        ['name' => __('User')],
            'created_at'=>   ['name' => __('Created at')],
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
