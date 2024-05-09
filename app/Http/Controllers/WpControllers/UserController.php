<?php

namespace App\Http\Controllers\WpControllers;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Field;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    function save(Request $request){
        try{

            $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
            session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

            $input = $request->all();

            DB::beginTransaction();

            $user = User::query()->where('email', $input['wp_email'])
                ->whereHas(BUSINESS_IDENTIFY)->count();
            if($user > 0){
                throw __('user already exists');
            }

            $input['code'] = generateUserCode();

            $user = User::query()->create([
                'name' => $input['wp_name'],
                'email' => $input['wp_email'],
                'password' => Hash::make($input['wp_password']),
                'code' => $input['code'],
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $business = Business::query()->where('code', $request[BUSINESS_IDENTIFY])->first();
            $user->business()->syncWithPivotValues($business->id, ['model_type' => User::class]);

            $object = ObjectType::query()->where('slug', 'tal_reg')->whereHas(BUSINESS_IDENTIFY)->first();

            if($object == null) throw __('object data not exists');

            $object = TheObject::query()->create([
                'name' =>  $input['wp_name'] . " " . $input['wp_lastname'] . " " . $input['code'],
                'description' => '',
                'excerpt' => '',
                'object_type' => $object->id,
                'visible' => true,
                'owner' => $user->id
            ]);

            $object->business()->syncWithPivotValues($business->id, ['model_type' => TheObject::class]);

            $object = $this->fillField($object, 'tal_reg_info-no', $input['wp_name']);
            $object = $this->fillField($object, 'tal_reg_info-ap', $input['wp_lastname']);
            $object = $this->fillField($object, 'tal_reg_info-se_co', $input['wp_email']);
            $object = $this->fillField($object, 'tal_reg_info-se_te', $input['wp_phone']);

            $all_relations = [
                [
                    'key' => 'tal_reg_info-se',
                    'value' =>  'wp_sex_gender'
                ],
                [
                    'key' => 'tal_reg_info-se_pa',
                    'value' =>  'wp_country'
                ]
            ];

            foreach ($all_relations as $rl){
                $relation_db = ObjectTypeRelation::query()->where(['slug'=> $rl['key'], 'enable' => true] )->first();
                $relation = TheObject::query()->where('wp_id', $input[$rl['value']])
                    ->whereHas('object_type', function($q){
                        $q->where('type', 'taxonomy');
                    })->whereHas(BUSINESS_IDENTIFY)->first();
                if(!empty($relation_db) && !empty($relation)) {
                    $object->relation_value()->attach($relation->id, ['relation_object' => $relation_db->id]);
                }
            }



            $obj = parseWpData($object->id);

            $obj = syncWp($obj, 'save-object');

            $obj = json_decode($obj);
            if($obj != null && (isset($obj->Error) || isset($obj->error))){
                throw new Exception($obj->Error?? $obj->error);
            }
            if($obj != null && !isset($obj->Error)){
                $object->wp_id = $obj->post_id;
                $object->save();
            }

            $user->email_verified_at = now();
            $user->save();

            Auth::attempt([
                'email' => $input['wp_email'] ,
                'password' => $input['wp_password']
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

    function fillField($object, $slug, $value){
        $field_db = Field::query()->where(['slug'=> $slug, 'enable' => true])->first();
        if(!empty($field_db)) $object->field_value()->attach($field_db->id, ['value' => $value]);
        return $object;
    }

    public function recovery(Request $request, String $lang){
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $user = User::query()->where('email', $request['wp_email'])->whereHas('all_business', function ($q) use ($request){
            $q->where('code', $request[BUSINESS_IDENTIFY]);
        })->first();
        $user->is_app = true;
        $token = Password::broker()->createToken($user);
        return response()->json([
            'recovery_token' => $token
        ]);
    }

    public function reset(Request $request, $lang){
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);
        $input = $request->all();

        $reset_token = DB::table('password_resets')->where('email', $input['wp_email'])->first();
        if(empty($reset_token)) throw __('invalid user');
        $valid = Hash::check($input['wp_token'], $reset_token->token);
        if(!$valid) throw __('invalid token');

        $user = User::query()->where('email',$input['wp_email'] )->first();
        $user->forceFill([
            'password' => Hash::make($input['wp_password1'])
        ])->setRememberToken(Str::random(60));

        $user->save();
        setEmailConfiguration();
        event(new PasswordReset($user));
        return response()->json(['success' => true]);
    }
}
