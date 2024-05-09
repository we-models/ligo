<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Channel extends BaseModel implements BaseModelInterface
{
    use HasFactory, SoftDeletes, Sortable, LogsActivity, CascadeSoftDeletes;

    /**
     * @var string[]
     */
    public $fillable = ['user1', 'user2', 'intermediary', 'name', 'profile_user1', 'profile_user2'];

    /**
     * @var string
     */
    public string $singular = 'channel';

    /**
     * @var array|string[]
     */
    public array $sortable = ['user1', 'user2', 'intermediary', 'name', 'profile_user1' , 'profile_user2'];

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
            'name' => [
                'properties' => ['width' => 12, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => false, 'readonly' =>true, 'class' => 'form-control']
            ],
            'user1' => [
                'properties' => ['width' => 6, 'label' => __('User 1')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'user1',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new User())->publicAttributes()
                ]
            ],
            'profile_user1' => [
                'properties' => ['width' => 6, 'label' => __('Profile for user 1')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'profile_user1',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new TheObject())->publicAttributes()
                ]
            ],
            'user2' => [
                'properties' => ['width' => 6, 'label' => __('User 2')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'user2',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new User())->publicAttributes()
                ]
            ],
            'profile_user2' => [
                'properties' => ['width' => 6, 'label' => __('Profile for user 2')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'profile_user2',
                    'required' => true,
                    'multiple' => false ,
                    'data' => (new TheObject())->publicAttributes()
                ]
            ],
            'intermediary' => [
                'properties' => ['width' => 6, 'label' => __('Intermediary')],
                'attributes' => [
                    'type' => 'object',
                    'name' =>'intermediary',
                    'required' => false,
                    'multiple' => false ,
                    'readonly' => true,
                    'data' => (new User())->publicAttributes()
                ]
            ],


        ];
        if($has_business){
            $response = array_merge($response, [
                'business' => [
                    'properties' => ['width' => 6, 'label' => __('Business')],
                    'attributes' => [
                        'type' => 'object',
                        'name' =>'business',
                        'required' => true,
                        'multiple' => false ,
                        'data' => (new Business())->publicAttributes()
                    ]
                ],
            ]);
        }
        return $response;
    }

    /**
     * @return array
     */
    public static function newObject(string $parameters = "") : array
    {
        return [
            'id' =>0,
            'name' => Str::uuid(),
            'user1' => null,
            'profile_user1' => null,
            'user2' => null,
            'profile_user2' => null,
            'intermediary' => null,
            'business' => self::getCurrentBusiness()
        ];
    }

    public function user1():HasOne{
        return $this->hasOne(User::class, 'id', 'user1');
    }

    public function user2():HasOne{
        return $this->hasOne(User::class, 'id', 'user2');
    }

    public function intermediary():HasOne{
        return $this->hasOne(User::class, 'id', 'intermediary');
    }

    public function profile_user1():HasOne{
        return $this->hasOne(TheObject::class, 'id', 'profile_user1');
    }
    public function profile_user2():HasOne{
        return $this->hasOne(TheObject::class, 'id', 'profile_user2');
    }

    public function all_business(): BelongsToMany {
        return $this->belongsToMany(
            Business::class,
            'model_has_business',
            'model_id',
            'business' )
            ->wherePivot('model_type', '=', get_class($this))
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    public function messages(){
        return $this->hasMany(Message::class, 'channel', 'id');
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logAll();
    }
}
