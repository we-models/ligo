<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Channel;
use App\Models\FCMToken;
use App\Models\Group;
use App\Models\NewRole;
use App\Models\NewPermission;
use App\Models\Notification;
use App\Models\ObjectType;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Kutia\Larafirebase\Facades\Larafirebase;
use Tests\Entities\PostType;

/**
 *  THE CONTROLLER IS USED TO SAVE RELATIONS MANY TO MANY BETWEEN 2 MODELS
 */
class AssignmentController extends Controller {
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * CREATE A GRID USING THE X AND Y COMPONENTS
     */
    public function assignments(Request $request): JsonResponse {
        // IS NECESSARY GET THE KEY, THE X=>HORIZONTAL, AND THE Y=>VERTICAL FOR ASSIGNMENTS
        if(empty($request['key']) || empty($request['x']) || empty($request['y']) ) abort(404);

        $key = $request['key'];

        $rq = getRequestParams($request);

        $row_object =  $request['x'];
        $column_object =  $request['y'];

        $userDataObject =  getPermissionTree(false);

        $noAdmin = !auth()->user()->hasAnyRole(ALL_ACCESS);

        // THE SEARCH AND PAGINATE QUERY IS ONLY FOR THE COLUMN
        $column = $column_object::query()->where('name', 'like', '%' . $rq->search . '%');
        if($column_object == NewRole::class && $noAdmin ){
            $column = $column->whereIn('id', $userDataObject->roles);
        }
        if($column_object == NewPermission::class && $noAdmin ){
            $column = $column->whereIn('id', $userDataObject->permissions);
        }
        if($column_object == Group::class && $noAdmin ){
            $column = $column->whereIn('id', $userDataObject->groups);
        }
        if($column_object == User::class && $noAdmin && !$request['general'] ){
            $column = $column->whereIn('id', $userDataObject->users);
        }

        if($column_object == User::class && $request['general'] ){
            $column = $column->role('Contact')->whereHas(BUSINESS_IDENTIFY);
        }

        if($column_object == Channel::class){
            $column = $column_object::query()->where('channels.name', 'like', '%' . $rq->search . '%');

            $column = $column->join('objects as obj1', 'obj1.id', '=', 'channels.profile_user1')
                ->join('objects as obj2', 'obj2.id', '=', 'channels.profile_user2')
                ->whereHas('business')
                ->select('channels.id', DB::raw("CONCAT(obj1.name, ' - ', obj2.name) AS name"));
        }

        if($column_object == Business::class && $noAdmin ){
            $column = $column->whereIn('id', $userDataObject->business);
        }
        if($column_object == ObjectType::class ){
            $column = $column->where('type' , 'post');
            if($noAdmin){
                $column = $column->whereIn('id', $userDataObject->objectTypes);
            }

        }
        $column = $column->paginate($rq->paginate)->toArray();


        $row = $row_object::query();
        if($row_object == NewRole::class && $noAdmin ){
            $row = $row->whereIn('id', $userDataObject->roles);
        }
        if($row_object == NewPermission::class && $noAdmin ){
            $row = $row->whereIn('id', $userDataObject->permissions);
        }
        if($row_object == Group::class && $noAdmin ){
            $row = $row->whereIn('id', $userDataObject->groups);
        }
        if($row_object == User::class && $noAdmin && !$request['general'] ){
            $row = $row->whereIn('id', $userDataObject->users);
        }

        if($row_object == User::class && $request['general'] ){
            $row = $row->role('Contact')->whereHas(BUSINESS_IDENTIFY);
        }
        if($row_object == Channel::class){
            $row = $row->join('objects as obj1', 'obj1.id', '=', 'channels.profile_user1')
                ->join('objects as obj2', 'obj2.id', '=', 'channels.profile_user2')
                ->whereHas('business')
                ->select('channels.id', DB::raw("CONCAT(obj1.name, ' - ', obj2.name) AS name"));
        }

        if($row_object == Business::class && $noAdmin ){
            $row = $row->whereIn('id', $userDataObject->business);
        }
        if($row_object == ObjectType::class){
            $row = $row->where('type' , 'post');
            if($noAdmin){
                $row = $row->whereIn('id', $userDataObject->objectTypes);
            }
        }

        $row = $row->select('id', 'name')->orderBy('id', 'asc');



        //FOR EACH VALUE ON VERTICAL A ROW IS ASSIGNED
        $column['data'] = array_map(function ($item) use ($row, $key){
            return $this->iteratorForColumn($item, $row, $key);
        }, $column['data']);
        // GET ALL COLUMN NAME FOR EACH ROW
        $column['headers'] = $row->pluck('name')->toArray();
        return response()->json($column);
    }

    /**
     * @param $item
     * @param $row
     * @param $key
     * @return mixed
     *
     * FOR EACH CASE SET THE STATUS OF THE RELATION
     */
    public function iteratorForColumn(&$item, $row, $key): mixed {
        $relation_name= 'relation';
        switch ($key){
            case 'user_has_role':
                /** $item is a user instance, row is a collection of roles */
                $the_user = User::find($item['id']);
                $item[$relation_name . 's'] = $row->get()->each(function ($role) use ($the_user, $relation_name){
                    $role->{$relation_name} =  $the_user->hasRole($role->name);
                });
                break;
            case 'role_for_business':
                $item[$relation_name . 's'  ] = $row->get()->each(function ($business) use ($item, $relation_name){
                    $business_exists = DB::table('model_has_business')->where([
                        'model_type' => NewRole::class,
                        'model_id' => $item['id'],
                        BUSINESS_IDENTIFY => $business->id
                    ])->exists();
                    $business->{$relation_name} = $business_exists;
                });
                break;
            case 'permission_has_group':
                $item[$relation_name . 's'  ] = $row->get()->each(function ($group) use ($item, $relation_name){
                    $group_exists = DB::table('model_has_group')->where([
                        'model_type' => NewPermission::class,
                        'model_id' => $item['id'],
                        'group' => $group->id
                    ])->exists();
                    $group->{$relation_name} = $group_exists;
                });
                break;
            case 'permission_has_role':
                /** $item is a permission instance, row is a collection of roles */
                $item[$relation_name . 's' ] = $row->get()->each(function ($role) use ($item, $relation_name){
                    $the_role = NewRole::find($role->id);
                    $role->{$relation_name} = $the_role->permissions->contains($item['id']);
                });
                break;
            case'group_has_business':
                $item[$relation_name . 's'  ] = $row->get()->each(function ($business) use ($item, $relation_name){
                    $group_exists = DB::table('model_has_business')->where([
                        'model_type' => Group::class,
                        'model_id' => $item['id'],
                        'business' => $business->id
                    ])->exists();
                    $business->{$relation_name} = $group_exists;
                });
                break;
            case 'object_type_has_role':
                $item[$relation_name . 's' ] = $row->get()->each(function ($role) use ($item, $relation_name){
                    //item is object type
                    $object_type = ObjectType::find($item['id']);
                    $role->{$relation_name} = $object_type->hasRole($role->name, '');
                });
                break;
            case 'channel_has_user':
                $item[$relation_name . 's' ] = $row->get()->each(function ($user) use ($item, $relation_name){
                    $channel = Channel::query()->where('id', $item['id'])->first();
                    $user->{$relation_name} = $channel->intermediary == $user['id'];
                });
                break;
            default :
                break;
        }

        return $item;
    }

    function sendNotification($channel, $bs, $title, $description, $action){
        $devices = FCMToken::query()->whereHas('user', function ($q) use ($channel){
            $q->where('id', $channel->intermediary);
        })->pluck('token')->toArray();

        if(count($devices) > 0){
            Config::set('larafirebase.authentication_key', getConfigValue('GOOGLE_FIREBASE_PUBLIC'));

            $notification = Notification::query()->create([
                'name' => $title,
                'content' => $description,
                'type' => getConfigValue('NEW_CHANNEL_TYPE'),
                'link' => route('chat.index', app()->getLocale()),
            ]);

            $notification->users()->sync([$channel->intermediary]);
            $notification->business()->syncWithPivotValues($bs, ['model_type' => Notification::class]);

            $nt = Notification::query()->where('id', $notification->id)->with(['images', 'type'])->first();
            $nt->images = [];

            Larafirebase::withTitle($notification->name)
                ->withBody($notification->content)
                ->withClickAction($notification->link)
                ->withPriority('high')->withAdditionalData([
                    'notification' => $nt->toArray(),
                    'channel' => $channel->toArray(),
                    'state' => 'channel',
                    'action' => $action
                ])->sendNotification($devices);
        }
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse|Application|ResponseFactory
     *
     * USING THE X AND Y COMPONENTS AND THE RELATION STATUS THE SYSTEM CAN ADD A RELATION MANY TO MANY FOR EACH CASE
     */
    public function saveAssignment(Request $request): Response|JsonResponse|Application|ResponseFactory {
        try {
            DB::beginTransaction();
            $row = $request['x'];
            $column = $request['y'];
            $key = $request['key'];
            $type = $request['type'] === 'true';
            switch ($key){
                case 'user_has_role':
                    if($column == auth()->user()->getAuthIdentifier())
                        throw new Exception(__('This user cannot change their own roles'));
                    $column = User::find($column);
                    $role = NewRole::find($row);
                    if($type){
                        $column->assignRole($role->name);
                    }else{
                        $column->removeRole($role->name);
                    }
                    break;
                case 'role_for_business':
                    $column = NewRole::find($column);
                    if($type ){
                        $column->business()->attach($row, ['model_type' => NewRole::class]);
                    }else{
                        $column->business()->detach($row, ['model_type' => NewRole::class]);
                    }
                    break;
                case 'permission_has_group':
                    $column = NewPermission::find($column);
                    if($type){
                        $column->groups()->attach($row,['model_type' => NewPermission::class] );
                    }else{
                        $column->groups()->detach($row,['model_type' => NewPermission::class] );
                    }
                    break;
                case 'permission_has_role':
                    $row = NewRole::find($row);
                    $column = NewPermission::find($column);
                    if($type ){
                        $row->givePermissionTo($column->name);
                    }else{
                        $row->revokePermissionTo($column->name);
                    }
                    break;
                case 'group_has_business':
                    $column = Group::find($column);
                    if($type){
                        $column->business()->attach($row, ['model_type' => Group::class]);
                    }else{
                        $column->business()->detach($row, ['model_type' => Group::class]);
                    }
                    break;
                case 'object_type_has_role':
                    if($type){
                        DB::table('model_has_roles')->insert([
                            'role_id' => $row,
                            'model_type' => ObjectType::class,
                            'model_id' =>$column
                        ]);
                    }else{
                        DB::table('model_has_roles')->where([
                            'role_id' => $row,
                            'model_type' => ObjectType::class,
                            'model_id' =>$column
                        ])->delete();
                    }
                    break;
                case 'channel_has_user':
                    //$channel = Channel::query()->where('id', $column)
                    //    ->with([BUSINESS_IDENTIFY, 'user1', 'user2', 'intermediary'])
                    //    ->with('profile_user1', function ($u){
                    //        $u->with('images');
                    //    })
                    //    ->with('profile_user2', function ($u){
                    //        $u->with('images');
                    //    })
                    //    ->with('messages', function($m){
                    //        $m->where('is_last', true)->first();
                    //    })->first();

                    $channel = Channel::query()->where('id', $column)->first();
                    $bs = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();

                    if($type){
                        $channel->intermediary = $row;
                        $description = __("New conversation in progress") . " ". $channel->name;
                        $this->sendNotification($channel, $bs->id, __('New channel assigned'), $description, 1);

                    }else{
                        $description = __("Intermediary was removed") . " ". $channel->name;
                        $this->sendNotification($channel, $bs->id, __('Unassigned channel'), $description, 0);
                        $channel->intermediary = null;
                    }
                    $channel->save();
                    break;
                default:
                    break;
            }
            DB::commit();
            return response()->json(['assignment' => 'success']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }
}
