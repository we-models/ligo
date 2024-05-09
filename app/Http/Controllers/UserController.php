<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use GuzzleHttp\Client;
use App\Interfaces\MainControllerInterface;
use App\Mail\NewUserMail;
use App\Models\Business;
use App\Models\NewRole;
use App\Models\User;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\UserRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use \Illuminate\Contracts\Foundation;
/**
 *
 */
class UserController extends BaseController implements  MainControllerInterface {

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;


    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var string
     */
    public string $object = User::class;

    /**
     * @param LogRepository $logRepo
     * @param UserRepository $userRepo
     * @param BusinessRepository $businessRepo
     */
    public function __construct(LogRepository $logRepo, UserRepository $userRepo) {
        $this->logRepository = $logRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse {
        $rq = getRequestParams($request);
        $users = $this->userRepository->search($rq->search)->whereHas(BUSINESS_IDENTIFY)->sortable();
        return  $this->userRepository->getResponse($users, $rq);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users'
        ]);

        $password = generateRandomString();

        $request['password']= Hash::make($password);

        $request['code'] = generateUserCode();

        $business = Business::query()->where('code', session("business"))->first();

        $mail = new stdClass();
        $mail->address = getConfigValue('MAIL_USERNAME');
        $mail->name = $business->name;
        $mail->subject =  __("New Account");
        $mail->mail = ['email' => $request['email'], 'password' => $password];

        $input = $request->all();
        DB::beginTransaction();
        try {

            $user = $this->userRepository->create($input);
            setEmailConfiguration();

            Mail::to($user->email)->send(new NewUserMail($mail));

            $business = Business::query();
            $business = getBusiness($business)->pluck('id')->toArray();
            $business = array_intersect($request['business'], $business);
            $user->business()->syncWithPivotValues($business, ['model_type' => User::class]);
            $this->saveManipulation($user);
            DB::commit();

            $newUser = [
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => $password,
                BUSINESS_IDENTIFY => session("business")
            ];

            syncWp($newUser, 'user-register');

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
        $fields = (new User)->getFields();
        $user  = $this->userRepository;
        $user = $user->formatQuery();
        $user = $user->find($id);
        if (empty($user)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $user,
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> __('Show the User'),
            'url' => '#'
        ]);
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory  {
        $fields = (new User)->getFields();
        $user  = $this->userRepository;
        $user = $user->formatQuery();
        $user = $user->find($id);
        if (empty($user)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $user,
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> __('Update the user'),
            'url' => route('user.update', ['locale' => $lang, 'user' => $id])
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory  {
        $request->validate([
            'name' => 'required|max:255',
            'email' => "unique:users,email,$id,id"
        ]);
        $user = User::query()->find($id);

        $business = Business::query();
        $business = getBusiness($business)->pluck('id')->toArray();
        $business = array_intersect($request['business'], $business);

        $user->business()->syncWithPivotValues($business, ['model_type' => User::class]);
        DB::beginTransaction();
        try {
            $user->email = $request['email'];
            $user->name = $request['name'];
            $this->saveManipulation($user, 'updated');
            $user->save();


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
     * @throws Exception
     */
    public function destroy(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory   {
        if(auth()->user()->ID == $id) throw new Exception(__("Can't delete your own user"));
        $user = User::query()->find($id);
        DB::beginTransaction();
        try {
            $this->saveManipulation($user, 'deleted');
            $user->delete();
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
    public function logs(Request $request, string $lang): Response|JsonResponse  {
        return getAllModelLogs($request,User::class, $this->logRepository);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function assignRoles(Request $request) {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => NewRole::class,
            'columns' => User::class,
            'key' => 'user_has_role'
        ]);
    }

    public function changePassword(Request $request){
        return view('pages.user.change_password');
    }

    public function changePasswordSave(Request $request){
        DB::beginTransaction();
        try {
            $password = auth()->user()->getAuthPassword();
            if(!Hash::check($request['current_password'], $password)){
                throw new Exception(__('The current password is incorrect'));
            }
            if($request['new_password'] != $request['repeat_password']){
                throw new Exception(__('Passwords do not match'));
            }
            auth()->user()->password = Hash::make($request['new_password']);
            auth()->user()->save();
            DB::commit();
            return view('pages.user.change_password')->with('success', __('The password was changed successfully'));
        }catch (\Throwable $e){
            DB::rollBack();
            return view('pages.user.change_password')->with('error', $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return Factory|View|Foundation\Application
     */
    public function assignChannel(Request $request): Factory|View|Foundation\Application {
        return view('pages.assignments.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => User::class,
            'columns' => Channel::class,
            'key' => 'channel_has_user',
            'unique' => true,
            'general' => true
        ]);
    }
}
