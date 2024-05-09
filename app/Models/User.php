<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Notifications\NewUserNotification;
use App\Notifications\ResetPasswordNotification;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use JetBrains\PhpStorm\ArrayShape;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
/**
 *
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, SoftDeletes, CascadeSoftDeletes, LogsActivity, Sortable, HasApiTokens, Notifiable;

    public bool $is_app = false;

    public string $singular = 'user';

    /**
     * @var string
     */
    public string $query = "";

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'name', 'email'];

    /**

     */
    public function getFields($self=false)
    {
        $business = Business::query();
        $business = getBusiness($business)->count();

        $response = [
            'name' => [
                'properties' => ['width' => 6, 'label' => __('Name')],
                'attributes' => ['type' => 'text', 'minlength' => 1, 'required' => true, 'class' => 'form-control']
            ],
            'email' => [
                'properties' => ['width' => 6, 'label' => __('Email') ],
                'attributes' => ['type' => 'email', 'required' => true, 'class' => 'form-control']
            ],
        ];
        if($business > 0){
            $response = array_merge($response, [
                'business' => [
                    'properties' => ['width' => 12, 'label' => __('Business')],
                    'attributes' => [
                        'type' => 'object',
                        'name' =>'business',
                        'required' => true,
                        'multiple' => true ,
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
    public static function newObject($pt = null) : array
    {
        $business = auth()->user()->business();
        $business = $business->count() > 0 ?  $business->get() : [];
        return [
            'id' =>0,
            'name' => '',
            'email' => '',
            'business' => $business,
            'code' => ''
        ];
    }

    /**
     * @param string $uri
     * @return array
     */
    public function getPermissionsForModel(): array
    {
        return requestPermission($this->singular);
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * @param Activity $activity
     * @param string $eventName
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        saveLogForBusiness($activity);
    }

    /**
     * @return array
     */
    public function publicAttributes(): array
    {
        return [
            'fields' => $this->getFields(),
            'values' => $this->newObject(),
            'url' => route($this->singular . '.all', app()->getLocale()),
            'index' => route($this->singular . '.index', app()->getLocale())
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function all_business(): BelongsToMany
    {
        return $this->belongsToMany(
            Business::class,
            'model_has_business',
            'model_id',
            'business' )
            ->wherePivot('model_type', '=', User::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( !Auth::check() || auth()->user()->hasAnyRole(ALL_ACCESS) || session(BUSINESS_IDENTIFY) == null){
            return $this->all_business();
        }
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return parent::getTable();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new NewUserNotification($this->is_app));
    }

    public function sendPasswordResetNotification($token)
    {
        setEmailConfiguration();
        $this->notify(new ResetPasswordNotification($this->is_app, $token));
    }

    public function reset(Request $request){

    }

    /**
     * @return BelongsToMany
     */
    public function manipulated_by(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_manipulations',
            'model_id',
            'user' )
            ->wherePivot('model_type', '=', get_class($this))
            ->withTimestamps();
    }
}


