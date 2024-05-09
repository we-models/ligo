<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Field;
use App\Models\ObjectType;
use App\Repositories\BusinessRepository;
use App\Repositories\FieldRepository;
use App\Repositories\LogRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use \Illuminate\Contracts\Foundation;
use stdClass;

class FieldController extends BaseController implements MainControllerInterface
{
    /**
     * @var FieldRepository
     */
    private FieldRepository $fieldRepository;

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
    public string $object = Field::class;

    /**
     * @param FieldRepository $fieldRepo
     * @param BusinessRepository $businessRepo
     * @param LogRepository $logRepo
     */
    public function __construct(FieldRepository $fieldRepo, BusinessRepository $businessRepo, LogRepository $logRepo)
    {
        $this->fieldRepository = $fieldRepo;
        $this->businessRepository = $businessRepo;
        $this->logRepository = $logRepo;
    }

    public function all(Request $request): Response|JsonResponse
    {
        $rq = getRequestParams($request);
        $fields = $this->fieldRepository->search($rq->search);

        if(isset($request['object_type'])){
            $fields = $fields->where('object_type', $request['object_type']);
        }
        if(isset($request['tab'])){
            $fields = $fields->where('layout', 'tab');
        }

        $fields = $fields->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->with('tab')->sortable();
        return  $this->fieldRepository->getResponse($fields, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $request['enable'] = $request['enable'] == 'on';
        $request['visible_in_app'] = $request['visible_in_app'] == 'on';
        $request['editable'] = $request['editable'] == 'on';
        $request['required'] = $request['required'] == 'on';

        if($request['type'] == 13 ){
            $request['default'] = $request['default'] == 'on';
        }


        $input = $request->all();
        try {
            DB::beginTransaction();
            $field = $this->fieldRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $field->business()->syncWithPivotValues($bs, ['model_type' => Field::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $field->business()->syncWithPivotValues($bs->id, ['model_type' => Field::class]);
            }
            $this->saveManipulation($field);
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
        $fields = $this->fieldRepository->makeModel()->getFields(true);
        $field = $this->fieldRepository->find($id);
        if (empty($field)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->fieldRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->fieldRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the field'),
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
        $fields = $this->fieldRepository->makeModel()->getFields(true);
        $field = $this->fieldRepository->find($id);
        if (empty($field)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->fieldRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->fieldRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the field'),
            'url' => route('field.update', ['locale' => $lang, 'field' => $id])
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

        $request['enable'] = $request['enable'] == 'on';
        $request['visible_in_app'] = $request['visible_in_app'] == 'on';
        $request['editable'] = $request['editable'] == 'on';
        $request['required'] = $request['required'] == 'on';

        if($request['type'] == 13 ){
            $request['default'] = $request['default'] == 'on';
        }

        $input = $request->all();

        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $field = $this->fieldRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($field == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $field->update($input);
            $field->enable = $request['enable'];
            $field->save();
            $this->saveManipulation($field, 'updated');
            if(userCanViewBusiness($bs) && isset($bs)){
                $field->business()->syncWithPivotValues($bs, ['model_type' => Field::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $field->business()->syncWithPivotValues($bs->id, ['model_type' => Field::class]);
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
    public function destroy(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $field = $this->fieldRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($field == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($field, 'deleted');
            $field->delete();
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
        return getAllModelLogs($request,Field::class, $this->logRepository);
    }
}
