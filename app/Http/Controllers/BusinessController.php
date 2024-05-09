<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Business;
use App\Models\ImageFile;
use App\Models\NewRole;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \Illuminate\Contracts\Foundation;

/**
 * IS THE CORE OF THE SYSTEM. EACH MODEL ON THE APP IS SPECIFIED FOR A BUSINESS
 */
class BusinessController extends BaseController implements MainControllerInterface {

    /**
     * @var BusinessRepository
     */
    private BusinessRepository $businessRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = Business::class;

    /**
     * @param BusinessRepository $roleRepo
     * @param LogRepository $logRepo
     */
    public function __construct(BusinessRepository $roleRepo, LogRepository $logRepo) {
        $this->businessRepository = $roleRepo;
        $this->logRepository = $logRepo;
        $this->setIcons(false);
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $business = $this->businessRepository->search($rq->search);
        $business = getBusiness($business)->sortable();
        return $this->businessRepository->getResponse($business, $rq);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory {
        // Generate a unique code for the business for use it instead ID
        $request['code'] = (string) Str::uuid();
        $input = $request->all();
        try {
            DB::beginTransaction();

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }

            $gallery = [];
            if(isset($input['gallery'])){
                $gallery = $input['gallery'];
                $gallery = ImageFile::query()->whereIn('id', $gallery)
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['gallery']);
            }


            $business =$this->businessRepository->create($input);
            $business->images()->syncWithPivotValues($image, ['model_type' => Business::class]);
            $business->gallery()->syncWithPivotValues($gallery, ['model_type' => Business::class, 'field' => 'gallery']);

            $this->saveManipulation($business);

            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws Exception
     */
    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        // For security only allowed users can view the business
        if(!userCanViewBusiness($id)) abort(403);
        $fields = $this->businessRepository->makeModel()->getFields();
        $business = $this->businessRepository;
        $business = $business->formatQuery();
        $business = $business->find($id);

        if (empty($business)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $business,
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the business'),
            'url' => '#'
        ]);
    }

    /**
     * @throws Exception
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory  {
        // For security only allowed users can edit the business
        if(!userCanViewBusiness($id)) abort(403);
        $fields =$this->businessRepository->makeModel()->getFields();

        $business = $this->businessRepository;
        $business = $business->formatQuery();
        $business = $business->find($id);

        if (empty($business)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $business,
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Update the business'),
            'url' => route('business.update', ['locale' => $lang, 'business' => $id])
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory {
        // For security only allowed users can update the business
        if(!userCanViewBusiness($id)) abort(403);
        $input = $request->all();
        $business =  $this->businessRepository->find($id);

        $image = [];
        if(isset($input['images'])){
            $image = $input['images'];
            $image = ImageFile::query()->whereIn('id', [$image])
                ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
        }

        $gallery = [];
        if(isset($input['gallery'])){
            $gallery = $input['gallery'];
            $gallery = ImageFile::query()->whereIn('id', $gallery)
                ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
        }

        try {
            DB::beginTransaction();
            $business->update($input);
            $business->images()->syncWithPivotValues($image, ['model_type' => Business::class]);
            $business->gallery()->syncWithPivotValues($gallery , ['model_type' => Business::class, 'field' => 'gallery']);
            $this->saveManipulation($business, 'updated');
            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory {
        // For security only allowed users can destroy the business
        if(!userCanViewBusiness($id)) abort(403);
        $business =  $this->businessRepository->find($id);

        try {
            if($business->code == session(BUSINESS_IDENTIFY)){
                throw new Exception(__('The user can not delete the current business'));
            }
            DB::beginTransaction();
            $this->saveManipulation($business, 'deleted');
            $business->delete();
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
     *
     * GET ALL LOGS FOR THE MODEL AND BUSINESS
     */
    public function logs(Request $request, string $lang): Response|JsonResponse {
        return getAllModelLogs($request,$this->object, $this->logRepository);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     *
     * IS USED AS A FALLBACK FOR MIDDLEWARE TO SET CURRENT BUSINESS ON THE SESSION
     */
    public function select(Request $request): View|Factory|Application {
        return view('pages.business.select');
    }

    /**
     * @param Request $request
     * @param string $locale
     * @param string $code
     * @return Application|RedirectResponse|Redirector|void
     */
    public function selectCode(Request $request, string $locale, string $code) {
        //FIND THE SELECTED BUSINESS
        $business = Business::query()->where('code', $code)->first();
        // IF THE BUSINESS IS EMPTY OR THE USER DOESN'T HAVE PERMISSIONS RETURN 403
        if( !empty($business) && userCanViewBusiness($business->id)){
            session([BUSINESS_IDENTIFY => $code]);
            //setcookie(BUSINESS_IDENTIFY, session(BUSINESS_IDENTIFY), time() + 864000 );
            return redirect(route('home', app()->getLocale()));
        }
        abort(403);
    }

    public function information(Request $request, string $lang, string $business): JsonResponse
    {
        $business = Business::query()
            ->with('images')
            ->where('code', $business)->first();
        return response()->json($business);
    }
}
