<?php


namespace App\Repositories;


use App\Interfaces\BaseRepositoryInterface;
use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 */
class GroupRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'icon'];

    /**
     * @var array
     */
    public array $includes = [BUSINESS_IDENTIFY, 'roles', 'permissions'];

    /**
     * @return array
     */
    public function getReportable():array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'icon' =>        ['name' => __('Icon')],
            'created_at'=>   ['name' => __('Created at')],
            'business' =>   ['name' => __('Business')],
            'roles' =>   ['name' => __('Roles')],
            'permissions' =>   ['name' => __('Permissions')]
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
        return Group::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}

