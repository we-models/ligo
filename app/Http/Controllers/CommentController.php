<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Business;
use App\Models\Comment;
use App\Http\Controllers\Controller;
use App\Models\CommentRating;
use App\Models\RatingType;
use App\Repositories\CommentRepository;
use App\Repositories\LogRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;

class CommentController extends BaseController implements MainControllerInterface
{
    private CommentRepository $commentRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Comment::class;

    /**
     * @param CommentRepository $commentRepo
     * @param LogRepository $logRepo
     */
    public function __construct(CommentRepository $commentRepo, LogRepository $logRepo)
    {
        $this->commentRepository = $commentRepo;
        $this->logRepository = $logRepo;
        $this->setIcons(false);
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $comments = $this->commentRepository->search($rq->search);
        if(isset($request['object'])){
            $comments = $comments->where('object', $request['object'])->orderBy('created_at', 'DESC');
        }
        $comments = $comments->whereHas(BUSINESS_IDENTIFY)->with([BUSINESS_IDENTIFY, 'object.object_type.rating_types', 'ratings.rating_type'])->sortable();
        return  $this->commentRepository->getResponse($comments, $rq);
    }

    public function rating_types(Request $request){
        $objectType = $request['object_type'];
        $objectId = $request['object'];

        $rating_types = RatingType::query()
            ->where('object_type', $objectType)
            ->get()
            ->toArray();

        foreach ($rating_types as &$rt) {
            $commentRating = CommentRating::query()
                ->join('comments', 'comments.id', '=', 'comment_ratings.comment')
                ->where('comments.object', $objectId)
                ->where('comment_ratings.rating_type', $rt['id'])
                ->avg('rating');

            $rt['average'] = $commentRating ;
        }
        return response()->json($rating_types);
    }

    public function save(Request $request){
        $bs = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
        $input = $request->all();
        try {
            DB::beginTransaction();
            $input['user'] = auth()->user()->getAuthIdentifier();
            $comment = $this->commentRepository->create($input);
            $comment->business()->syncWithPivotValues($bs->id, ['model_type' => Comment::class]);
            $this->saveManipulation($comment);
            if(isset($input['ratings'])){
                for($i=0; $i < count($input['ratings']); $i++){
                    CommentRating::query()->create([
                        'rating_type' => $input['ratings'][$i]['id'],
                        'comment' => $comment->id,
                        'rating' => $input['ratings'][$i]['value']
                    ]);
                }
            }

            DB::commit();
            $comment = $this->commentRepository->makeModel()->where('id', $comment->id)->with(['object', 'user', BUSINESS_IDENTIFY, 'ratings.rating_type'])->get();
            return response()->json($comment[0]);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        $bs = $request['business'];
        // IS necessary remove the business from request
        unset($request['business']);

        $input = $request->all();
        try {
            DB::beginTransaction();
            $comment = $this->commentRepository->create($input);
            if(userCanViewBusiness($bs) && isset($bs)){
                $comment->business()->syncWithPivotValues($bs, ['model_type' => Comment::class]);
            }else{
                $bs = $this->commentRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $comment->business()->syncWithPivotValues($bs->id, ['model_type' => Comment::class]);
            }
            $this->saveManipulation($comment);
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
        $fields = $this->commentRepository->makeModel()->getFields();
        $comment = $this->commentRepository->find($id);
        if (empty($comment)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->commentRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->commentRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the comment'),
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
        $fields = $this->commentRepository->makeModel()->getFields();
        $comment = $this->commentRepository->find($id);
        if (empty($comment)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->commentRepository->makeModel()->whereHas(BUSINESS_IDENTIFY)->with($this->commentRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Update the comment'),
            'url' => route('comment.update', ['locale' => $lang, 'comment' => $id])
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
        $comment = $this->commentRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($comment == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $comment->update($input);
            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->commentRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $comment->business()->syncWithPivotValues($bs, ['model_type' => Comment::class]);
            $this->saveManipulation($comment, 'updated');

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
        $comment = $this->commentRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($comment == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($comment, 'deleted');
            $comment->delete();
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
        return getAllModelLogs($request,Comment::class, $this->logRepository);
    }
}
