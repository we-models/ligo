<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;

use App\Models\Configuration;
use App\Repositories\ConfigurationRepository;
use App\Repositories\LogRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class ConfigurationController extends BaseController implements MainControllerInterface {

    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Configuration::class;

    /**
     * @param ConfigurationRepository $configurationRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ConfigurationRepository $configurationRepo, LogRepository $logRepo)
    {
        $this->configurationRepository = $configurationRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $configurations = $this->configurationRepository->search($rq->search)->sortable();
        return  $this->configurationRepository->getResponse($configurations, $rq);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory {
        $request['custom_by_user'] = $request['custom_by_user'] == 'on';
        $input = $request->all();
        try {
            DB::beginTransaction();
            if($input['type']  == 16 || $input['type'] == 17){
                $converted =  json_decode($input['default']);
                if( $input['type']  == 16 && !is_array($converted)) throw new \Exception(__('The value is not an array'), 403);
            }
            $this->configurationRepository->create($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $fields = (new Configuration)->getFields();
        $configuration  = $this->configurationRepository;
        $configuration = $configuration->formatQuery();
        $configuration = $configuration->find($id);
        if (empty($configuration)) return response(__('Not found'), 404);
        return response([
            'object' => $configuration,
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the Configuration'),
            'url' => '#'
        ]);
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $fields = (new Configuration)->getFields();
        $configuration  = $this->configurationRepository;
        $configuration = $configuration->formatQuery();
        $configuration = $configuration->find($id);
        if (empty($configuration)) return response(__('Not found'), 404);
        return response([
            'object' => $configuration,
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> __('Update the Configuration'),
            'url' => route('configuration.update', ['locale' => $lang, 'configuration' => $id])
        ]);
    }


    /**
     * @param Request $request
     * @param string $lang
     * @param int $id
     * @return Response|Application|ResponseFactory
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory {
        $configuration = Configuration::query()->find($id);
        try {
            DB::beginTransaction();
            $request['custom_by_user'] = $request['custom_by_user'] == 'on';
            $input = $request->all();
            if($input['type']  == 16 || $input['type'] == 17){
                $converted =  json_decode($input['default']);
                if($converted == null) throw new \Exception(__('Invalid JSON/Array'), 403);
                if( $input['type']  == 16 && !is_array($converted)) throw new \Exception(__('The value is not an array'), 403);
            }
            $configuration->update($input);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        $configuration = Configuration::query()->find($id);
        try {
            DB::beginTransaction();
            $configuration->delete();
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
        return getAllModelLogs($request,Configuration::class, $this->logRepository);
    }
}
