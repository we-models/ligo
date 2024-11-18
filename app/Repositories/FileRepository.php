<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Builder;

class FileRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'size',
        'extension'
    ];

    /**
     * @var array
     */
    public  array $includes = [ 'user', 'images'];

    public function getFieldsSearchable(): array
    {
        return $this->fields;
    }

    public function model():string
    {
        return File::class;
    }

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')]
        ];
    }

    public function search($param): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery(queryGenerator($this->fields, $param)));
    }

    public function formatQuery(): Builder
    {
        return createSearchQuery($this->includes, $this->allQuery());
    }
}
