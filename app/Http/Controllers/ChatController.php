<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Channel;
use App\Models\Message;
use App\Repositories\ChannelRepository;
use App\Repositories\MessageRepository;
use Exception;
use http\Env\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{

    private ChannelRepository $channelRepository;
    private MessageRepository $messageRepository;

    /**
     * @param ChannelRepository $channelRepo
     * @param MessageRepository $messageRepo
     */
    public function __construct(ChannelRepository $channelRepo, MessageRepository $messageRepo)
    {
        $this->channelRepository = $channelRepo;
        $this->messageRepository = $messageRepo;
    }

    public function all(Request $request): JsonResponse
    {
        $channels = $this->channelRepository->makeModel()
            ->query()->where('intermediary', auth()->user()->getAuthIdentifier())
            ->orWhere('user1', auth()->user()->getAuthIdentifier())
            ->orWhere('user2', auth()->user()->getAuthIdentifier())
            ->with($this->channelRepository->includes)
            ->with('profile_user1', function ($u){
                $u->with('images');
            })
            ->with('profile_user2', function ($u){
                $u->with('images');
            })
            ->with('messages', function($m){
                $m->orderBy('created_at', 'DESC')->where('is_last', true);
            });

        if(isset( $request['search'])){
            $search = $request['search'];
            $channels = $channels->where(function ($q) use ($search){
                $q->orWhereHas('profile_user1', function ($p) use ($search){
                    $p->where('name', 'LIKE', '%' . $search . '%' );
                });
                $q->orWhereHas('profile_user2', function ($p) use ($search){
                    $p->where('name', 'LIKE', '%' . $search . '%' );
                });
                $q->orWhereHas('messages', function ($p) use ($search){
                    $p->where('message', 'LIKE', '%' . $search . '%' );
                });
            });
        }

        $channels = $channels->paginate(20);
        return response()->json($channels);
    }

    public function get_channels (Request $request){
        $channels = $this->channelRepository->makeModel()
            ->query()->where('intermediary', auth()->user()->getAuthIdentifier())
            ->orWhere('user1', auth()->user()->getAuthIdentifier())
            ->orWhere('user2', auth()->user()->getAuthIdentifier());
        return $channels->pluck('name')->toArray();
    }

    public function get_chats(Request $request){
        $messages = Message::query()
            ->where('channel', $request['channel'])
            ->whereHas('channel', function ($q){
                $id = auth()->user()->getAuthIdentifier();
                $q->where('user1', $id)
                    ->orWhere('user2', $id)
                    ->orWhere('intermediary', $id);
            })
            ->with('channel', function($q){
                $q->with('profile_user1', function ($u){
                    $u->with('images');
                })->with('profile_user2', function ($u){
                    $u->with('images');
                })->with(['user1', 'user2']);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        return response()->json($messages);
    }


    public function index(Request $request): Factory|View|Application
    {
        return view('pages.chat.chat');
    }

    /**
     * @throws Exception
     */
    public function get_individual(Request $request){
        $channel = Channel::query()->where(['name'=> $request['code'], 'intermediary' => auth()->user()->getAuthIdentifier() ])
            ->with([BUSINESS_IDENTIFY, 'user1', 'user2', 'intermediary'])
            ->with('profile_user1', function ($u){
                $u->with('images');
            })
            ->with('profile_user2', function ($u){
                $u->with('images');
            })
            ->with('messages', function($m){
                $m->where('is_last', true)->first();
            })->first();
        if(empty($channel)) throw new Exception("Empty");
        return response()->json($channel);
    }

    public function send(Request $request){
        try {
            DB::beginTransaction();
            unset($request['id']);
            unset($request['created_at']);
            $request['is_from_intermediary'] = $request['is_from_intermediary'] == 'false' ? false : true;
            $request['is_last'] = true;
            $input = $request->all();
            Message::query()->where('channel', $request['channel'])->update(['is_last'=> false]);
            $message = Message::query()->create($input);
            broadcast(new NewMessageEvent($message->id))->toOthers();

            DB::commit();
            return response()->json(['message' => $message]);
        }catch (\Throwable $error){
            DB::rollBack();
            return response($error->getMessage(), 403);
        }
    }
}
