<?php

namespace App\Http\Controllers;

use App\Events\NewChannelEvent;
use App\Interfaces\MainControllerInterface;
use App\Models\Business;
use App\Models\Channel;
use App\Models\FCMToken;
use App\Models\Notification;
use App\Repositories\ChannelRepository;
use App\Repositories\LogRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Kutia\Larafirebase\Facades\Larafirebase;

class ChannelController extends BaseController implements MainControllerInterface {

    /**
     * @var ChannelRepository
     */
    private ChannelRepository $channelRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Channel::class;

    /**
     * @param ChannelRepository $channelRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ChannelRepository $channelRepo, LogRepository $logRepo)
    {
        $this->channelRepository = $channelRepo;
        $this->logRepository = $logRepo;
    }


    public function all(Request $request): Response|JsonResponse
    {
        $rq = getRequestParams($request);
        $links = $this->channelRepository->search($rq->search)->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return  $this->channelRepository->getResponse($links, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {
            DB::beginTransaction();
            $channel = $this->channelRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $channel->business()->syncWithPivotValues($bs, ['model_type' => Channel::class]);
            }else{
                $bs = $this->channelRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $channel->business()->syncWithPivotValues($bs->id, ['model_type' => Channel::class]);
            }

            //$this->sendNotification($channel, $bs);

            $this->saveManipulation($channel);
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    function sendNotification($channel, $bs){
        $devices = FCMToken::query()->whereHas('user', function ($q) use ($channel){
            $q->where('id', $channel->user1)->orWhere('id', $channel->user2);
        })->pluck('token')->toArray();

        if(count($devices) > 0){
            Config::set('larafirebase.authentication_key', getConfigValue('GOOGLE_FIREBASE_PUBLIC'));

            $notification = Notification::query()->create([
                'name' => __('New channel'),
                'content' => __("New conversation"),
                'type' => getConfigValue('NEW_CHANNEL_TYPE'),
                'link' => '',
            ]);

            $notification->users()->sync([$channel->user1, $channel->user2]);
            $notification->business()->syncWithPivotValues($bs, ['model_type' => Notification::class]);

            $nt = Notification::query()->where('id', $notification->id)->with(['images', 'type'])->first();

            Larafirebase::withTitle($notification->name)
                ->withBody($notification->content)
                ->withPriority('high')->withAdditionalData([
                    'notification' => $nt->toArray(),
                    'channel' => $channel->toArray(),
                    'state' => 'channel',
                ])->sendNotification($devices);
        }
    }

    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->channelRepository->makeModel()->getFields();
        $channel = $this->channelRepository->find($id);
        if (empty($channel)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->channelRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->channelRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Show the Channel'),
            'url' => '#'
        ]);
    }

    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->channelRepository->makeModel()->getFields();
        $channel = $this->channelRepository->find($id);
        if (empty($channel)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->channelRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->channelRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> __('Update the channel'),
            'url' => route('channel.update', ['locale' => $lang, 'channel' => $id])
        ]);
    }

    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        unset($request['business']);


        $input = $request->all();
        $channel = $this->channelRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($channel == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $channel->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->channelRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $channel->business()->syncWithPivotValues($bs, ['model_type' => Channel::class]);
            //$this->sendNotification($channel, $bs);
            $this->saveManipulation($channel, 'updated');

            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $channel = $this->channelRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($channel == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($channel, 'deleted');
            $channel->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function logs(Request $request, string $lang): Response|JsonResponse
    {
        return getAllModelLogs($request,Channel::class, $this->logRepository);
    }
}
