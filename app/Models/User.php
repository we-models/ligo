<?php

namespace App\Models;

use App\Notifications\NewUserNotification;
use App\Notifications\ResetPasswordNotification;
use App\Properties\Prop;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
/**
 *
 */
class User extends Authenticatable
{
    use HasFactory, HasRoles, SoftDeletes, CascadeSoftDeletes, LogsActivity, Sortable, HasApiTokens, Notifiable;

    public string $singular = 'user';

    /**
     * @var string
     */
    public string $query = "";

    public function __construct(string $query = "")
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
        'lastname',
        'ndocument',
        'birthday',
        'ncontact',
        'email',
        'password',
        'code',
        'enable'
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
        'email_verified_at' => 'datetime'
    ];

    /**
     * @var array|string[]
     */
    public array $sortable = [
        'id',
        'name',
        'lastname',
        'ndocument',
        'email',
        'birthday',
        'ncontact',
        'enable'
    ];

    /**

     */
    public function getFields($self=false,array $keysToExclude = []): array
    {
        App::setLocale('es');
        $response = [];

        if (!in_array("enable",$keysToExclude)) {
            $response = array_merge($response,[
                (new Prop('enable', 'Enable', [], 12))->booleanInput(),
            ]);
        }

        $response = array_merge($response,[
        (new Prop('name', 'Name', [], 6))->textInput(),
        (new Prop('lastname', 'Last name', [], 6))->textInput(),
        ]);


        $response = array_merge($response,[
            (new Prop('ndocument', 'N of document', [], 4))->textInput(),
            (new Prop('birthday', 'Birthday', [], 4))->dateInput(),
            (new Prop('ncontact', 'Telef contacto', [], 4))->telInput()
        ]);

        $response = array_merge($response, [
            (new Prop('email', 'E-Mail Address', [], 8))->textInput(),
            (new Prop('images', 'Photo', [],4))->imageInput()
        ]);


        return $this->getMergedFields($response);
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getMergedFields(array $fields): array {
        $response = [];
        foreach ($fields as $field){
            $response = array_merge($response, $field);
        }
        return $response;
    }

    /**
     * @param null $parameters
     * @return array
     */
    public static function newObject($parameters = null) : array
    {
        return [
            'enable' => true,
            'images' => null,
            'name' => '',
            'lastname' => '',
            'email' => '',
            'ndocument' => '',
            'birthday' => '',
            'ncontact' => ''
        ];
    }

    /**
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
     * @return array
     */
    public function publicAttributes(): array
    {
        if(!str_starts_with($this->query, '?')){
            $this->query = '?'. $this->query;
        }

        return [
            'fields' => $this->getFields(),
            'values' => $this->newObject(),
            'url' => route($this->singular . '.all', app()->getLocale()) . $this->query,
            'index' => route($this->singular . '.index', app()->getLocale()) . $this->query
        ];
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return parent::getTable();
    }

    /**
     * @param Activity $activity
     * @param string $eventName
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        if($eventName == 'created' || $eventName == 'updated'){
            $props = $activity->properties->toArray();
            $props['attributes']['images'] = $this->images()->get()->toJson();
            $activity->properties = $props;
        }
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

    public function sendPasswordResetNotification($token)
    {
        setEmailConfiguration();
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return BelongsToMany
     */
    public function images(): BelongsToMany {
        return $this->belongsToMany(
            ImageFile::class,
            'model_has_image',
            'model_id',
            'image' )
            ->wherePivot('model_type', '=', get_class($this))
            ->wherePivot('field', '=', 'images')
            ->withTimestamps();
    }

}
