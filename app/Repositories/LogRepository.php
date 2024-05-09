<?php

namespace App\Repositories;


use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 */
class LogRepository extends BaseRepository
{
    /**
     * @var array|string[]
     */
    protected array $fields =['properties'];

    /**
     * @return string[]
     */
    public function getFieldsSearchable(): array
    {
        return ['properties'];
    }

    /**
     * @param $param
     * @return Builder|ActivityLog
     */
    public function search($param): Builder|ActivityLog
    {
        return createSearchQuery([], $this->allQuery(queryGenerator($this->fields, $param)));
    }


    /**
     * @return string
     */
    public function model(): string
    {
        return ActivityLog::class;
    }

    /**
     * @return array
     */
    public function getReportable(): array
    {
        return $this->fields;
    }
}
