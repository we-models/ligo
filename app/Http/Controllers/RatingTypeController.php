<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\RatingType;
use App\Http\Controllers\Controller;
use App\Repositories\LogRepository;
use App\Repositories\RatingTypeRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;

class RatingTypeController extends BaseController implements MainControllerInterface
{

    private RatingTypeRepository $ratingTypeRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = RatingType::class;

    /**
     * @param RatingTypeRepository $ratingTypeRepository
     * @param LogRepository $logRepository
     */
    public function __construct(RatingTypeRepository $ratingTypeRepo, LogRepository $logRepo)
    {
        $this->ratingTypeRepository = $ratingTypeRepo;
        $this->logRepository = $logRepo;
        $this->setIcons(false);
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $ratingTypes = $this->ratingTypeRepository->search($rq->search);
        $ratingTypes = $ratingTypes->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->ratingTypeRepository->getResponse($ratingTypes, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {
            DB::beginTransaction();
            $ratingType = $this->ratingTypeRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $ratingType->business()->syncWithPivotValues($bs, ['model_type' => RatingType::class]);
            }else{
                $bs = $this->ratingTypeRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $ratingType->business()->syncWithPivotValues($bs->id, ['model_type' => RatingType::class]);
            }
            $this->saveManipulation($ratingType);
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
        $fields = $this->ratingTypeRepository->makeModel()->getFields();
        $ratingType = $this->ratingTypeRepository->find($id);
        if (empty($ratingType)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->ratingTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->ratingTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the Rating type'),
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
        $fields = $this->ratingTypeRepository->makeModel()->getFields();
        $ratingType = $this->ratingTypeRepository->find($id);
        if (empty($ratingType)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->ratingTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->ratingTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Update the Rating type'),
            'url' => route('rating_type.update', ['locale' => $lang, 'rating_type' => $id])
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

        $input = $request->all();
        $ratingType = $this->ratingTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($ratingType == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $ratingType->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->ratingTypeRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $ratingType->business()->syncWithPivotValues($bs, ['model_type' => RatingType::class]);
            $this->saveManipulation($ratingType, 'updated');

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
        $ratingType = $this->ratingTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($ratingType == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($ratingType, 'deleted');
            $ratingType->delete();
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
        return getAllModelLogs($request,RatingType::class, $this->logRepository);
    }

}
