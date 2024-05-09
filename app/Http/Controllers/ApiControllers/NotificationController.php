<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\HomeController;
use \App\Http\Controllers\NotificationController as NotCtrl;
use App\Models\FCMToken;
use App\Models\NewRole;
use App\Repositories\LogRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class NotificationController
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
     * @param NotificationRepository $notificationRepo
     * @param LogRepository $logRepo
     */
    public function __construct(NotificationRepository $notificationRepo, LogRepository $logRepo)
    {
        $this->notificationRepository = $notificationRepo;
        $this->logRepository = $logRepo;
    }

    public function all(Request $request){
        session([BUSINESS_IDENTIFY =>  request()->header(BUSINESS_IDENTIFY)]);
        $ctrl = new NotCtrl($this->notificationRepository, $this->logRepository);
        return $ctrl->by_user($request);
    }

    public function read(Request $request){
        session([BUSINESS_IDENTIFY =>  request()->header(BUSINESS_IDENTIFY)]);
        $ctrl = new NotCtrl($this->notificationRepository, $this->logRepository);
        return $ctrl->mark_as_read($request);
    }

    public function saveFcm(Request $request){
        $input = $request->all();

        FCMToken::query()->where(['token' => $input['fcm_token'] ])->delete();

        $fcm = FCMToken::query()->where(['user' => auth()->user()->getAuthIdentifier(), 'device' => $input['device']])->first();
        if(!empty($fcm)){
            $fcm->token = $input['fcm_token'];
            $fcm->save();
        }else{
            FCMToken::query()->create([
                'user' => auth()->user()->getAuthIdentifier(),
                'device' => $input['device'],
                'token' => $input['fcm_token']
            ]);
        }

        return response()->json(['OK' => true]);
    }


    public function unReads(Request $request){
        session([BUSINESS_IDENTIFY =>  request()->header(BUSINESS_IDENTIFY)]);
        $ctrl = new NotCtrl($this->notificationRepository, $this->logRepository);

        $input = $request->all();
        $obj = $input['object']??null;

        $roles = NewRole::query()->whereIn('name', auth()->user()->getRoleNames())->pluck('id');
        $notifications = $ctrl->getNotificationBody($obj, $roles, $input)->count();
        return response()->json(['total' => $notifications]);
    }
}
