<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Link;
use App\Models\NewPermission;
use App\Models\NewRole;
use App\Models\ObjectType;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\ObjectTypeRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;

class ObjectTypeController extends BaseController implements MainControllerInterface
{

    /**
     * @var ObjectTypeRepository
     */
    private ObjectTypeRepository $objectTypeRepository;

    /**
     * @var BusinessRepository
     */
    private BusinessRepository $businessRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = ObjectType::class;

    /**
     * @param ObjectTypeRepository $objectTypeRepo
     * @param BusinessRepository $businessRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ObjectTypeRepository $objectTypeRepo, BusinessRepository $businessRepo, LogRepository $logRepo)
    {
        $this->objectTypeRepository = $objectTypeRepo;
        $this->businessRepository = $businessRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $object_types = $this->objectTypeRepository->search($rq->search)->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->objectTypeRepository->getResponse($object_types, $rq);
    }

    /**
     * @param Request $request
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     *
     * IF THE USER HAS NOT PERMISSIONS FOR THE SELECTED BUSINESS THE SYSTEM WILL APPLY THE CURRENT BUSINESS
     */
    public function store(Request $request): Response|Foundation\Application|ResponseFactory {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $request['enable'] = $request['enable'] == 'on';
        $request['public'] = $request['public'] == 'on';

        $input = $request->all();
        try {
            DB::beginTransaction();
            $object_type = $this->objectTypeRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $object_type->business()->syncWithPivotValues($bs, ['model_type' => ObjectType::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $object_type->business()->syncWithPivotValues($bs->id, ['model_type' => ObjectType::class]);
            }
            $this->saveManipulation($object_type);
            $link = Link::query()->create([
                'name' => $request['name'],
                'url' => route('object.index', app()->getLocale()) . "?object_type=" . $object_type->id
            ]);
            $link->business()->syncWithPivotValues($bs, ['model_type' => Link::class]);
            $link->group()->syncWithPivotValues(10, ['model_type' => Link::class]);

            $assign_link_role = auth()->user()->roles()->first()->pluck('id')->toArray();
            foreach ($assign_link_role as $role){
                DB::table('model_has_roles')->insert([
                    'role_id' => $role,
                    'model_type' => ObjectType::class,
                    'model_id' =>$object_type->id
                ]);
            }

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
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->objectTypeRepository->makeModel()->getFields();
        $object_type = $this->objectTypeRepository->find($id);
        if (empty($object_type)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->objectTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->objectTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the Object type'),
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
        $fields = $this->objectTypeRepository->makeModel()->getFields();
        $object_type = $this->objectTypeRepository->find($id);
        if (empty($object_type)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->objectTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->objectTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the object type'),
            'url' => route('object_type.update', ['locale' => $lang, 'object_type' => $id])
        ]);
    }

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     *
     * IF THE USER HAS NOT PERMISSIONS FOR THE SELECTED BUSINESS THE SYSTEM WILL APPLY THE CURRENT BUSINESS
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {
        $bs = $request['business'];
        unset($request['business']);

        $request['enable'] = $request['enable'] == 'on';
        $request['public'] = $request['public'] == 'on';

        $input = $request->all();
        $object_type = $this->objectTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($object_type == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            Link::query()->where('name', $object_type->name)->update(['name' => $input['name'] ]);
            $object_type->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $object_type->business()->syncWithPivotValues($bs, ['model_type' => ObjectType::class]);
            $this->saveManipulation($object_type, 'updated');

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
        $object_type = $this->objectTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($object_type == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            Link::query()->where('name', $object_type->name )->delete();
            $this->saveManipulation($object_type, 'deleted');
            $object_type->delete();
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
        return getAllModelLogs($request,ObjectType::class, $this->logRepository);
    }

    public function assignRoles(Request $request): Factory|View|Application {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => NewRole::class,
            'columns' => ObjectType::class,
            'key' => 'object_type_has_role'
        ]);
    }
}
