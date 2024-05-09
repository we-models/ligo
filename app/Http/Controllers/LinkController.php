<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Link;
use App\Repositories\LinkRepository;
use App\Repositories\LogRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class LinkController extends BaseController implements MainControllerInterface
{

    /**
     * @var LinkRepository
     */
    private LinkRepository $linkRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Link::class;

    /**
     * @param LinkRepository $linkRepo
     * @param LogRepository $logRepo
     */
    public function __construct(LinkRepository $linkRepo, LogRepository $logRepo)
    {
        $this->linkRepository = $linkRepo;
        $this->logRepository = $logRepo;
    }

    public function all(Request $request): Response|JsonResponse
    {
        $rq = getRequestParams($request);
        $links = $this->linkRepository->search($rq->search)->sortable();
        return  $this->linkRepository->getResponse($links, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {

        $input = $request->all();
        try {
            DB::beginTransaction();
            $link = $this->linkRepository->create($input);
            $link->group()->syncWithPivotValues($request['group'], ['model_type' => Link::class]);
            $this->saveManipulation($link);
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws Exception
     */
    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->linkRepository->makeModel()->getFields();
        $link = $this->linkRepository->find($id);
        if (empty($link)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->linkRepository->makeModel()->with($this->linkRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the Link'),
            'url' => '#'
        ]);
    }

    /**
     * @throws Exception
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->linkRepository->makeModel()->getFields();
        $link = $this->linkRepository->find($id);
        if (empty($link)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->linkRepository->makeModel()->with($this->linkRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the link'),
            'url' => route('link.update', ['locale' => $lang, 'link' => $id])
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory
    {


        $input = $request->all();
        $link = $this->linkRepository->makeModel()->where('id', $id)->first();
        try {
            if($link == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $link->update($input);
            $link->group()->syncWithPivotValues($request['group'], ['model_type' => Link::class]);
            $this->saveManipulation($link, 'updated');
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $link = $this->linkRepository->makeModel()->where('id', $id)->first();
        try {
            if($link == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($link, 'deleted');
            $link->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function logs(Request $request, string $lang): Response|JsonResponse
    {
        return getAllModelLogs($request,Link::class, $this->logRepository);
    }
}
