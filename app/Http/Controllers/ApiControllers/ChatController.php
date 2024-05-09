<?php

namespace App\Http\Controllers\ApiControllers;

use App\Events\NewChannelEvent;
use App\Events\NewMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Repositories\ChannelRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private ChannelRepository $channelRepository;
    private UserRepository $userRepository;

    /**
     * @param ChannelRepository $channelRepo
     */
    public function __construct(ChannelRepository $channelRepo, UserRepository $userRepo)
    {
        $this->channelRepository = $channelRepo;
        $this->userRepository = $userRepo;
    }

    public function getChatRooms(Request $request){
        $current_user =  auth()->user()->getAuthIdentifier();
        $channels = $this->channelRepository->makeModel()->query()
            ->where([
                'user1' => $current_user,
                'profile_user1' => $request['profile']
            ])->orWhere([
                'user2'=> $current_user,
                'profile_user2' => $request['profile']
            ])->with(['user1', 'user2', 'profile_user1', 'profile_user2'])->get();

        return response()->json($channels->toArray());
    }

    public function initChat(Request $request){
        $input = $request->all();
        try {
            DB::beginTransaction();

            session([BUSINESS_IDENTIFY =>   request()->header(BUSINESS_IDENTIFY) ]);
            $bs = request()->header(BUSINESS_IDENTIFY);
            $bs = Business::query()->where('code', $bs )->first();
            $bs = $bs->id;

            $other_user = $this->userRepository->makeModel()->query()->where('code', $input['other'])->first();
            $other_user = $other_user->getAuthIdentifier();

            $current_user =  auth()->user()->getAuthIdentifier();
            $channel = $this->channelRepository->makeModel()->query()
                ->where([
                    'user1' => $current_user,
                    'user2' => $other_user,
                    'profile_user1' => $input['own_profile'],
                    'profile_user2' => $input['other_profile']
                ] )->orWhere(function ($query) use ($current_user, $other_user, $input) {
                    $query->where('user1', $other_user)
                        ->where('user2', $current_user)
                        ->where('profile_user1', $input['other_profile'])
                        ->where('profile_user2', $input['own_profile']);
                })->whereHas(BUSINESS_IDENTIFY)->first();


            if(empty($channel) ){
                $channel = $this->channelRepository->create([
                    'user1' => $current_user,
                    'user2' => $other_user,
                    'profile_user1' => $input['own_profile'],
                    'profile_user2' => $input['other_profile'],
                    'name' => $current_user . '_'  . Str::uuid()
                ]);
                if(userCanViewBusiness($bs) && isset($bs)){
                    $channel->business()->syncWithPivotValues($bs, ['model_type' => Channel::class]);
                }
            }
            DB::commit();
            return response($channel);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }


    public function sendChat(Request $request){
        try{
            $message = [];
            $channel = Channel::query()->where('name', $request['channel'] )->first();
            $message['channel'] = $channel->id;
            $message['transmitter'] =  $request['transmitter']; //auth()->user()->getAuthIdentifier();
            $message['receiver'] = $channel->user1 === $message['transmitter'] ? $channel->user2 : $channel->user1;
            $message['is_from_intermediary'] = false;
            $message['is_last'] = true;
            $message['message'] = $request['message'];

            Message::query()->where('channel', $request['channel'])->update(['is_last'=> false]);
            $message = Message::query()->create($message);

            $event = new NewMessageEvent($message->id);
            broadcast($event)->toOthers();

            return response()->json(['message' => $message]);
        }catch (\Throwable $error){
            DB::rollBack();
            return response($error->getMessage(), 403);
        }
    }

}
