<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SystemConfiguration extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['configuration', 'value', 'user'];

    /**
     * @var string
     */
    public string $singular = 'system';

    /**
     * @var string
     */
    public $table = 'system_configs';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }


    /**
     * @return HasOne
     */
    public function configuration():HasOne{
        return $this->hasOne(Configuration::class, 'id', 'configuration');
    }

    /**
     * @return HasOne
     */
    public function user():HasOne{
        return $this->hasOne(User::class, 'id', 'user');
    }
}
