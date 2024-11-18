<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Icon;
use App\Repositories\IconRepository;
use App\Repositories\LogRepository;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation;
use Throwable;

class IconController extends BaseController implements MainControllerInterface
{
    /**
     * @var IconRepository
     */
    private IconRepository $iconRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Icon::class;

    public function __construct(IconRepository $iconRepo, LogRepository $logRepo)
    {
        $this->iconRepository = $iconRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $groups = $this->iconRepository->search($rq->search);
        $groups = $groups->sortable();
        return  $this->iconRepository->getResponse($groups, $rq);
    }

    /**
     * @param Request $request
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     */
    public function store(Request $request): Response|Foundation\Application|ResponseFactory
    {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $icon = $this->iconRepository->create($input);
            $this->saveManipulation($icon);
            DB::commit();
            return response(__('Success'), 200);
        } catch (Throwable $e) {
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
        $fields = $this->iconRepository->makeModel()->getFields();
        $icon = $this->iconRepository->find($id);
        if (empty($icon)) return response(__('Not found'), 404);
        return response([
            'object' => $this->iconRepository->makeModel()->with($this->iconRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> 'icon',
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
        $fields = $this->iconRepository->makeModel()->getFields();
        $icon = $this->iconRepository->find($id);
        if (empty($icon)) return response(__('Not found'), 404);
        return response([
            'object' => $this->iconRepository->makeModel()->with($this->iconRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> 'icon',
            'url' => route('icon.update', ['locale' => $lang, 'icon' => $id])
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
        $input = $request->all();
        $icon = $this->iconRepository->makeModel()->where('id', $id)->first();
        try {
            if($icon == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $icon->update($input);
            $this->saveManipulation($icon, 'updated');
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
        $icon = $this->iconRepository->makeModel()->where('id', $id)->first();
        try {
            if($icon == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($icon, 'deleted');
            $icon->delete();
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
        return getAllModelLogs($request,Icon::class, $this->logRepository);
    }
}
