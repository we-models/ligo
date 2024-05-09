<?php


namespace App\Repositories;


use App\Interfaces\BaseRepositoryInterface;
use App\Models\NewPermission;
use Illuminate\Database\Eloquent\Builder;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 */
class PermissionRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'guard_name', 'detail', 'show_in_menu', 'created_at'];
    /**
     * @var array|string[]
     */
    public array $includes = ['groups', BUSINESS_IDENTIFY];

    /**
     * @return array
     */

    public function getReportable(): array
    {
        return [
            'id' =>             ['name' => 'ID'],
            'name' =>           ['name' => __('Name')],
            'guard_name' =>     ['name' => __('Guard')],
            'detail' =>         ['name' => __('Detail')],
            'show_in_menu' =>   ['name' => __('Show in menu')],
            'groups'=> ['name' => __('Groups')],
            'created_at'=>      ['name' => __('Created at')]
        ];
    }

    /**
     * @return array
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
    public function model(): string
    {
        return NewPermission::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes,  $this->allQuery());
    }
}
