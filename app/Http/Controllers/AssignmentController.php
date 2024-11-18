<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\NewRole;
use App\Models\NewPermission;
use App\Models\ObjectType;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;

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

        $theClass = app()->make($column_object);

        $column = $column_object::query()->where(function($q) use($rq, $theClass){
            $values = $theClass->getFillable();
            foreach ($values as $value){
                $q->orWhere($value, 'like', '%' . $rq->search . '%');
            }
        });


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
            $column = $column->role('Contact');
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
            $row = $row->role('Contact');
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
        $column['search'] = $rq->search;
        /* Assign Icon default */

        $column['iconAssign'] = match ($column_object) {
            NewPermission::class => "fas fa-newspaper",
            NewRole::class => "fab fa-buromobelexperte",
            Group::class => "fas fa-object-group",
            User::class => "fa-solid fa-user",
            ObjectType::class => "fa-solid fa-diagram-successor",
            default => "fa-regular fa-circle",
        };
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
            case 'object_type_has_role':
                $item[$relation_name . 's' ] = $row->get()->each(function ($role) use ($item, $relation_name){
                    //item is object type
                    $object_type = ObjectType::find($item['id']);
                    $role->{$relation_name} = $object_type->hasRole($role->name, '');
                });
                break;
            default :
                break;
        }

        return $item;
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
            }
            DB::commit();
            return response()->json(['assignment' => 'success']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }
}
