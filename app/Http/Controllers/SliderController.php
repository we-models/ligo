<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\ImageFile;
use App\Models\ObjectType;
use App\Models\Slider;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\SliderRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;

class SliderController extends BaseController implements MainControllerInterface
{
    private SliderRepository $sliderRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var BusinessRepository
     */
    private BusinessRepository $businessRepository;

    /**
     * @var string
     */
    public string $object = Slider::class;


    /**
     * @param SliderRepository $sliderRepo
     * @param BusinessRepository $businessRepository
     */
    public function __construct(SliderRepository $sliderRepo, BusinessRepository $businessRepo, LogRepository $logRepo)
    {
        $this->sliderRepository = $sliderRepo;
        $this->businessRepository = $businessRepo;
        $this->logRepository = $logRepo;
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $sliders = $this->sliderRepository->search($rq->search)->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->sliderRepository->getResponse($sliders, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {
            DB::beginTransaction();

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }

            $slider = $this->sliderRepository->create($input);
            $slider->images()->syncWithPivotValues($image, ['model_type' => Slider::class]);

            if(userCanViewBusiness($bs) && isset($bs)){
                $slider->business()->syncWithPivotValues($bs, ['model_type' => Slider::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $slider->business()->syncWithPivotValues($bs->id, ['model_type' => Slider::class]);
            }
            $this->saveManipulation($slider);
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
     * @throws Exception
     */
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->sliderRepository->makeModel()->getFields();
        $slider = $this->sliderRepository->find($id);
        if (empty($slider)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->sliderRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->sliderRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the Slider'),
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
        $fields = $this->sliderRepository->makeModel()->getFields();
        $slider = $this->sliderRepository->find($id);
        if (empty($slider)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->sliderRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->sliderRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Update the slider'),
            'url' => route('slider.update', ['locale' => $lang, 'slider' => $id])
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
        $slider = $this->sliderRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($slider == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }
            $slider->update($input);
            $slider->images()->syncWithPivotValues($image, ['model_type' => Slider::class]);

            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $slider->business()->syncWithPivotValues($bs, ['model_type' => Slider::class]);
            $this->saveManipulation($slider, 'updated');
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
        $slider = $this->sliderRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($slider == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $slider->business()->detach();
            $slider->images()->detach();
            $this->saveManipulation($slider, 'deleted');
            $slider->delete();
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
        return getAllModelLogs($request,Slider::class, $this->logRepository);
    }
}
