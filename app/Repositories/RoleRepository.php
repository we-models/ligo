<?php


namespace App\Repositories;


use App\Interfaces\BaseRepositoryInterface;
use App\Models\NewRole;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 */
class RoleRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =['name', 'description'];
    /**
     * @var array|string[]
     */
    public array $includes = [];

    /**
     * @return array
     */
    public function getReportable () : array{
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')],
            'description' => ['name' => __('Description')],
            'groups'=> ['name' => __('Groups')],
            'created_at'=>   ['name' => __('Created at')]
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
    public function search($param):Builder{
        return createSearchQuery($this->includes, $this->allQuery(queryGenerator($this->fields, $param)));
    }

    /**
     * @return string
     */
    public function model():string
    {
        return NewRole::class;
    }

    /**
     * @return Builder
     */
    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
