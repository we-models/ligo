<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\ObjectTypeRelation;
use App\Models\ObjectType;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\ObjectTypeRelationRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\ObjectTypeRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;

class ObjectTypeRelationController extends BaseController implements MainControllerInterface
{


    /**
     * @var ObjectTypeRelationRepository
     */
    private ObjectTypeRelationRepository $objectTypeRelationRepository;

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
    public string $object = ObjectTypeRelation::class;

    /**
     * @param ObjectTypeRelationRepository $objectTypeRelationRepo
     * @param BusinessRepository $businessRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ObjectTypeRelationRepository $objectTypeRelationRepo, BusinessRepository $businessRepo, LogRepository $logRepo)
    {
        $this->objectTypeRelationRepository = $objectTypeRelationRepo;
        $this->businessRepository = $businessRepo;
        $this->logRepository = $logRepo;
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $object_type_relations = $this->objectTypeRelationRepository->search($rq->search)->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->objectTypeRelationRepository->getResponse($object_type_relations, $rq);
    }

    public function store(Request $request): Response|Foundation\Application|ResponseFactory {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $request['enable'] = $request['enable'] == 'on';
        $request['visible_in_app'] = $request['visible_in_app'] == 'on';
        $request['editable'] = $request['editable'] == 'on';
        $request['required'] = $request['required'] == 'on';

        $input = $request->all();
        try {
            DB::beginTransaction();
            $object_type_relation = $this->objectTypeRelationRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $object_type_relation->business()->syncWithPivotValues($bs, ['model_type' => ObjectTypeRelation::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $object_type_relation->business()->syncWithPivotValues($bs->id, ['model_type' => ObjectTypeRelation::class]);
            }


            if(isset($request['video'])){
                $object_type_relation->video()->syncWithPivotValues($request['video'], ['model_type' => ObjectTypeRelation::class,  'field' => 'video']);
            }

            $this->saveManipulation($object_type_relation);
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
        $fields = $this->objectTypeRelationRepository->makeModel()->getFields();
        $object_type_relation = $this->objectTypeRelationRepository->find($id);
        if (empty($object_type_relation)) return response(__('Not found'), 404);
        return response([
            'object' => $this->objectTypeRelationRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->objectTypeRelationRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the Object type relation'),
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
        $fields = $this->objectTypeRelationRepository->makeModel()->getFields();
        $object_type_relation = $this->objectTypeRelationRepository->find($id);
        if (empty($object_type_relation)) return response(__('Not found'), 404);
        return response([
            'object' => $this->objectTypeRelationRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->objectTypeRelationRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the object type relation'),
            'url' => route('object_type_relation.update', ['locale' => $lang, 'object_type_relation' => $id])
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
        $request['visible_in_app'] = $request['visible_in_app'] == 'on';
        $request['editable'] = $request['editable'] == 'on';
        $request['required'] = $request['required'] == 'on';

        $input = $request->all();
        $object_type_relation = $this->objectTypeRelationRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($object_type_relation == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $object_type_relation->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }

            if(isset($request['video'])){
                $object_type_relation->video()->syncWithPivotValues($request['video'], ['model_type' => ObjectTypeRelation::class,  'field' => 'video']);
            }

            $object_type_relation->business()->syncWithPivotValues($bs, ['model_type' => ObjectTypeRelation::class]);
            $this->saveManipulation($object_type_relation, 'updated');
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
        $object_type_relation = $this->objectTypeRelationRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($object_type_relation == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($object_type_relation, 'deleted');
            $object_type_relation->delete();
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
        return getAllModelLogs($request,ObjectTypeRelation::class, $this->logRepository);
    }
}
