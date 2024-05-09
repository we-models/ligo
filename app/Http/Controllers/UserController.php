<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Mail\NewUserMail;
use App\Models\ImageFile;
use App\Models\NewRole;
use App\Models\User;
use App\Repositories\LogRepository;
use App\Repositories\UserRepository;
use Exception;
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
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use stdClass;
use Throwable;

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

        $users = $this->userRepository->search($rq->search);

        if ($request['roles']) {
            $roleIds = explode(',',$request['roles']);

            $users = $users->with('roles')->whereHas('roles', function ($query) use ($roleIds) {
                $query->whereIn('id', $roleIds);
            });
        }

        $users = $users->sortable();

        return  $this->userRepository->getResponse($users, $rq);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory {
        try {
            $request->validate([
                'name' => 'required|max:255',
                'email' => [
                    'required',
                    Rule::unique('users')->whereNull('deleted_at'),
                ],
            ],[
                'name.required' => __('The [name] field is required'),
                'email.unique' => __('The [email] field must be unique, a record with this email already exists'),
                'email.required' => __('The [email] field is required'),
            ]);

            $request['enable'] = $request['enable'] == 'on';

            $password = generateRandomString();

            $request['password']= Hash::make($password);

            $request['code'] = generateUserCode();

            $mail = new stdClass();
            $mail->address = getConfigValue('MAIL_USERNAME');
            $mail->name = config('APP_NAME', 'LIGO');
            $mail->subject =  __("New Account");
            $mail->mail =  "
            <p>
                <strong class='text-body-strong'>". __('Email') .": </strong> ". $request['email'] ." <br>
                <strong class='text-body-strong'>". __('Password') ." : </strong> ". $password ."
            </p>";
            $mail->titleText = __('New Account registered');
            $mail->primaryText = __('A new account has been created for you with this credentials');
            $mail->buttonText = __("Login");
            $mail->finalText = __("It is recommended that you change your password as soon as possible");


            $input = $request->all();
            DB::beginTransaction();
            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])->pluck('id')->toArray();
                unset($input['images']);
            }


            $user = $this->userRepository->create($input);
            $user->images()->syncWithPivotValues($image, ['model_type' => User::class]);

            $user->assignRole('General');
            setEmailConfiguration();

            Mail::to($user->email)->send(new NewUserMail($mail));
            $this->saveManipulation($user, 'created', $user->toJson());
            DB::commit();

            return response(__('Success'), 200);
        }catch (Throwable $e){
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
        $fields = (new User)->getFields(true);
        $user  = $this->userRepository;
        $user = $user->formatQuery();
        $user = $user->find($id);
        if (empty($user)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $user,
            'fields' => $fields,
            'icons' => [],
            'csrf' => csrf_token(),
            'title'=> 'user',
            'url' => '#'
        ]);
    }

    /**
     * @param string $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory  {
        $fields = (new User)->getFields(true);
        $user  = $this->userRepository;
        $user = $user->formatQuery();
        $user = $user->find($id);
        if (empty($user)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $user,
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> 'user',
            'url' => route('user.update', ['locale' => $lang, 'user' => $id])
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(Request $request, string $lang, int $id): Response|Application|ResponseFactory  {

        try {
            $request->validate([
                'name' => 'required|max:255',
                'email' => [
                    'required',
                    Rule::unique('users')->whereNull('deleted_at')->ignore($id),
                ],
            ]);

            $userLogin = auth()->user();
            $user = User::query()->find($id);

            $request['enable'] = $request['enable'] == 'on';


            if ($userLogin->getAuthIdentifier() === $id && $request['enable'] !== (bool) $user->enable) {
                throw new Exception(__("A user cannot disable their own account"));
            }

            $input = $request->all();
            DB::beginTransaction();

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])->pluck('id')->toArray();
                unset($input['images']);
            }

            //$user->email = $request['email'];
            $user->name = $request['name'];
            $user->lastname = $request['lastname'];
            $user->document_type = $request['document_type'];
            $user->ndocument = $request['ndocument'];
            $user->birthday = $request['birthday'];
            $user->ncontact = $request['ncontact'];
            $user->area = $request['area'];
            $user->enable= $request['enable'];

            $user->images()->syncWithPivotValues($image, ['model_type' => User::class]);

            $this->saveManipulation($user, 'updated', $user->toJson());
            $user->save();
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
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

        try {
            if(auth()->user()->getAuthIdentifier() == $id) throw new Exception(__("Can't delete your own user"));
            $user = User::query()->with('roles')->find($id);
            $roles = $user->toArray()['roles'];
            DB::beginTransaction();

            $roles = array_filter($roles, function($item){
               return in_array($item['name'], ALL_ACCESS);
            });

            if(count($roles) > 0){
                $roles = array_map(function($role){
                    return $role['name'];
                }, $roles);
                $my_roles = auth()->user()->roles()->pluck('name')->toArray();
                $intersec = array_intersect($roles, $my_roles);
                if(empty($intersec)){
                    throw new Exception(__("Can't remove a superadmin"));
                }
            }

            $this->saveManipulation($user, 'deleted', $user->toJson());
            $user->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (Throwable $e){
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
    public function assignRoles(Request $request): View|Factory|Application
    {
        return view('pages.general.assign', [
            'url' => route('assign.objects', app()->getLocale()) ,
            'rows' => NewRole::class,
            'columns' => User::class,
            'key' => 'user_has_role'
        ]);
    }

    public function changePassword(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('pages.user.change_password');
    }

    public function changePasswordSave(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
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
        }catch (Throwable $e){
            DB::rollBack();
            return view('pages.user.change_password')->with('error', $e->getMessage());
        }
    }

    /**
     * @param Activity $activity
     * @param string $eventName
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $ggs =99;
    }

    public function profile(){
        $keysToExclude = ['enable'];
        $fields = (new User)->getFields(true,$keysToExclude);

        $user = auth()->user();

        $user = User::query()->where('id',$user->getAuthIdentifier())
        ->with(['images','documentType','area'])->first();

        return view('pages.general.profile',[
            'object' => $user,
            'fields' => $fields,
            'icons' => "",
            'csrf' => csrf_token(),
            'title'=> 'my user profile',
            'urlUpdateProfile' => route('user.profile.update', [ 'locale'=> app()->getLocale()]),
            'urlUpdatePassword' => route('user.profile.update.password', [ 'locale'=> app()->getLocale()]),
        ]);
    }

    public function profileUpdate(Request $request ){
        try {
            $id  = auth()->user()->getAuthIdentifier();

            $request->validate([
                'name' => 'required|max:255',
                'email' => [
                    'required',
                    Rule::unique('users')->whereNull('deleted_at')->ignore($id),
                ],
            ],[
                'name.required' => __('The [name] field is required'),
                'email.unique' => __('The [email] field must be unique, a record with this email already exists'),
                'email.required' => __('The [email] field is required'),
            ]);

            $user = User::query()->find($id);

            $request['enable'] = true;

            $input = $request->all();
            DB::beginTransaction();

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])->pluck('id')->toArray();
                unset($input['images']);
            }

            $user->name = $request['name'];
            $user->lastname = $request['lastname'];
            $user->document_type = $request['document_type'];
            $user->ndocument = $request['ndocument'];
            $user->birthday = $request['birthday'];
            $user->ncontact = $request['ncontact'];
            $user->area = $request['area'];
            $user->enable= $request['enable'];

            $user->images()->syncWithPivotValues($image, ['model_type' => User::class]);

            $this->saveManipulation($user, 'updated');
            $user->save();
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }


    public function profileUpdatePassword(Request $request){

        $rules = [
            'email' => 'required|email',
            'password' => 'required',
            'password_confirmation' => 'required|min:8|different:password'
        ];

        $validatedData = $request->validate($rules);

        $user = auth()->user();


        if ($validatedData['email'] !== $user->email) {
            throw new Exception(__("The email is different from that of the logged in user"));
        }
        if(!Hash::check($validatedData['password'], $user->password)){
            throw new Exception(__('The current password is incorrect'));
        }
        DB::beginTransaction();
        try {
            $user->password = Hash::make($validatedData['password_confirmation']);
            $user->save();

            DB::commit();
            return response(__('Success'), 200);

        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }

    }


}
