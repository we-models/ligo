<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

class File extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = [
        'name',
        'size',
        'extension',
        'mimetype',
        'user',
        'url',
        'permalink'
    ];

    /**
     * @var string
     */
    public string $singular = 'file';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id','created_at', 'name', 'size', 'extension'];

    protected $hidden = ['updated_at', 'deleted_at'];

    /**
     * @var string
     */
    public $table = 'files';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }


    public function publicAttributes(): array {
        return [
            'url' => route($this->singular .'.all', app()->getLocale()),
            'store' => route($this->singular . '.store', app()->getLocale()),
            'sorts' => $this->sortable
        ];
    }

    public function user(): HasOne{
        return $this->hasOne(User::class, 'id', 'user');
    }
}
