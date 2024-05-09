<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\FCMToken;
use App\Models\ImageFile;
use App\Models\NewRole;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Models\NotificationRead;
use App\Models\NotificationType;
use App\Models\User;
use App\Repositories\LogRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;
use \Illuminate\Support\Facades\Config;
use Kutia\Larafirebase\Facades\Larafirebase;
use function PHPUnit\TestFixture\func;

class NotificationController  extends BaseController implements MainControllerInterface
{

    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Notification::class;


    /**
     * @param NotificationRepository $notificationRepo
     * @param LogRepository $logRepo
     */
    public function __construct(NotificationRepository $notificationRepo, LogRepository $logRepo)
    {
        $this->notificationRepository = $notificationRepo;
        $this->logRepository = $logRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $notifications = $this->notificationRepository->search($rq->search);

        $notifications = $notifications
            ->whereHas(BUSINESS_IDENTIFY)
            ->sortable();
        return  $this->notificationRepository->getResponse($notifications, $rq);
    }

    public function getNotificationBody($obj, $roles, $input){
        return Notification::query()->whereHas('receivers', function($receiver) use ($obj, $roles, $input){

            $receiver->where(function($rcv) use ($obj, $roles, $input){
                $rcv->whereIn('role', $roles)->where(function ($q) use ($obj, $input){
                    return $this->read_condition($q, $obj, $input);
                });
            });

            $receiver->orWhere(function ($rcv) use ($obj, $input) {
                $rcv->where('user', auth()->user()->getAuthIdentifier())->where(function ($q) use ($obj, $input){
                    return $this->read_condition($q, $obj, $input);
                });
            });
        })->whereHas(BUSINESS_IDENTIFY)->with(['type', 'images'])->orderBy('created_at','DESC');
    }

    public function by_user(Request $request){
        $input = $request->all();
        $obj = $input['object']??null;

        $roles = NewRole::query()->whereIn('name', auth()->user()->getRoleNames())->pluck('id');
        $notifications = $this->getNotificationBody($obj, $roles, $input);

        return $notifications->paginate(10);
    }

    public function read_condition($q, $obj, $input){
        if(isset($input['read']) && $input['read'] == 1){
            /// GET all read notifications
            $q->WhereHas('reads', function($read) use ($obj){
                $read->where('user', auth()->user()->getAuthIdentifier());
                if(!empty($obj)) $read->where('object', $obj);
            });
        }else{
            /// GET all not read notifications
            $q->whereDoesntHave('reads', function($read) use ($obj){
                $read->where('user', auth()->user()->getAuthIdentifier());
                if(!empty($obj)) $read->where('object', $obj);
            });
        }
        return $q;
    }

    public function mark_as_read(Request $request){
        $input = $request->all();

        try {
            DB::beginTransaction();
            $roles = NewRole::query()->whereIn('name', auth()->user()->getRoleNames())->pluck('id');
            $notification = Notification::query()
                ->where('id',$input['notification'])
                ->whereHas('receivers', function($receiver) use ($roles){
                    $receiver->whereIn('role', $roles)
                        ->orWhere('user', auth()->user()->getAuthIdentifier())
                        ->with('reads');
                })->whereHas(BUSINESS_IDENTIFY)->with(['type', 'images', 'receivers'])->first();

            if(!empty($notification)){
                foreach($notification['receivers'] as $receiver){
                    NotificationRead::query()->create([
                        'receiver' => $receiver['id'],
                        'user' => auth()->user()->getAuthIdentifier(),
                        'object' => null,
                        'read'=>true
                    ]);
                }
            }
            DB::commit();
            return response()->json(['status' => 'removed']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response()->json(['status' => 'failed']);
        }
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {

            if(!isset($input['type'])){
                throw new Exception(__('Add type for notification'));
            }

            DB::beginTransaction();
            $notification = $this->notificationRepository->create($input);

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }
            $notification->images()->syncWithPivotValues($image, ['model_type' => Notification::class]);

            if(userCanViewBusiness($bs) && isset($bs)){
                $notification->business()->syncWithPivotValues($bs, ['model_type' => Notification::class]);
            }else{
                $notification = $this->notificationRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $notification->business()->syncWithPivotValues($bs->id, ['model_type' => Notification::class]);
            }

            $receivers = [];

            if(isset($request['roles'])){
                $notification->roles()->sync($request['roles']);

                $roles = NewRole::query()->whereIn('id', $request['roles'])->pluck('name');

                $receivers = array_merge($receivers, User::role($roles)->whereHas(BUSINESS_IDENTIFY)->pluck('id')->toArray());
                //->whereNot('id', auth()->user()->getAuthIdentifier())
            }

            if(isset($request['users'])){
                $users  = User::query()->whereIn('id', $request['users'])->whereHas(BUSINESS_IDENTIFY)->pluck('id')->toArray();
                $notification->users()->sync($users);
                $receivers = array_merge($receivers, $users);
            }

            $receivers = array_unique($receivers);


            $devices = FCMToken::query()->whereHas('user', function ($q) use ($receivers){
                $q->whereIn('id', $receivers);
            })->pluck('token')->toArray();

            Config::set('larafirebase.authentication_key', getConfigValue('GOOGLE_FIREBASE_PUBLIC'));

            $notify = Larafirebase::withTitle($notification->name)
                ->withBody($notification->content)->withPriority('high');

            if(!empty($image)){
                $img = $notification->images()->first();
                $notify= $notify->withImage($img->thumbnail);
            }
            if(!empty($notification->link)){
                $notify= $notify->withClickAction($notification->link);
            }

            $nt = Notification::query()->where('id', $notification->id)->with(['images', 'type'])->first()->toArray();
            if(count($nt['images']) > 0){
                $nt['images'] = [['thumbnail' =>  $nt['images'][0]['thumbnail'] ]];
            }

            $notify->withPriority('high')->withAdditionalData([
                'notification' => $nt,
                'state' => 'notification',
            ])->sendNotification($devices);

            $this->saveManipulation($notification);
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
        $fields = $this->notificationRepository->makeModel()->getFields();
        $notificationType = $this->notificationRepository->find($id);
        if (empty($notificationType)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->notificationRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->notificationRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the Notification'),
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
        return response(__('Can not edit'), 403);
    }

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     *
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {
        return response(__('Can not update'), 403);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     * @throws Exception
     */
    public function destroy(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        return response(__("Can not delete"), 403);
    }

    /**
     * @param Request $request
     * @param string $lang
     * @return Response|JsonResponse
     */
    public function logs(Request $request, string $lang): Response|JsonResponse {
        return getAllModelLogs($request,Notification::class, $this->logRepository);
    }
}
