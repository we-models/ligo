<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\ImageFile;
use Illuminate\Database\Eloquent\Builder;

class ImageRepository extends BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'height',
        'width' ,
        'size',
        'extension',
        'visibility',
        'thumbnail',
        'small',
        'medium',
        'large',
        'xlarge'
    ];

    /**
     * @var array
     */
    public  array $includes = ['business', 'user'];

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
        return ImageFile::class;
    }

    public function getReportable(): array
    {
        return [
            'id' =>          ['name' => 'ID'],
            'name' =>        ['name' => __('Name')]
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
