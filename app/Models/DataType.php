<?php

namespace App\Models;

class DataType extends BaseModel
{
    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * @var string
     */
    public string $singular = 'datatype';

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @var string
     */
    public $table = 'data_type';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }


    /**
     * @param bool $self
     * @return array[]
     */
    public function getFields(bool $self = false) : array  {
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => 'Name' ],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ]
        ];
    }

}
