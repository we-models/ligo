<?php


namespace App\Repositories;


use App\Interfaces\BaseRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 */
class UserRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields =[
        'name',
        'lastname',
        'email',
        'code',
        'ndocument',
        'birthday',
        'ncontact','enable'
    ];

    /**
     * @var array
     */
    public array $includes = ['images'];


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
            'lastname' =>        ['name' => __('LastName')],
            'ndocument' =>        ['name' => __('Document number')],
            'birthday' =>        ['name' => __('Birthday')],
            'ncontact' =>        ['name' => __('Contact number')],
            'email' =>        ['name' => __('Email')],
            'code' => ['code' => __('Code')],
            'created_at'=>   ['name' => __('Created at')],
            'enable'=>   ['name' => __('Enable')]
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
