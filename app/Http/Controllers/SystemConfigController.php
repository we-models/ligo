<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\SystemConfiguration;
use App\Repositories\ConfigurationRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 *
 */
class SystemConfigController extends BaseController {

    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $systemConfigRepository;

    public string $object = SystemConfiguration::class;

    /**
     * @param ConfigurationRepository $systemConfigRepo
     */
    public function __construct(ConfigurationRepository $systemConfigRepo) {
        $this->systemConfigRepository = $systemConfigRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application {
        return view('pages.general.config');
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $configurations =  $this->systemConfigRepository->search($rq->search)
            ->where('type' , $rq->type)
            ->sortable();
        return  $this->systemConfigRepository->getResponse($configurations, $rq);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|JsonResponse|Response
     */
    public function store(Request $request): \Illuminate\Foundation\Application|Response|JsonResponse|Application|ResponseFactory
    {

        $input = $request->all();
        $configuration = Configuration::find(intval($input['id']));

        try {
            DB::beginTransaction();
            if($input['configuration']['exists'] == 'true'){
                $config = SystemConfiguration::find($input['configuration']['id']);
                $config->value = $input['configuration']['value']??"";
            }else{
                $config = [
                    'configuration' => $configuration->id,
                    'value' => $input['configuration']['value']
                ];
                if($configuration->custom_by_user) $config['user'] = auth()->user()->getAuthIdentifier();

                $config = SystemConfiguration::create($config);
                $this->saveManipulation($config);
            }
            $config->save();
            DB::commit();
            return response()->json(['configuration' => $config->id]);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }
}
