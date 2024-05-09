<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Business;
use App\Models\Group;
use App\Models\NewPermission;
use App\Models\NewRole;
use App\Models\User;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\PermissionRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class PermissionController extends BaseController implements MainControllerInterface {

    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = NewPermission::class;

    /**
     * @param PermissionRepository $permissionRepo
     * @param LogRepository $logRepo
     */
    public function __construct(PermissionRepository $permissionRepo, LogRepository $logRepo, BusinessRepository $businessRepo) {
        $this->permissionRepository = $permissionRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $permissions = $this->permissionRepository->search($rq->search);
        if(!auth()->user()->hasAnyRole(ALL_ACCESS)){
            $permissions = $permissions->whereIn('id', auth()->user()->getAllPermissions()->pluck('id')->toArray());
        }
        $permissions  = $permissions->sortable();
        return  $this->permissionRepository->getResponse($permissions, $rq);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     * @throws Exception
     *
     * If the user can not set a business the system will use the current business on session
     */
    public function store(Request $request): Response|Application|ResponseFactory {
        $request['show_in_menu'] = $request['show_in_menu'] == 'on';
        $input = $request->all();
        try {
            DB::beginTransaction();
            $permission = $this->permissionRepository->create($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     * @throws Exception
     *
     * The permission should be assigned to current business for SHOW
     */
    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $fields = $this->permissionRepository->makeModel()->getFields();
        $permission = $this->permissionRepository->find($id);
        if (empty($permission)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->permissionRepository->makeModel()->with($this->permissionRepository->includes)->where('id', $id)->first(),
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> __('Show the permission'),
            'url' => '#'
        ]);
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     * @throws Exception
     *
     * The permission should be assigned to current business for EDIT
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $fields = $this->permissionRepository->makeModel()->getFields();
        $permission = $this->permissionRepository->find($id);
        if (empty($permission)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->permissionRepository->makeModel()->with($this->permissionRepository->includes)->where('id', $id)->first(),
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> __('Update the permission'),
            'url' => route('permission.update', ['locale' => $lang, 'permission' => $id])
        ]);
    }

    /**
     * @param Request $request
     * @param string $lang
     * @param int $id
     * @return Response|Application|ResponseFactory
     * @throws Exception
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory {
        $request['show_in_menu'] = $request['show_in_menu'] == 'on';
        $input = $request->all();
        try {
            $permission = $this->permissionRepository->find($id);
            DB::beginTransaction();
            $permission->update($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $permission = $this->permissionRepository->find($id);
        if (empty($permission)) return response(__('Not found'), 404);
        try {
            DB::beginTransaction();
            $permission->business()->detach();
            $permission->roles()->detach();
            $permission->groups()->detach();
            $permission->delete();
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
        return getAllModelLogs($request,NewPermission::class, $this->logRepository);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function assignRoles(Request $request): Factory|View|Application {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => NewRole::class,
            'columns' => NewPermission::class,
            'key' => 'permission_has_role'
        ]);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function assignGroup(Request $request): Factory|View|Application {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => Group::class,
            'columns' => NewPermission::class,
            'key' => 'permission_has_group'
        ]);
    }
}
