<?php

namespace App\Interfaces;
use Illuminate\Database\Eloquent\Builder;

interface BaseRepositoryInterface
{
    /**
     * @return array
     */
    public function getReportable () : array;

    /**
     * @param $param
     * @return Builder
     */
    public function search($param) : Builder;

    /**
     * @return string
     */
    public function model(): string;

    /**
     * @return Builder
     */
    public function formatQuery(): Builder;
}
