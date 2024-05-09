<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\MainControllerInterface;
use App\Models\NotificationType;
use App\Repositories\LogRepository;
use App\Repositories\NotificationTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use \Illuminate\Contracts\Foundation;
use Exception;


class NotificationTypeController extends BaseController implements MainControllerInterface {

    /**
     * @var NotificationTypeRepository
     */
    private NotificationTypeRepository $notificationTypeRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = NotificationType::class;

    /**
     * @param NotificationTypeRepository $notificationTypeRepo
     * @param LogRepository $logRepo
     */
    public function __construct(NotificationTypeRepository $notificationTypeRepo, LogRepository $logRepo)
    {
        $this->notificationTypeRepository = $notificationTypeRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $notificationTypes = $this->notificationTypeRepository->search($rq->search);

        $notificationTypes = $notificationTypes
            ->whereHas(BUSINESS_IDENTIFY)
            ->sortable();
        return  $this->notificationTypeRepository->getResponse($notificationTypes, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {
            DB::beginTransaction();
            $notificationType = $this->notificationTypeRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $notificationType->business()->syncWithPivotValues($bs, ['model_type' => NotificationType::class]);
            }else{
                $notificationType = $this->notificationTypeRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $notificationType->business()->syncWithPivotValues($bs->id, ['model_type' => NotificationType::class]);
            }
            $this->saveManipulation($notificationType);
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
        $fields = $this->notificationTypeRepository->makeModel()->getFields();
        $notificationType = $this->notificationTypeRepository->find($id);
        if (empty($notificationType)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->notificationTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->notificationTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the Notification type'),
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
        $fields = $this->notificationTypeRepository->makeModel()->getFields();
        $notificationType = $this->notificationTypeRepository->find($id);
        if (empty($notificationType)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->notificationTypeRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->notificationTypeRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Update the notification type'),
            'url' => route('notification_type.update', ['locale' => $lang, 'notification_type' => $id])
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
        $notificationType = $this->notificationTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($notificationType == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $notificationType->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->notificationTypeRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $notificationType->business()->syncWithPivotValues($bs, ['model_type' => NotificationType::class]);
            $this->saveManipulation($notificationType, 'updated');

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
        $notificationType = $this->notificationTypeRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($notificationType == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($notificationType, 'deleted');
            $notificationType->delete();
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
        return getAllModelLogs($request,NotificationType::class, $this->logRepository);
    }

}
