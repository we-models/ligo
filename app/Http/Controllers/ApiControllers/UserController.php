<?php

namespace App\Http\Controllers\ApiControllers;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Models\Business;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Exception;

class UserController extends BaseController
{
    use SendsPasswordResetEmails;


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);

        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $user = User::query()->where('email', $request['email'])->whereHas(BUSINESS_IDENTIFY, function($q) use ($request){
            $q->where('code', $request[BUSINESS_IDENTIFY]);
        })->first();

        if($user == null) return response()->json(["error" => __("User not allowed")], status: 404);

        $user->is_app = true;

        if($user == null) return response()->json(["error" => __("User not exists")], status: 404);

        if(!$user->hasVerifiedEmail()){
            setEmailConfiguration();
            event(new Registered( $user));
            return response()->json(["error" => __("user not verified")], status: 401);
        }

        $credentials = $request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            return response()->json(["error" => __("Incorrect credentials")], status: 401);
        }
        $user->tokens()->each(function ($token, $key) {
            $token->revoke();
            $token->delete();
        });

        $accessToken = $user->createToken('AuthToken')->accessToken;

        return response()->json([
            'user' => Auth::user(),
            'access_token' => $accessToken
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function is_logged(Request $request, String $lang){
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);

        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);
        if(Auth::check()){

            $object = TheObject::query()
                ->where('owner', auth()->user()->getAuthIdentifier())
                ->whereHas('object_type', function($q){
                    $q->where('slug', 'tal_reg')->whereHas(BUSINESS_IDENTIFY);
                })->with('object_type')->first()->toArray();

            $user = User::query()->where('id', auth()->user()->getAuthIdentifier())->first()->toArray();

            if(!empty($object)){
                $object['custom_fields'] = getCustomFieldsRelations("object_type=" . $object['object_type']['id'], $this,  $object['id'], false, true);
                $user['profile'] = $object;
            }

            return response()->json($user);
        }else{
            throw new AuthenticationException("Unauthenticated.");
        }
    }

    public function register(Request $request, String $lang ): JsonResponse
    {
        set_time_limit(180);
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8'
        ]);

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        DB::beginTransaction();
        try {
            $rq = $request->all();
            $business = Business::query()->where('code', $request[BUSINESS_IDENTIFY])->first();
            $request['password']= Hash::make($request['password']);
            $request['code'] = generateUserCode();
            $input = $request->only('name', 'email', 'password', 'code');
            $user = User::create($input);
            $user->business()->syncWithPivotValues($business, ['model_type' => User::class]);

            setEmailConfiguration();

            $user->is_app = true;

            event(new Registered( $user));
            DB::commit();

            syncWp($rq, 'user-register');

            return response()->json([
                'user' => $user
            ]);
        }catch (\Throwable $e){
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], status: 403);
        }
    }

    /**
     * @throws Exception
     */
    public function recovery(Request $request, String $lang){
        $request->validate([
            'email' => 'required',
        ]);
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        $user = User::query()->where('email', $request['email'])->whereHas('all_business', function ($q) use ($request){
            $q->where('code', $request[BUSINESS_IDENTIFY]);
        })->first();
        if(empty($user)) response()->json(['error' => __('User not allowed')], status: 404);
        $user->is_app = true;
        $request['is_app'] = true;
        $status = Password::sendResetLink($request->only('email', 'is_app'));

        return $status === Password::RESET_LINK_SENT ?  response()->json(['success' => true]) : throw new Exception($status);
    }



    /**
     * @param Request $request
     * @param int $id
     * @throws Exception
     */
    public function reset(Request $request, String $lang )
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                $updated_user = $user;

                event(new PasswordReset($user));
            }
        );
        syncWp($request->all(), 'change-password');
        return $status === Password::PASSWORD_RESET ? response()->json(['success' => true]) :  throw new Exception($status);
    }

    public function logout(Request $request){
        $request->session()->forget(BUSINESS_IDENTIFY);
        Auth::logout();
        return response()->json(['success' => __('Logout')]);
    }

    public function profile(Request $request){

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $input = $request->all();

        try {
            DB::beginTransaction();
            $user = User::query()->find(auth()->user()->getAuthIdentifier());
            if($user == null) throw __('user not exists');
            $user->name = $input['name'] . " " . $input['last_name'];


            $pc = $input['password_current'];
            $ps = $input['password'];
            $psc = $input['confirm_password'];
            $match = Hash::check($pc, auth()->user()->getAuthPassword());

            if(!empty($pc) && !empty($ps) && !empty($psc) && $ps == $psc && $match){
                $user->password = Hash::make($input['password']);
                $pc = $input['password'];
            }
            $user->save();

            $object = ObjectType::query()->where('slug', 'tal_reg')->whereHas(BUSINESS_IDENTIFY)->first();
            if($object == null) throw __('object data not exists');

            $object = TheObject::query()->where([
                'object_type' => $object->id,
                'owner' => $user->id
            ])->whereHas(BUSINESS_IDENTIFY)->latest()->first();

            $img = null;
            if(!empty($input['image'])){
                $img = $this->saveImage($input['image_name'], $input['extension'], $input['image_size'], $input['image']);
            }

            $object = $this->detachField($object,'tal_reg_info-no' );
            $object = $this->detachField($object,'tal_reg_info-ap' );
            $object = $this->detachField($object,'tal_reg_info-se_te' );

            $object = $this->fillField($object, 'tal_reg_info-no', $input['name']);
            $object = $this->fillField($object, 'tal_reg_info-ap', $input['last_name']);
            $object = $this->fillField($object, 'tal_reg_info-se_te', $input['phone']);

            if(!empty($img)){
                $object = $this->detachField($object,'glo_fot_perf' );
                $object = $this->fillField($object, 'glo_fot_perf', $img['image']->id);
            }

            $all_relations = [
                [
                    'key' => 'tal_reg_info-se',
                    'value' =>  'sex_gender'
                ],
                [
                    'key' => 'tal_reg_info-se_pa',
                    'value' =>  'country'
                ]
            ];

            foreach ($all_relations as $rl){
                $relation_db = ObjectTypeRelation::query()->where(['slug'=> $rl['key'], 'enable' => true] )->first();
                $relation = TheObject::query()->where('wp_id', $input[$rl['value']])
                    ->whereHas('object_type', function($q){
                        $q->where('type', 'taxonomy');
                    })->whereHas(BUSINESS_IDENTIFY)->first();
                if(!empty($relation_db) && !empty($relation)) {
                    $object->relation_value()->detach();
                    $object->relation_value()->attach($relation->id, ['relation_object' => $relation_db->id]);
                }
            }

            $obj = parseWpData($object->id);

            $obj = syncWp($obj, 'update-object', 'UPDATE');

            $obj = json_decode($obj);
            if($obj != null && isset($obj->Error)){
                throw new Exception($obj->Error);
            }

            $this->saveManipulation($object, 'updated');

            if($pc !== null){
                Auth::shouldUse('web');
                if(!Auth::attempt([
                    'email' => $user->email,
                    'password' => $pc
                ])){
                    throw new Exception(__("Incorrect credentials"));
                }

                $user->tokens()->each(function ($token, $key) {
                    $token->revoke();
                    $token->delete();
                });

                $accessToken = $user->createToken('AuthToken')->accessToken;
            }else{
                $accessToken = request()->header('Authorization');
                $accessToken = str_replace('Bearer ', "", $accessToken);
            }

            DB::commit();
            $imgwp = null;
            if(!empty($img)){
                $imgwp = $img['wp']->post_id;
            }
            return response()->json([
                'access_token' => $accessToken,
                'profile_image' => $imgwp
            ]);

        }catch (\Throwable $error){
            DB::rollBack();
            return response()->json(["error" => $error->getMessage()], status: 403);
        }
    }

}
