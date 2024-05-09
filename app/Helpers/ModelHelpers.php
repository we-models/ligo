<?php

use App\Models\Business;
use App\Models\Configuration;
use App\Models\File;
use App\Models\Group;
use App\Models\Icon;
use App\Models\ImageFile;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\SystemConfiguration;
use App\Models\TheObject;
use App\Models\User;
use App\Models\NewRole;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Collection;

/**
 *  THE ROLES WITH ALL PERMISSIONS BY DEFAULT
 */
const ALL_ACCESS = ['Developer', 'Super Admin'];

const DEFAULT_PERMISSIONS = ['.create','.show', '.edit', '.all', '.destroy', '.logs'];

const DEFAULT_PAGINATION = 10;

const BUSINESS_IDENTIFY= 'business';

const POSSIBLE_DATES =  ['created_at', 'updated_at', 'deleted_at'];

const IMAGE_LOCATIONS  = ['public', 'private', 'business'];

const APP_URL = "https://app.weagencyworld.com";

const MIMES = [
    'application/vnd.rar',
    'application/zip',
    'application/gzip',
    'application/x-7z-compressed',
    'font/*',
    'audio/*',
    'video/*',
    'text/*',
    'image/svg+xml',
    'application/sql',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.oasis.opendocument.text',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.oasis.opendocument.presentation',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.oasis.opendocument.spreadsheet',
    'application/xml',
    'application/msword',
    'application/vnd.ms-excel',
    'application/vnd.ms-powerpoint',
    'application/pdf',
];

const IMG_MIMES = ['jpeg','png','jpg','webp','gif'];

/**
 * @param string $search
 * @param BaseRepository $repository
 * @param string $subject
 * @return mixed
 *
 * This method allow search all logs by params on search
 * The results include "causer"
 * Only show the results from allowed business|companies
 * $subject is the name of the class
 */
function masterLogQuery(string $search, BaseRepository $repository, string $subject): mixed {
    $response = $repository->search($search)->with('causer');
    if( $subject != Business::class && method_exists($subject, BUSINESS_IDENTIFY)) $response = $response->whereHas(BUSINESS_IDENTIFY);
    return $response->where('subject_type', $subject);
}


/**
 * @param Request $request
 * @param string $subject
 * @param BaseRepository $repository
 * @param $callback
 * @return Response|JsonResponse
 */
function getAllModelLogs(Request $request, string $subject, BaseRepository $repository, $callback = null): Response|JsonResponse {

    //GET PARAMETERS FOR SEARCH, OFFSET AND PAGINATION
    $rq = getRequestParams($request);
    $offset = $rq->offset;
    $paginate = $rq->paginate;

    $logs = masterLogQuery($rq->search,$repository,  $subject);
    if($callback != null && $logs != null){
        $logs = $callback($logs);
    }
    $logs = $logs->orderBy('created_at', 'desc');

    if(isset($request['pdf'])){
        //THE FUNCTION EXPORT ON PDF THE CURRENT ELEMENTS ON SCREEN
        $logs = $logs->take($paginate)->offset($offset* $paginate)->get();
        $pdf = Pdf::loadView('exports.log', ['logs' => $logs])->setPaper('a4');
        return $pdf->download(__('Report').'.pdf');
    }
    //SHOW ALL RESULTS AS PAGINATION
    return response()->json($logs->paginate($paginate));
}

/**
 * @param string $uri
 * @return array
 *
 * Send to frontend all permission allowed for the logged user if the route exists
 */
function requestPermission(string $uri): array {
    $p = [];
    if(!Auth::check()) return $p;
    foreach (DEFAULT_PERMISSIONS as $c){
        if(Route::has($uri . $c ) && Auth::user()->can($uri . $c)) $p[] = $c;
    }
    return $p;
}


/**
 * @param array $includes
 * @param Builder $query
 * @param bool $rel_includes
 * @return mixed
 *
 * Add the relation details to the current query
 */
function createSearchQuery(array $includes, Builder $query, bool $rel_includes = true, $excluded = []): Builder {
    if($rel_includes){
        foreach ($includes as $include){
            $query = $query->with($include);
        }
    }
    foreach ($excluded as $exclude){
        $query->whereNot($exclude['column'], $exclude['operator'], $exclude['value']);
    }
    return $query;
}

/**
 * @param int $length
 * @return string
 *
 * This function is used to generate new passwords for new users
 */
function generateRandomString(int $length = 8): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateUserCode(): string
{
    return date('m') . date('d') . date('H') . date('i') . date('s') .  generateRandomString(4);
}

/**
 * @param Builder $query
 * @return Builder
 *
 * This function filter the "business" table with the allowed business
 */
function getBusiness(Builder $query): Builder {
    $user = Auth::user();
    if($user->hasAnyRole(ALL_ACCESS)){
        return $query;
    }else{
        $business = $user->business()->pluck((new Business)->getTable() . '.id')->toArray();
        return $query->whereIn('id', $business);
    }
}

/**
 * @param null $id
 * @return bool
 *
 * This function return true only if the logged user have permissions for the business
 */
function userCanViewBusiness($id= null): bool {
    if(!isset($id)) return true;
    $user = auth()->user();
    //GET ALL BUSINESS WITH PERMISSIONS FOR THE CURRENT USER
    $allowedBusiness =  $user->business()->pluck((new Business)->getTable() . '.id')->toArray();
    return in_array($id, $allowedBusiness) || $user->hasAnyRole(ALL_ACCESS);
}

/**
 * @param $request
 * @return stdClass
 *
 * This function is used for format the parameters for lists
 */
#[Pure]
function getRequestParams($request): stdClass {
    $page = (int) $request['page'];
    $response = new stdClass();
    $response->search = $request['search'] ?? '';
    $response->offset = $page > 0 ? $page -1 : 0;
    $response->paginate = isset($request['paginate']) ? (int) $request['paginate'] : DEFAULT_PAGINATION;
    $response->pdf = isset($request['pdf']);
    if(isset($request['type'])){
        $response->type = $request['type'];
    }
    return $response;
}

/**
 * @param array $keys
 * @param string $value
 * @return array
 *
 * This function use key=>value to create an array for search on columns
 */
function queryGenerator(array $keys, string $value): array {
    $response  = [];
    foreach ($keys as $key){
        $response = array_merge($response, [$key => $value]);
    }
    return $response;
}


/**
 * @param Activity $activity
 *
 * Each Business has own logs. With this function the system save a relation for logs with business
 */
function saveLogForBusiness(Activity $activity) {
    $business = Business::query()->where('code', session("business"))->first();
    if($business == null) return;
    $statement = DB::select("show table status like '".$activity->getTable()."'")[0]->Auto_increment;
    //THIS PROCESS OCCURS TWO TIMES BUT WE ONLY NEED THE FIRST.
    DB::table('model_has_business')->insertOrIgnore([
        'model_type' => Activity::class,
        'model_id' => $statement,
        'business' => $business->id
    ]);
}

/**
 * Default function to get all icons
 */
function getAllIcons(): Collection
{
    return Icon::all();
}


/**
 * @param array $item
 * @param string $key
 * @return mixed
 */
function getPDFValues(array $item, string $key): mixed {
    if(!is_array($item[$key])) {
        if(in_array($key, POSSIBLE_DATES))  return CarbonImmutable::parse($item[$key])->format('d-m-Y H:i:s');
        return $item[$key];
    }
    if(isset($item[$key]['id'])) return $item[$key]['id'] . ': ' . $item[$key]['name'];
    $the_item = array_map(fn($i)=> '<p>' . $i['id'] . ': ' . $i['name'] .'</p>' , $item[$key]);
    return implode("", $the_item);
}

/**
 * @param $unsafeFilename
 * @return array|string
 */
function filename_sanitizer($unsafeFilename): array|string {
    $dangerousCharacters = array('"', "'", "&", "/", "\\", "?", "#", "<", ">", "%", ",", "*", ":", "?", "|", "^", "~", "`");
    return str_replace($dangerousCharacters, '_', $unsafeFilename);
}

/**
 * @param bool $showMenu
 * @return array|stdClass
 */
function getPermissionTree(bool $showMenu = false): array|stdClass {
    $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->with('roles')->with('users')->first();
    if ($business == null) return [];

    $roles = array_map(fn($role) => $role['name'], $business->roles()->get()->toArray());

    $roles = array_intersect($roles, auth()->user()->getRoleNames()->toArray());

    $object_types = ObjectType::with('roles')->whereHas('roles', function ($q) use ($roles){
        $q->whereIn("name", $roles);
    })->pluck('id')->toArray();

    $roles = NewRole::query()->whereIn('name', $roles)
        ->with('permissions', function ($q) use ($showMenu) {
            $q->with('groups');
            if($showMenu) $q->where('show_in_menu', true);
        })->get();

    $all_roles = [];
    $groups = [];
    $permissions = [];

    foreach ($roles as $role){
        $all_roles = array_merge($all_roles, [$role->id]);
        $permissions = array_merge($permissions, array_map(fn($q)=>$q['id'], $role->permissions->toArray()));
        foreach ($role->permissions as $permission){
            $groups = array_merge($groups, array_map(fn($g)=>$g['id'], $permission->groups->toArray()));
        }
    }
    $groups = array_unique($groups);
    $permissions = array_unique($permissions);

    $response = new stdClass();
    $response->groups = $groups;
    $response->permissions = $permissions;
    $response->roles = $all_roles;
    $response->users = $business->users()->pluck('users.id')->toArray();
    $response->business = [$business->id];
    $response->objectTypes = $object_types;
    return $response;
}

/**
 * @return Collection|array
 */
function getGroupsForMenu(): Collection|array{
    $roles_groups = getPermissionTree(true);
    return Group::query()->whereIn('id', array_unique($roles_groups->groups))
        ->with('permissions', function($q) use ($roles_groups){
            $q->where('show_in_menu', true);
            $q->whereIn('permissions.id', $roles_groups->permissions);
        })->with('links', function($q) {
            $q->orderBy('name', 'ASC');
            $q->whereHas(BUSINESS_IDENTIFY);
        })->get();
}

/**
 * @param String $name
 * @return array
 */
function getConfiguration(String $name): array {
    $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
    if($business == null){
        $business = Business::query()->first();
    }

    if(Auth::check()){
        $configuration = SystemConfiguration::query()
            ->whereHas('configuration', function ($q) use ($name){
                $q->where(['name'=> $name, 'custom_by_user' => true ]);
            })->where([BUSINESS_IDENTIFY => $business->id, 'user' => auth()->user()->getAuthIdentifier()])->first();
    }


    if(empty($configuration)) {
        $configuration = SystemConfiguration::query()
            ->whereHas('configuration', function ($q) use ($name){
                $q->where('name', $name);
            })->where(BUSINESS_IDENTIFY, $business->id)->first();
    }
    if(empty($configuration)) return ["exists" => false, "config" => Configuration::query()->where('name', $name)->first()];
    return ["exists" => true, "config" => $configuration];
}

/**
 * @param string $name
 * @return string
 */
function getConfigValue(string $name): string {
    $config = getConfiguration($name);
    if($config['exists']) return $config['config']->value??"";
    else return $config['config']->default??"";
}

function setEmailConfiguration(){
    $config = array(
        'driver'     => getConfigValue('MAIL_MAILER' ),
        'host'       => getConfigValue( 'MAIL_HOST'),
        'port'       => intval(getConfigValue( 'MAIL_PORT')),
        'encryption' => getConfigValue('MAIL_ENCRYPTION'),
        'username'   => getConfigValue('MAIL_USERNAME'),
        'password'   => getConfigValue('MAIL_PASSWORD'),
        'sendmail'   => '/usr/sbin/sendmail -bs',
        'pretend'    => false,
        'from' => [
            'address' => getConfigValue('MAIL_USERNAME'),
            'name' => getConfigValue('MAIL_USERNAME'),
        ],
    );
    Config::set('mail', $config);
}

function cleanString(String $string = "") {
    // Replace spaces with dashes
    $string = str_replace(' ', '-', $string);

    // Convert everything to lowercase
    $string = strtolower($string);

    // Replace accented vowels with their unaccented version
    $string = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $string);

    // Remove special characters

    return preg_replace('/[^a-z0-9\-]/', '', $string);
}

function removeNull($data, $eraseNull){
    if(!$eraseNull || $data == null) return $data;
    return array_filter($data, fn ($value) => $value !== null);
}

function getCustomFieldsRelations(string $parameters, $element, int $object = 0, bool $showData = true, bool $eraseNull = false): array
{
    $response = [];
    $parameters = explode('&', $parameters);
    foreach ($parameters as $parameter){
        $parameter = explode('=', $parameter);
        if($parameter[0] != 'object_type') continue;
        $object_type = ObjectType::query()->where('id', $parameter[1] )
            ->with('relations_with', function ($q){
                $q->whereNull('tab')->with('relation', function($query){
                    $query->where('enable', true);
                });
            })
            ->with('fields', function ($q){
                $q->where(['layout'=> 'tab', 'enable' => true])->orWhere(function ($query){
                    $query->where(['layout'=> 'field', 'enable' => true])->whereNull('tab');
                });
                $q->with('type')->with('fields.type')->with('relations', function ($q){
                    $q->with('object_type');
                    $q->with('relation', function($query){
                        $query->where('enable', true);
                    });
                });
                $q->where('enable', true);
            })->where('enable', true)
            ->first()->toArray();

        $object_type['fields'] = array_map(function($item) use ($object, $element, $showData, $eraseNull){

            $item['relations'] = array_filter($item['relations'], function($rl){
                return $rl['relation'] !== null;
            });

            $item['fields'] = array_map(function($field) use ($object, $showData, $eraseNull) {
                $f = formatField($field, $object, $showData);
                return removeNull($f, $eraseNull);
            }, $item['fields']);
            $item['fields'] = array_merge($item['fields'], array_map(function($relation) use ($object, $element, $showData, $eraseNull) {
                $returned = formatRelation($relation, $element, $object, $showData, $eraseNull);
                return $returned;
            }, $item['relations']));
            unset($item['relations']);
            usort($item['fields'],fn($first,$second) => $first['order'] > $second['order']);
            return removeNull($item, $eraseNull);
        }, $object_type['fields']);

        $response = array_merge($response, array_map(function ($field) use ($object, $showData){
            return formatField($field, $object, $showData);
        }, $object_type['fields']));

        $response = array_merge($response, array_map(function ($relation) use ($object, $element, $showData, $eraseNull) {
            $returned = formatRelation($relation, $element,$object, $showData, $eraseNull);
            return $returned;
        }, $object_type['relations_with']));

        usort($response,fn($first,$second) => $first['order'] > $second['order']);
    }
    return $response;
}

function formatField($field, $object, $showData = true){
    $field['status'] = 'field';

    if(isset($field['type']['name']) && $field['type']['name'] == 'Map'){
        $latLang = DB::table('object_field_value')->where(['object' => $object, 'field' => $field['id']])->select('id', 'object', 'field', 'value')->get()->toArray();

        $field['latitude'] = null;
        $field['longitude'] =  null;
        if(count($latLang) > 0){
            $field['latitude'] = $latLang[0];
            $field['longitude'] = $latLang[1];
        }

    }else{
        $field['value'] = DB::table('object_field_value')->where(['object' => $object, 'field' => $field['id']])->select('id', 'object', 'field', 'value')->first();
    }

    if(!isset($field['type']['name'])) {
        $field['type'] = ['name' => 'Tab'];
        return $field;
    }
    if($field['type']['name'] == 'Image' ){
        $field['data'] = [
            'type' => 'object',
            'name' => $field['name'],
            'required' => false,
            'multiple' => false
        ];
        if($showData){
            $field['data']['data'] = (new ImageFile())->publicAttributes();
        }
        $field['values'] = $field['value'];
        if(!empty($field['value'])){
            $field['values'] = ImageFile::find($field['value']->value);
        }
        $field['entity'] = [$field['slug'] => $field['values']];
        unset($field['values']);
    }

    if($field['type']['name'] == 'File' ){
        $field['data'] = [
            'type' => 'object',
            'name' => $field['name'],
            'required' => false,
            'multiple' => false,
        ];
        if($showData){
            $field['data']['data'] = (new File())->publicAttributes();
        }

        $field['values'] = $field['value'];
        if(!empty($field['value'])){
            $field['values'] = File::query()->where('id', $field['value']->value)->with('images')->first();
        }

        $field['entity'] = [$field['slug'] => $field['values']];
        unset($field['values']);
    }
    return $field;
}

function formatRelation($relation, $element, $object, $showData = true, $eraseNull = false){
    $relation['status'] = 'relation';
    $obj = new TheObject('?object_type=' . $relation['relation']['id']);

    $relation['data'] = [
        'type' => 'object',
        'name' => $relation['name'],
        'required' => false,
        'multiple' => $relation['type'] == 'multiple'
    ];
    if($showData){
        $relation['data']['data'] = $obj->publicAttributes();
    }

    $rl = DB::table('object_relations')->where(['relation_object'=> $relation['id'], 'object' => $object]);
    if($relation['type'] == 'unique'){
        $rl = $rl->first();
        $toGetWith = array_diff($element->objectRepository->includes, [BUSINESS_IDENTIFY]);
        $relation['values'] = empty($rl) ?  null : $element->objectRepository->makeModel()->with($toGetWith)->find($rl->relation)->toArray();
        unset($relation['deleted_at']);
        if(!empty($relation['values'])){
            $relation['values']['custom_fields'] = getCustomFieldsRelations("object_type=" . $relation['values']['object_type']['id'], $element,  $relation['values']['id'], false);
            $relation['values']['has_custom_fields'] = count($relation['values']['custom_fields'] ) > 0;
        }
        $relation['values'] = removeNull($relation['values'], $eraseNull);
        if($relation['values'] != null){
            $relation['values']['object_type'] = removeNull($relation['values']['object_type'], $eraseNull);
        }

    }else{
        $rl = $rl->pluck('object_relations.relation')->toArray();
        $relation['values'] = empty($rl) ? [] : $element->objectRepository->makeModel()
            ->with($element->objectRepository->includes)->whereIn('id', $rl)->get()->toArray();
        if(count($relation['values']) > 0){
            $relation['values'] = array_map(function($rv) use ($element, $eraseNull){
                $rv['custom_fields'] = getCustomFieldsRelations("object_type=" . $rv['object_type']['id'], $element,  $rv['id'], false);
                $rv['has_custom_fields'] = count($rv['custom_fields'] ) > 0;
                $rv['object_type'] = removeNull($rv['object_type'], $eraseNull);
                return removeNull($rv, $eraseNull);
            }, $relation['values']);
        }
    }
    $relation['entity'] = [$relation['slug'] => $relation['values']];
    unset ($relation['values']);
    return $relation;
}

function parseWpData($id){

    $obj = TheObject::query()->where('id', $id)
        ->with(['object_type', 'field_value', 'owner', 'parent', 'images',  'field_value.type', 'relation_value'])
        ->first()->toArray();

    if(!empty($obj['relation_value'])){
        $rl = [];

        $obj['relation_value'] = array_filter($obj['relation_value'], function ($rlv){
            $rlv = $rlv['pivot']['relation_object'];
            $rlv = ObjectTypeRelation::find($rlv);
            return $rlv !== null;
        });

        foreach ($obj['relation_value'] as $relation){
            $rl_obj = ObjectTypeRelation::query()->where('id', $relation['pivot']['relation_object'] )->first();
            $rl_obj = $rl_obj->toArray();

            if($rl_obj['type'] == 'unique'){
                $rl[] = [
                    'relation_object_id' =>  $rl_obj['id'],
                    'relation_type' => $rl_obj['type'],
                    'relation_object' => $rl_obj,
                    'relation' => TheObject::query()->where('id', $relation['pivot']['relation'] )->first()->toArray()
                ];
            }else{
                $ml =  array_search($rl_obj['id'], array_column($rl, 'relation_object_id'));
                if(empty($ml)){
                    $rl[] = [
                        'relation_object_id' =>  $rl_obj['id'],
                        'relation_object' => $rl_obj,
                        'relation' => [
                            TheObject::query()->where('id', $relation['pivot']['relation'] )->first()->toArray()
                        ]
                    ];
                }else{
                    $rl[$ml]['relation'][] = TheObject::query()->where('id', $relation['pivot']['relation'] )->first()->toArray() ;
                }
            }
        }
        $obj['relation_value'] = $rl;
    }
    return $obj;
}



/**
 * @throws GuzzleException
 */
function syncWp(array $request, string $url= '', string $method = 'POST')
{
    $wp_link = getConfigValue('WP_LINK');
    if(empty($wp_link)) return null;
    $wp_link = $wp_link . '/wp-json/v1/' . $url;
    $client = new Client();
    $body = $client->request($method, $wp_link, [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            BUSINESS_IDENTIFY => session(BUSINESS_IDENTIFY)
        ],
        'json' => $request,
        'verify' => false,
        'http_errors' => false,
        'http_version' => '2.0',
        //'debug' => true,
        'timeout' => 3000
    ]);
    return $body->getBody()->getContents();
}
