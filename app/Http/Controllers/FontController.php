<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Font;
use App\Repositories\FontRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LogRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;

class FontController extends BaseController implements MainControllerInterface
{

    /**
     * @var FontRepository
     */
    private FontRepository $fontRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Font::class;

    /**
     * @param GroupRepository $groupRepo
     * @param LogRepository $logRepo
     */
    public function __construct(FontRepository $fontRepo, LogRepository $logRepo)
    {
        $this->fontRepository = $fontRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $object_types = $this->fontRepository->search($rq->search);
        if(isset($request['all'])){
            $rq->paginate = 1000;
            $object_types = $object_types->where('enable', true);
        }
        $object_types = $object_types->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->fontRepository->getResponse($object_types, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $request['enable'] = $request['enable'] == 'on';

        $input = $request->all();
        try {
            DB::beginTransaction();
            $font = $this->fontRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $font->business()->syncWithPivotValues($bs, ['model_type' => Font::class]);
            }else{
                $bs = $this->fontRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $font->business()->syncWithPivotValues($bs->id, ['model_type' => Font::class]);
            }
            $this->saveManipulation($font);
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
        $fields = $this->fontRepository->makeModel()->getFields();
        $font = $this->fontRepository->find($id);
        if (empty($font)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->fontRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->fontRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the font'),
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
        $fields = $this->fontRepository->makeModel()->getFields();
        $font = $this->fontRepository->find($id);
        if (empty($font)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->fontRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->fontRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the font'),
            'url' => route('font.update', ['locale' => $lang, 'font' => $id])
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

        $input = $request->all();
        $font = $this->fontRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($font == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $font->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->fontRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $font->business()->syncWithPivotValues($bs, ['model_type' => Font::class]);
            $this->saveManipulation($font, 'updated');

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
        $font = $this->fontRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($font == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($font, 'deleted');
            $font->delete();
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
        return getAllModelLogs($request,Font::class, $this->logRepository);
    }
}
