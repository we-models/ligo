<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icon extends BaseModel implements BaseModelInterface
{
    public $fillable = ['name'];

    /**
     * @var string
     */
    public string $singular = 'icon';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name'];

    /**
     * @var string
     */
    public $table = 'icons';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }

    /**
     * @param bool $self
     * @return array[]
     */
    public function getFields(bool $self = false) : array
    {
        return [
            'name' => [
                'properties' => ['width' => 6, 'label' => 'Name'],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ]
        ];
    }

    public static function newObject(string $parameters = "") : array   {
        return [
            'id' =>0,
            'name' => ''
        ];
    }
}
