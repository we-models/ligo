<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Business;
use App\Models\NewRole;
use App\Models\User;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\RoleRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;


/**
 *
 */
class RoleController extends BaseController implements MainControllerInterface {
    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = NewRole::class;

    /**
     * RoleController constructor.
     * @param RoleRepository $roleRepo
     * @param LogRepository $logRepo
     */
    public function __construct(RoleRepository $roleRepo, LogRepository $logRepo) {
        $this->roleRepository = $roleRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $roles = $this->roleRepository->search($rq->search);
        if(!auth()->user()->hasAnyRole(ALL_ACCESS)){
            $roles = $roles->whereIn('name', auth()->user()->getRoleNames()->toArray())
                ->whereHas(BUSINESS_IDENTIFY);
        }
        $roles = $roles->sortable();
        return  $this->roleRepository->getResponse($roles, $rq);
    }

    /**
     * @param Request $request
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     */
    public function store(Request $request): Response|Foundation\Application|ResponseFactory {
        $request['public'] = $request['public'] == 'on';
        $request['is_admin'] = $request['is_admin'] == 'on';
        $input = $request->all();

        try {
            DB::beginTransaction();
            $this->roleRepository->create($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse {
        $this->setIcons();
        $obj    =   new $this->object($this->getParams($request, false));
        return response()->json([
            'rating_types_url' => route('comment.rating_types', app()->getLocale()),
            'comments' => route('comment.all', app()->getLocale()),
            'object' => $this->object,
            'title' => __(strtoupper($obj->singular)),
            'csrf' => csrf_token(),
            'fields' => $obj->getFields(true),
            'icons' => $this->icons,
            'values' => $obj->newObject($this->getParams($request, false)),
            'index' => route($obj->singular .  '.index',   app()->getLocale()),
            'all' => route($obj->singular .  '.all',     app()->getLocale()) . $this->getParams($request),
            'create' => route($obj->singular .  '.store',   app()->getLocale()),
            'languages' => config('app.available_locales'),
            'language' => app()->getLocale(),
            'permissions' => $obj->getPermissionsForModel(),
            'logs' => route($obj->singular .  '.logs',    app()->getLocale()) . $this->getParams($request),
            'custom_fields' => $this->getCustomFieldsRelations($this->getParams($request, false))
        ]);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     */
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->roleRepository->makeModel()->getFields();
        $role =  $this->roleRepository->find($id);
        if (empty($role)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->roleRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->roleRepository->includes)->where('id' ,$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the role'),
            'url' => '#'
        ]);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     */
    public function edit(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->roleRepository->makeModel()->getFields();
        $role =  $this->roleRepository->find($id);
        if (empty($role)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->roleRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->roleRepository->includes)->where('id', $id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the role'),
            'url' => route('role.update', ['locale' => $lang, 'role' => $id])
        ]);
    }

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {
        $request['public'] = $request['public'] == 'on';
        $request['is_admin'] = $request['is_admin'] == 'on';
        $input = $request->all();
        $role = $this->roleRepository->find($id);
        try {
            DB::beginTransaction();
            $role->update($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     * @throws Exception
     */
    public function destroy(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $role =  $this->roleRepository->find($id);
        if (empty($role)) return response(__('Not found'), 404);
        if($role->name == 'Developer') throw new Exception(__("Not all roles should be removed"));
        try {
            DB::beginTransaction();
            $users = User::all();
            foreach($users as $user){
                if( $user->hasRole($role->name))  $user->removeRole($role->name);
            }
            $role->permissions()->detach();
            $role->business()->detach();
            $role->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param Request $request
     * @param string $lang
     * @return Response|JsonResponse
     */
    public function logs(Request $request, string $lang): Response|JsonResponse {
        return getAllModelLogs($request,NewRole::class, $this->logRepository);
    }

    /**
     * @param Request $request
     * @return Factory|View|Foundation\Application
     */
    public function assignBusiness(Request $request): Factory|View|Foundation\Application {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => Business::class,
            'columns' => NewRole::class,
            'key' => 'role_for_business'
        ]);
    }
}
