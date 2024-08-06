<?php

use App\Models\Configuration;
use App\Models\File;
use App\Models\Group;
use App\Models\Icon;
use App\Models\ImageFile;
use App\Models\NewRole;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\SystemConfiguration;
use App\Models\TheObject;
use App\Models\User;
use App\Repositories\BaseRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Response;


const ALL_ACCESS = ['Developer', 'Super Admin'];
const DEFAULT_PAGINATION = 10;
const DEFAULT_PERMISSIONS = ['.create','.show', '.edit', '.all', '.destroy', '.logs'];

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
 * Default function to get all icons
 */
function getAllIcons(): Collection
{
    return Icon::all();
}

/**
 * @param $request
 * @return stdClass
 *
 * This function is used for format the parameters for lists
 */
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
 * @param string $search
 * @param BaseRepository $repository
 * @param string $subject
 * @return mixed
 *
 * This method allow search all logs by params on search
 * The results include "causer"
 * $subject is the name of the class
 */
function masterLogQuery(string $search, BaseRepository $repository, string $subject): mixed {
    $response = $repository->search($search)->with('causer');
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
 * @param array $includes
 * @param Builder $query
 * @param bool $rel_includes
 * @param array $excluded
 * @return mixed
 *
 * Add the relation details to the current query
 */
function createSearchQuery(array $includes, Builder $query, bool $rel_includes = true, array $excluded = []): Builder {
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

function removeNull($data, $eraseNull){
    if(!$eraseNull || $data == null) return $data;
    return array_filter($data, fn ($value) => $value !== null);
}

function formatRelation($relation, $element, $object, $showData = true, $eraseNull = false){
    $relation['status'] = 'relation';
        /* $relation is of type type_relationship=object */
        if ($relation['type_relationship'] === 'object') {

            $obj = new TheObject('?object_type=' . $relation['relation']['id']);
            $relation['data'] = [
                'type' => 'object',
                'name' => $relation['name'],
                'required' => $relation['required'] ? true : false,
                'multiple' => $relation['type'] == 'multiple'
            ];
            if($showData){
                $relation['data']['data'] = $obj->publicAttributes();
            }


            $rl = DB::table('object_relations')->where(['relation_object'=> $relation['id'], 'object' => $object]);
            if($relation['type'] == 'unique'){
                $rl = $rl->first();
                $toGetWith = $element->objectRepository->includes;
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
            //getCustomFieldsRelations("object_type=" . $objectType->id, $this,  0, false, true)
            if( in_array($relation['filling_method'], ['all', 'creation'])){
                $relation['structure'] = getCustomFieldsRelations("object_type=" . $relation['relation']['id'], $element, $object, $showData, $eraseNull);
            }
            unset ($relation['values']);
            //unset ($relation['object_type']);
            return $relation;
        }
        /* $relation is of type type_relationship=user */
        else if($relation['type_relationship'] === 'user'){

            if($relation['type'] == 'unique'){
                $rl = DB::table('object_relations')->where(['relation_object'=> $relation['id'], 'object' => $object]);

                $rl = $rl->first();
                $relation['values'] = empty($rl) ?  null : User::find($rl->relation)->toArray();
            }else{
                $rl = DB::table('object_relations')->where(['relation_object'=> $relation['id'], 'object' => $object]);

                $rl = $rl->pluck('object_relations.relation')->toArray();
                $relation['values'] = empty($rl) ? [] : User::whereIn('id', $rl)->get()->toArray();
            }
            $relation['entity'] = [$relation['slug'] => $relation['values']];
            unset ($relation['values']);

            $relation['data'] = [
                'type' => 'user',
                'name' => $relation['name'],
                'required' => $relation['required'] ? true : false,
                'multiple' => $relation['type'] == 'multiple'
            ];
            if($showData){
                $rolesForObectTypeRelation = ObjectTypeRelation::find($relation['id']);
                $rolesForObectTypeRelation = $rolesForObectTypeRelation->roles()->pluck('id')->toArray();

                $obj = new User('?roles='.implode(',',$rolesForObectTypeRelation));
                $relation['data']['data'] = $obj->publicAttributes();
            }
            return $relation;
        }
        return $relation;
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
            $item['fields'] = array_map(function($field) use ($object, $showData, $eraseNull) {
                $f = formatField($field, $object, $showData);
                return removeNull($f, $eraseNull);
            }, $item['fields']);
            $item['fields'] = array_merge($item['fields'], array_map(function($relation) use ($object, $element, $showData, $eraseNull) {
                return formatRelation($relation, $element, $object, $showData, $eraseNull);
            }, $item['relations']));
            unset($item['relations']);
            usort($item['fields'],fn($first,$second) => $first['order'] > $second['order']);
            return removeNull($item, $eraseNull);
        }, $object_type['fields']);

        $response = array_merge($response, array_map(function ($field) use ($object, $showData){
            return formatField($field, $object, $showData);
        }, $object_type['fields']));

        $response = array_merge($response, array_map(function ($relation) use ($object, $element, $showData, $eraseNull) {
            return formatRelation($relation, $element,$object, $showData, $eraseNull);
        }, $object_type['relations_with']));

        usort($response,fn($first,$second) => $first['order'] > $second['order']);
    }
    return $response;
}


function simplifyData($data)
{
    $currentData = $data;
    $data = array_map (function($object) {

        $fieldsDelete = [
            'object_type',
            'created_at',
            'images',
            'internal_id',
            'width',
            'format',
            'order',
            'show_tab_name',
            'type',
            'relation',
            'description',
            'editable',
            'type_relationship',
            'data',
            'filling_method',
            'options',
            'accept',
            'default',
            'structure'
        ];

        foreach ($fieldsDelete as $key => $field) {
            if(isset($object[$field])){
                unset($object[$field]);
            }
        }

        if(isset($object['has_custom_fields']) && !$object['has_custom_fields']){
            unset($object['custom_fields'] );
            unset($object['has_custom_fields'] );
        }

        if(isset($object['custom_fields'])){
            $object['custom_fields'] = simplifyData($object['custom_fields']);
        }

        if(isset($object['fields'])){
            $object['fields'] = simplifyData($object['fields']);
        }

        if(isset($object['entity'])){
            $object['entity'] = simplifyData($object['entity']);
        }



        return removeNull($object, true);
    }, $data);

    return $data;
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
 * @param String $name
 * @return array
 */
function getConfiguration(String $name): array {

    if(Auth::check()){
        $configuration = SystemConfiguration::query()
            ->whereHas('configuration', function ($q) use ($name){
                $q->where(['name'=> $name, 'custom_by_user' => true ]);
            })->where(['user' => auth()->user()->getAuthIdentifier()])->first();
    }


    if(empty($configuration)) {
        $configuration = SystemConfiguration::query()
            ->whereHas('configuration', function ($q) use ($name){
                $q->where('name', $name);
            })->first();
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

/**
 * @param bool $showMenu
 * @return array|stdClass
 */
function getPermissionTree(bool $showMenu = false): array|stdClass {

    $roles = auth()->user()->getRoleNames()->toArray();

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
    $response->users = User::all()->pluck('id')->toArray();
    $response->objectTypes = $object_types;
    return $response;
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
        'markdown' => [
            'theme' => 'default',
            'paths' => [
                resource_path('views/vendor/mail'),
            ],
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


/**
 * @return Collection|array
 */
function getGroupsForMenu(): Collection|array{
    $roles_groups = getPermissionTree(true);
    $user = auth()->user();


    $groups = Group::query()->whereIn('id', array_unique($roles_groups->groups))
        ->with('permissions', function($q) use ($roles_groups){
            $q->where('show_in_menu', true);
            $q->whereIn('permissions.id', $roles_groups->permissions);
        })->with('links', function($q) {
            $q->orderBy('name', 'ASC');
        })->with('icon')->get();

        foreach ($groups as $group) {
            $group->links = $group->links->filter(function ($link) use ($user) {
                return $user->can($link->slug);
            });
        }

    return $groups;
}


/**
 * @return Collection|array
 */
function getGroupsOfConfiguration(){
    $configuration_groups = getPermissionTree(true);

    $group = Group::query()->where('id', 6)
        ->with('permissions', function($q) use ($configuration_groups){
            $q->where('show_in_menu', true);
            $q->whereIn('permissions.id', $configuration_groups->permissions);
        })->with('links', function($q) {
            $q->orderBy('name', 'ASC');
        })->with('icon')->first();


    // Iterar sobre los permisos del grupo
    $group->permissions->each(function($permission) {
        // Agregar la ruta al permiso
        $permission->route = route($permission->name, app()->getLocale());
    });


    return $group;
}
