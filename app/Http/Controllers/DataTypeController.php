<?php

namespace App\Http\Controllers;

use App\Repositories\DataTypeRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Contracts\Foundation;

class DataTypeController extends Controller {

    /**
     * @var DataTypeRepository
     */
    private DataTypeRepository $dataTypeRepository;


    /**
     * @param DataTypeRepository $dataTypeRepo
     */
    public function __construct(DataTypeRepository $dataTypeRepo) {
        $this->dataTypeRepository = $dataTypeRepo;
    }


    public function index(Request $request): bool
    {
        return true;
    }

    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $dataTypes = $this->dataTypeRepository->search($rq->search)->whereNotNull('id')->sortable();
        return  $this->dataTypeRepository->getResponse($dataTypes, $rq);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     * @throws Exception
     */
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->dataTypeRepository->makeModel()->getFields();
        $field = $this->dataTypeRepository->find($id);
        if (empty($field)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->dataTypeRepository->makeModel()->with($this->dataTypeRepository->includes)->where('id',$id)->first(),
            'fields' => $fields,
            'icons' => "[]",
            'csrf' => csrf_token(),
            'title'=> 'Data type',
            'url' => '#'
        ]);
    }
}
