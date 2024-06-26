<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Group;
use App\Models\NewRole;
use App\Repositories\GroupRepository;
use App\Repositories\LogRepository;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation;
use Illuminate\Validation\Rule;

use Throwable;

/**
 * THE GROUPS ARE THE ELEMENTS PRESENTS ON THE MENU
 */
class GroupController extends BaseController implements MainControllerInterface {

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Group::class;

    /**
     * @param GroupRepository $groupRepo
     * @param LogRepository $logRepo
     */
    public function __construct(GroupRepository $groupRepo, LogRepository $logRepo)
    {
        $this->groupRepository = $groupRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $groups = $this->groupRepository->search($rq->search);
        $groups = $groups->sortable();
        return  $this->groupRepository->getResponse($groups, $rq);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse {
        $this->setIcons();
        $obj    =   new $this->object($this->getParams($request, false));
        return response()->json([
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
     * @param Request $request
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     */
    public function store(Request $request): Response|Foundation\Application|ResponseFactory {
        try {
            $request->validate([
                'icon' => [
                    'required'
                ],
            ],[
                'icon.required' => __('The [icon] field is required'),
            ]);

            $input = $request->all();

            DB::beginTransaction();
            $group  = $this->groupRepository->create($input);
            $this->saveManipulation($group);
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
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
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->groupRepository->makeModel()->getFields();
        $group = $this->groupRepository->find($id);
        if (empty($group)) return response(__('Not found'), 404);
        return response([
            'object' => $this->groupRepository->makeModel()->with($this->groupRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=>  'group',
            'url' => '#'
        ]);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     * @throws Exception
     */
    public function edit(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->groupRepository->makeModel()->getFields();
        $group = $this->groupRepository->find($id);
        if (empty($group)) return response(__('Not found'), 404);
        return response([
            'object' => $this->groupRepository->makeModel()->with($this->groupRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> 'group',
            'url' => route('group.update', ['locale' => $lang, 'group' => $id])
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
        try {
            $request->validate([
                'icon' => [
                    'required'
                ],
            ],[
                'icon.required' => __('The [icon] field is required'),
            ]);

            $input = $request->all();
            $group = $this->groupRepository->makeModel()->where('id', $id)->first();


            if($group == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $group->update($input);
            $this->saveManipulation($group, 'updated');
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
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
        $group = $this->groupRepository->makeModel()->where('id', $id)->first();
        try {
            if($group == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($group, 'deleted');
            $group->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (Throwable $e){
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
        return getAllModelLogs($request,Group::class, $this->logRepository);
    }

    /**
     * @param Request $request
     * @return Factory|View|Foundation\Application
     */
    public function assignRoles(Request $request): Factory|View|Foundation\Application {
        return view('pages.general.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => NewRole::class,
            'columns' => Group::class,
            'key' => 'group_has_role'
        ]);
    }
}
