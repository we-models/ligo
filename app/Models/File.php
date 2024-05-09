<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kyslik\ColumnSortable\Sortable;

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
        'business',
        'user',
        'visibility',
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
    public array $sortable = ['created_at', 'name', 'size', 'extension', 'visibility'];

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
    /**
     * @return HasOne
     */
    public function business(): HasOne
    {
        return $this->hasOne(Business::class, 'id', 'business');
    }

    public function user(): HasOne{
        return $this->hasOne(User::class, 'id', 'user');
    }
}
