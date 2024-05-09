<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'content', 'link', 'type'];

    /**
     * @var string
     */
    public string $singular = 'notification';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'content', 'link', 'type'];

    /**
     * @var string
     */
    public $table = 'notifications';

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
            (new Prop('name', __('Name'), [], 6, true))->textInput(),
            (new Prop('content', __('Content'), [], 12, true))->largeTextInput(),
            (new Prop('link', __('Link'), [], 6, false))->textInput(),
            (new Prop('type', __('Type'), [], 6, true))->objectInput(new NotificationType()),
            (new Prop('images', __('Image'), [], 4))->imageInput(),
            (new Prop('roles', __('Recipients'), [], 4, true))->objectInput(new NewRole(), true),
            (new Prop('users', __('Users'), [], 4, true))->objectInput(new User(), true),

        ];
        if($has_business){
            $response = array_merge(
                $response,[(new Prop('business', __('Business'), [], 12))->objectInput(new Business())]
            );
        }
        return $this->getMergedFields($response);
    }

    /**

     */
    public static function newObject(string $parameters = "") : array   {
        return [
            'id' =>0,
            'name' => '',
            'content' => '',
            'link' => '',
            'type' => null,
            'image' =>null,
            'roles' => [],
            'users' => [],
            'business' => self::getCurrentBusiness(),
        ];
    }

    public function type(){
        return $this->hasOne(NotificationType::class, 'id', 'type');
    }

    public function receivers(){
        return $this->hasMany(NotificationReceiver::class, 'notification', 'id');
    }

    public function roles(){
        $relation = $this->belongsToMany(
            NewRole::class,
            'notification_receivers',
            'notification',
            'role'
        );
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  {
            return $relation;
        }
        return $relation->whereIn('name', auth()->user()->getRoleNames());
    }

    public function users(){
        $relation = $this->belongsToMany(
            User::class,
            'notification_receivers',
            'notification',
            'user'
        );
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  {
            return $relation;
        }
        return $relation->whereHas(BUSINESS_IDENTIFY);
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }
}
