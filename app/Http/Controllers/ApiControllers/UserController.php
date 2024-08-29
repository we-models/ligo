<?php

namespace App\Http\Controllers\ApiControllers;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Models\Business;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
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

        $user = User::query()->where('email', $request['email'])->first();

        if($user == null) return response()->json(["error" => __("User not allowed")], status: 404);

        $user->is_app = true;

        if($user == null) return response()->json(["error" => __("User not exists")], status: 404);

        // if(!$user->hasVerifiedEmail()){
        //     setEmailConfiguration();
        //     event(new Registered( $user));
        //     return response()->json(["error" => __("user not verified")], status: 401);
        // }

        $credentials = $request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            return response()->json(["error" => __("Incorrect credentials")], status: 401);
        }
        $user->tokens()->each(function ($token, $key) {
            $token->revoke();
            $token->delete();
        });

        $objectType = ObjectType::query()->where('slug', 'regusu_cli_01')->first();
        $object = TheObject::query()->where('owner', $user->id)->where('object_type', $objectType->id)->first();

        
        $accessToken = $user->createToken('AuthToken')->accessToken;

        return response()->json([
            'user' => Auth::user(),
            'user_register' => $objectType,
            'access_token' => $accessToken
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function is_logged(Request $request, String $lang){
        // $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);

        // session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);
        if(Auth::check()){

            $object = TheObject::query()
                ->where('owner', auth()->user()->getAuthIdentifier())
                ->whereHas('object_type', function($q){
                    $q->where('slug', 'tal_reg')->whereHas(BUSINESS_IDENTIFY);
                })->with('object_type')->first();
            if(!is_null($object)) $object->toArray();

            $user = User::query()->where('id', auth()->user()->getAuthIdentifier())->first()->toArray();

            if(!empty($object)){
              $objectTypeId = is_int($object['object_type']) ? $object['object_type'] : $object['object_type']['id'];
                $object['custom_fields'] = getCustomFieldsRelations("object_type=" . $objectTypeId, $this,  $object['id'], false, true);
                $user['profile'] = $object;
            }

            return response()->json($user);
        }else{
            throw new AuthenticationException("Unauthenticated.");
        }
    }

    public function register(Request $request, String $lang ): JsonResponse
    {
        try{

            $input = $request->all();

            DB::beginTransaction();

            $user = User::query()->where('email', $input['email'])->count();
            if($user > 0){
                throw new Exception(__('user already exists')) ;

            }

            $input['code'] = generateUserCode();
            $name = isset($input['name']) ? $input['name'] : '';
            $user = User::query()->create([
                'name' => $name,
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'code' => $input['code'],
                'is_app' => 1
            ]);

            $object = ObjectType::query()->where('slug', 'regusu_cli_01')->first();

            if($object == null) throw __('object data not exists');

            $object = TheObject::query()->create([
                'name' =>  $name,
                'description' => '',
                'excerpt' => '',
                'object_type' => $object->id,
                'internal_id' => TheObject::newId($object->id),
                'visible' => true,
                'owner' => $user->id
            ]);

            $user->save();

            //Assign role to user
            $user->assignRole('General');

            Auth::attempt([
                'email' => $input['email'] ,
                'password' => $input['password']
            ]);

            $user->tokens()->each(function ($token, $key) {
                $token->revoke();
                $token->delete();
            });

            $accessToken = $user->createToken('AuthToken')->accessToken;

            DB::commit();

            return response()->json([
                'access_token' => $accessToken
            ]);

        }catch (\Throwable $error){
            DB::rollBack();
            return response()->json(["error" => $error->getMessage()], status: 403);
        }

    }

    /**
     * @throws Exception
     */
    public function recovery(Request $request, String $lang){
        $request->validate([
            'email' => 'required',
        ]);
        // $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        // session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);
        $user = User::query()->where('email', $request['email'])->first();
        if(empty($user)) response()->json(['error' => __('User not allowed')], status: 404);
        // $user->is_app = true;
        $request['is_app'] = true;
        setEmailConfiguration();
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT ?  response()->json(['success' => true]) : throw new Exception($status);
    }



    /**
     * @param Request $request
     * @param int $id
     * @throws Exception
     */
    public function resetOld(Request $request, String $lang )
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
        // syncWp($request->all(), 'change-password');
        return $status === Password::PASSWORD_RESET ? response()->json(['success' => true]) :  throw new Exception($status);
    }

    public function reset(Request $request): JsonResponse
    {
        // Validar el código y el correo
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|confirmed|min:8',
        ]);

        // Verificar el código
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->code, $passwordReset->token)) {
            return response()->json(['code' => 'El código de verificación no es válido.'], 403);
        }

        // Restablecer la contraseña
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['email' => 'No se encontró ningún usuario con ese correo electrónico.'], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Borrar el código usado
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['success' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        // $request->session()->forget(BUSINESS_IDENTIFY);
        //Auth::logout();
        // Revoca el token de acceso actual
        $request->user()->token()->revoke();
        return response()->json(['success' => __('Logout')]);
    }

    public function profile(Request $request){

        try {
            $user = User::query()->find(auth()->user()->getAuthIdentifier());
            if($user == null) throw __('user not exists');

            $objectType = ObjectType::query()->where('slug', 'regusu_cli_01')->first();
            if($objectType == null) throw __('object type not exists');

            $object = TheObject::query()->where([
                'object_type' => $objectType->id,
                'owner' => $user->id
            ])->latest()->first();

            if($object == null) throw __('object data not exists');
            $objects = $objects->toArray();
            $objects = array_map(function($object){
                $object['custom_fields'] = getCustomFieldsRelations(
                    "object_type=" . $object['object_type']['id'],
                    $this,
                    $object['id'],
                    false,
                    true
                );
                $object['has_custom_fields'] = count($object['custom_fields']) > 0;
                return $object;
            }, $objects);

            return response()->json([
                'profile' => $object
            ]);

        }catch (\Throwable $error){
            return response()->json(["error" => $error->getMessage()], status: 403);
        }
    }

    public function profileUpdate(Request $request){

        $input = $request->all();

        try {
            DB::beginTransaction();
            $user = User::query()->find(auth()->user()->getAuthIdentifier());
            if($user == null) throw __('user not exists');
            $user->name = $input['name'] ?? '' . " " . $input['last_name'] ?? '';


            $pc = $input['password_current'] ?? null;
            $ps = $input['password'] ?? null;
            $psc = $input['confirm_password'] ?? null;
            $match = Hash::check($pc, auth()->user()->getAuthPassword());

            if(!empty($pc) && !empty($ps) && !empty($psc) && $ps == $psc && $match){
                $user->password = Hash::make($input['password']);
                $pc = $input['password'];
            }
            $user->save();

            $objectType = ObjectType::query()->where('slug', 'regusu_cli_01')->first();
            if($objectType == null) throw __('object type not exists');

            $object = TheObject::query()->where([
                'object_type' => $objectType->id,
                'owner' => $user->id
            ])->latest()->first();

            if($object == null) throw __('object data not exists');

            $img = null;
            if(!empty($input['image'])){
                $img = $this->saveImage($input['image_name'], $input['extension'], $input['image_size'], $input['image']);
            }


            if(isset($input['name']) && !empty($input['name'])){
                $object = detachField($object, 'regusu_cli_04');
                $object = fillField($object, 'regusu_cli_04', $input['name']);
            }

            if(isset($input['lastname']) && !empty($input['lastname'])){
                $object = detachField($object, 'regusu_cli_05');
                $object = fillField($object, 'regusu_cli_05', $input['lastname']);
            }

            if(isset($input['info']) && !empty($input['info'])){
                $object = detachField($object, 'regusu_cli_12');
                $object = fillField($object, 'regusu_cli_12', $input['info']);
            }

            if(isset($input['email']) && !empty($input['email'])){
                $userExist = User::query()->where('email',$input['email'])->first();
                if($userExist != null) throw __('user email already exists');
                $object = detachField($object, 'regusu_cli_03');
                $object = fillField($object, 'regusu_cli_03', $input['email']);
            }

            if(!empty($img)){
                $object = detachField($object,'regusu_cli_10' );
                $object = fillField($object, 'regusu_cli_10', $img['image']->id);
            }

            $all_relations = [
                [
                    'key' => 'regusu_cli_09',
                    'value' =>  'sex_gender'
                ],
                [
                    'key' => 'regusu_cli_08',
                    'value' =>  'city'
                ],
                [
                    'key' => 'regusu_cli_07',
                    'value' =>  'country'
                ]
            ];

            foreach ($all_relations as $rl){
                $relation_db = ObjectTypeRelation::query()->where(['slug'=> $rl['key'], 'enable' => true] )->first();
                $relation = TheObject::query()->where('id', $input[$rl['value']] ?? null)
                    ->whereHas('object_type', function($q){
                        $q->where('type', 'taxonomy');
                    })->first();
                if(!empty($relation_db) && !empty($relation)) {
                    $object->relation_value()->detach();
                    $object->relation_value()->attach($relation->id, ['relation_object' => $relation_db->id]);
                }
            }

            if(!empty($input['phone'])){
                $relation_db = ObjectTypeRelation::query()->where(['slug'=> 'regusu_cli_06', 'enable' => true] )->first();
                $relationPhone = $this->addPhoneProfile($user,$input,$request);

                if(!empty($relation_db) && !empty($relationPhone)) {
                    $object->relation_value()->detach();
                    $object->relation_value()->attach($relationPhone->id, ['relation_object' => $relation_db->id]);
                }

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
            $profileImg = null;
            if(!empty($img)){
                $profileImg = $img['image'];
            }
            return response()->json([
                'access_token' => $accessToken,
                'profile_image' => $profileImg
            ]);

        }catch (\Throwable $error){
            DB::rollBack();
            return response()->json(
                [
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], status: 403);
        }
    }

    public function addPhoneProfile($user,$input,$request){
        $objectType = ObjectType::query()->where('slug', 'ad_numcell_01')->first();

        if($objectType == null) throw __('object data not exists');

        $object = TheObject::query()->where('object_type', $objectType->id)->where('owner', $user->id)->first();

        if($object == null){
            $object = TheObject::query()->create([
                'name' =>  $user->name,
                'description' => '',
                'excerpt' => '',
                'object_type' => $objectType->id,
                'visible' => true,
                'owner' => $user->id
            ]);
        }

        $object = fillField($object, 'ad_numcell_04', $input['phone']);

        return $object;
    }

}
