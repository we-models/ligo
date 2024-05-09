<?php

namespace App\Interfaces;

use Spatie\Activitylog\LogOptions;

interface BaseModelInterface
{

    /**
     * @return array
     */
    public function getFields() : array;

    /**
     * @param string $parameters
     * @return array
     */
    public static function newObject(string $parameters = "") : array;

    /**
     * @return array
     */
    public function getPermissionsForModel(): array;

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions;

    /**
     * @return array
     */
    public function publicAttributes():array;

}
