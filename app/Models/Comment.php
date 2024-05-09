<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Comment extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['comment', 'object', 'user'];

    /**
     * @var string
     */
    public string $singular = 'comment';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'comment', 'object', 'user', 'created_at'];

    /**
     * @var string
     */
    public $table = 'comments';

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
        $has_business = self::getCurrentBusiness() != null;
        $response = [
            (new Prop('comment', __('Comment'), [], 8))->largeTextInput(),
            (new Prop('object', __('Object'), [],4))->objectInput(new TheObject()),
            (new Prop('user', __('User'), [],4))->objectInput(new User()),
        ];
        if($has_business){
            $response = array_merge(
                $response,[(new Prop('business', __('Business'), [], 12))->objectInput(new Business())]
            );
        }
        return $this->getMergedFields($response);
    }

    /**
     * @param string $parameters
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'comment' => '',
            'object' => null,
            'user' => null,
            'business' => self::getCurrentBusiness(),
        ];
    }

    /**
     * @return HasOne
     */
    public function object(): HasOne
    {
        return $this->hasOne(TheObject::class, 'id', 'object');
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user');
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS)) return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    public function ratings(){
        return $this->hasMany(CommentRating::class, 'comment', 'id');
    }
}
