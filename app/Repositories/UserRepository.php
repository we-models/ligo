<?php


namespace App\Repositories;


use App\Interfaces\BaseRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 */
class UserRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'email', 'code'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY];


    /**
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fields;
    }

    /**
     * @param $param
     * @param bool $relation
     * @return Builder
     */
    public function search($param, bool $relation = true):Builder{
        return createSearchQuery($this->includes, $this->allQuery(queryGenerator($this->fields, $param)), $relation);
    }

    /**
     * @return string
     */
    public function model():string
    {
        return User::class;
    }

    /**
     * @return array
     */

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'email' =>        ['name' => __('Email')],
            'code' => ['code' => __('Code')],
            'created_at'=>   ['name' => __('Created at')],
            'business' => ['name' => __('Business')]
        ];
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes,  $this->allQuery());
    }
}
