<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\CommentRating;
use App\Models\RatingType;
use App\Repositories\CommentRepository;
use App\Repositories\LogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private CommentRepository $commentRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @param CommentRepository $commentRepository
     * @param LogRepository $logRepository
     */
    public function __construct(CommentRepository $commentRepository, LogRepository $logRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->logRepository = $logRepository;
    }


    public function all(Request $request){
        try{
            $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
            session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);
            $rq = getRequestParams($request);
            $comments = $this->commentRepository->search($rq->search);
            if(isset($request['object'])){
                $comments = $comments->where('object', $request['object']);
            }else{
                throw new \Exception("Please add an ID");
            }
            $comments = $comments->whereHas(BUSINESS_IDENTIFY)->with(['object.object_type.rating_types', 'ratings.rating_type'])->sortable();
            return  $this->commentRepository->getResponse($comments, $rq);
        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    public function rating_types(Request $request){
        try {
            $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
            session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

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
        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }
}
