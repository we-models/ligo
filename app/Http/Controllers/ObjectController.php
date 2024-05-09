<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\ErrorLog;
use App\Models\Field;
use App\Models\File;
use App\Models\ImageFile;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Models\User;
use App\Repositories\BusinessRepository;
use App\Repositories\LogRepository;
use App\Repositories\ObjectRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Paymentez\Paymentez;
use Paymentez\Exceptions\PaymentezErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \Illuminate\Contracts\Foundation;
use Exception;
use stdClass;


class ObjectController extends BaseController implements MainControllerInterface
{

    /**
     * @var ObjectRepository
     */
    public ObjectRepository $objectRepository;

    /**
     * @var BusinessRepository
     */
    public BusinessRepository $businessRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = TheObject::class;

    /**
     * @param ObjectRepository $objectRepo
     * @param BusinessRepository $businessRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ObjectRepository $objectRepo, BusinessRepository $businessRepo, LogRepository $logRepo)
    {
        $this->objectRepository = $objectRepo;
        $this->businessRepository = $businessRepo;
        $this->logRepository = $logRepo;
        $this->setIcons(false);
    }

    public function getCustomFieldsRelations(string $parameters, int $object = 0):array{
        return getCustomFieldsRelations($parameters, $this, $object, true);
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse GET THE FIELDS, NAME, EMPTY OBJECT AND PERMISSION LIST FOR THE CURRENT OBJECT
     * GET THE FIELDS, NAME, EMPTY OBJECT AND PERMISSION LIST FOR THE CURRENT OBJECT
     */


    public function all(Request $request): Response|JsonResponse
    {
        $rq = getRequestParams($request);
        $objects = $this->objectRepository->search($rq->search);
        if(isset($request['object_type'])) $objects = $objects->where('object_type', $request['object_type']);
        if(isset($request['filling_method']) && $request['filling_method'] == 'own_selection') $objects = $objects->where('owner', auth()->user()->getAuthIdentifier());
        $objects = $objects->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        if(isset($request['all_item']))  return response()->json($objects->get());
        return  $this->objectRepository->getResponse($objects, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        set_time_limit(180);
        $bs = $request['business'];
        unset($request['business']);

        $request['visible'] = $request['visible'] == 'on';

        $input = $request->all();

        if($request['object_type'] == '43'){
            $input = $this->addPaymentez($input);
        }


        try {
            DB::beginTransaction();
            $object = $this->objectRepository->create($input);

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }

            $object->images()->syncWithPivotValues($image, ['model_type' => TheObject::class]);

            if(userCanViewBusiness($bs) && isset($bs)){
                $object->business()->syncWithPivotValues($bs, ['model_type' => TheObject::class]);
            }else{
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
                $object->business()->syncWithPivotValues($bs->id, ['model_type' => TheObject::class]);
            }
            foreach ($input as $key=>$field){

                if(str_contains($key, '$$lat') || str_contains($key, '$$long')  ){

                    if(str_contains($key, '$$lat')){
                        $key = str_replace('$$lat_', '', $key);
                    }
                    if(str_contains($key, '$$long')){
                        $key = str_replace('$$long_', '', $key);
                    }
                }
                $field_db = Field::query()->where(['slug'=> $key, 'enable' => true])->first();
                if(!empty($field_db)) $object->field_value()->attach($field_db->id, ['value' => $field]);

                $relation_db = ObjectTypeRelation::query()->where(['slug'=> $key, 'enable' => true] )->first();
                if(!empty($relation_db)) {
                    $vl = is_array($field) ? array_unique($field) : $field;
                    $object->relation_value()->attach($vl, ['relation_object' => $relation_db->id]);
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
            DB::commit();

            $this->saveManipulation($object);

            return response([__('Success'),$object], 200);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->objectRepository->makeModel()->getFields();
        $object = $this->objectRepository->find($id);

        if (empty($object)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->objectRepository->makeModel()->with($this->objectRepository->includes)->find($id)->toArray(),
            'fields' => $fields,
            'icons' => "[]",
            'csrf' => csrf_token(),
            'title'=> __('Show the Object'),
            'custom_fields' => $this->getCustomFieldsRelations("object_type=" . $object->object_type, $id) ,
            'url' => '#'
        ]);
    }

    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $fields = $this->objectRepository->makeModel()->getFields();
        $object = $this->objectRepository->find($id);
        if (empty($object)) return response(__('Not found'), 404);
        return response()->json([
            'object' => $this->objectRepository->makeModel()->with($this->objectRepository->includes)->find($id)->toArray(),
            'fields' => $fields,
            'icons' => "[]",
            'csrf' => csrf_token(),
            'title'=> __('Update the object'),
            'custom_fields' => $this->getCustomFieldsRelations("object_type=" . $object->object_type, $id) ,
            'url' => route('object.update', ['locale' => $lang, 'object' => $id])
        ]);
    }

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     *
     * IF THE USER HASN'T PERMISSIONS FOR THE SELECTED BUSINESS THE SYSTEM WILL APPLY THE CURRENT BUSINESS
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {
        set_time_limit(180);

        $bs = $request['business'];
        unset($request['business']);

        $request['visible'] = $request['visible'] == 'on';

        $input = $request->all();

        try {
            DB::beginTransaction();
            $object = $this->objectRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();

            if($object == null) throw new Exception(__('The user can not update this item'));
            if($request['parent'] == $id)  throw new Exception(__('Can not use the same object as parent'));

            $fieldToken = $object->field_value()->where('slug','token')->first();
            if ($fieldToken) {
                if($fieldToken->pivot->value != $request['token']){
                    $input = $this->updatePaymentez($input);
                }
            }

            $object->update($input);

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }
            $object->images()->syncWithPivotValues($image, ['model_type' => TheObject::class]);

            if(!userCanViewBusiness($bs) || !isset($bs)) {
                $bs = $this->businessRepository->makeModel()->where('code', session(BUSINESS_IDENTIFY))->first();
            }
            $object->business()->syncWithPivotValues($bs, ['model_type' => TheObject::class]);

            $delete = true;
            foreach ($input as $key=>$field){

                if(str_contains($key, '$$lat') || str_contains($key, '$$long')  ){
                    if(str_contains($key, '$$lat')){
                        $delete = true;
                        $key = str_replace('$$lat_', '', $key);
                    }
                    if(str_contains($key, '$$long')){
                        $key = str_replace('$$long_', '', $key);
                        $delete = false;
                    }
                }

                $field_db = Field::query()->where(['slug'=> $key, 'enable' => true, 'editable' => true])->first();
                if(!empty($field_db)) {
                    if($delete){
                        DB::table('object_field_value')->where(['object' => $id, 'field' => $field_db->id])->delete();
                    }
                    $object->field_value()->attach($field_db->id, ['value' => $field]);
                    if(!$delete) $delete = true;
                }
                $relation_db = ObjectTypeRelation::query()->where(['slug'=> $key, 'enable' => true, 'editable' => true])->first();
                if(!empty($relation_db)) {
                    DB::table('object_relations')->where(['relation_object'=> $relation_db->id, 'object' => $id])->delete();
                    $object->relation_value()->attach($field, ['relation_object' => $relation_db->id]);
                }
            }

            $obj = parseWpData($object->id);

            $obj = syncWp($obj, 'update-object', 'UPDATE');
            $obj = json_decode($obj);
            if($obj != null && isset($obj->Error)){
                throw new Exception($obj->Error);
            }

            $this->saveManipulation($object, 'updated');
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
        set_time_limit(180);
        $object = $this->objectRepository->makeModel()->where('id', $id)->whereHas(BUSINESS_IDENTIFY)->first();
        try {
            if($object == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($object, 'deleted');
            $arrayDataDeletePaymentez = ['object_type' => $object->object_type,'owner' => $object->owner,'object_type' => $object->token];
            $this->deletePaymentez($arrayDataDeletePaymentez);
            $obj = TheObject::query()->where('id', $object->id)
                ->with(['object_type', 'field_value', 'owner', 'parent', 'images', 'relation_value', 'field_value.type'])
                ->first()->toArray();

            $object->field_value()->detach();
            $object->relation_value()->detach();
            $object->images()->detach();
            $object->delete();
            DB::commit();
            syncWp($obj, 'delete-object', 'DELETE');
            return response()->json(['delete' => 'success']);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function addPaymentez($request){

        if($request['object_type'] == '43' && isset($request['owner']) && isset($request['glo_pag_m_tc_nu'])){
            try {
                $user = User::find($request['owner']);
                $applicationCode = getConfigValue('PAYMENTEZ_APP_CODE_CLIENT');
                $applicationKey = getConfigValue('PAYMENTEZ_APP_KEY_CLIENT');
                $productionEnable = filter_var(getConfigValue('PAYMENTEZ_PRODUCTION_MODE'), FILTER_VALIDATE_BOOLEAN);
                $request['token'] = '';

                if (empty($applicationCode) || empty($applicationKey)) {
                    return $request;
                }

                Paymentez::init($applicationCode, $applicationKey,$productionEnable);


                if ($productionEnable) {
                    $baseURI = getConfigValue('PAYMENTEZ_ENDPOINT_PROD');
                }else{
                    $baseURI = getConfigValue('PAYMENTEZ_ENDPOINT_STAGING');
                }

                $date = date_parse($request['glo_pag_m_tc_e']);

                $month = $date['month'];
                $year = $date['year'];
                $card = [
                    "number" => $request['glo_pag_m_tc_nu'],
                    "holder_name" => $request['glo_pag_m_tc_no'],
                    "expiry_month" => $month,
                    "expiry_year" => $year,
                    "cvc" => $request['glo_pag_m_tc_c'],
                ];
                $holderName = $card['holder_name'];

                $body =[
                    'user' => [
                        'id' => (string) $user->id,
                        'email' => $user->email
                        ],
                    'card' => $card
                ];

                //REGISTER CARD IN PAYMENTEZ
                $response = $this->httpPostPaymentez($baseURI,$body);

                if (isset($response['card'])) {

                    //DON'T SAVE CARD IF HAS STATUS REJECTED
                    if($response['card']['status'] == 'rejected') {
                        return $request;
                    }
                    $request['token'] = $response['card']['token'];
                    $lastNumbersCC = substr($request['glo_pag_m_tc_nu'], -4);
                    $firstNumbersBin = substr($response['card']['bin'], 0, 4);
                    $lastNumbersBin = substr($response['card']['bin'], -2);
                    $request['name'] = $firstNumbersBin.'-'.$lastNumbersBin.'xx-xxxx '.$lastNumbersCC;
                    $request['glo_pag_m_tc_nu'] = $firstNumbersBin.'-'.$lastNumbersBin.'xx-xxxx '.$lastNumbersCC;
                    $request['glo_pag_m_tc_c'] = '';
                    $request['glo_pag_m_tc_no'] = '';
                    unset($request['glo_pag_m_tc_e']);
                }

                return $request;
            } catch (\Throwable $e) {
                return response($e->getMessage(),400);
            }
        }else{
            return $request;
        }
    }

    public function updatePaymentez($request){

        if($request['object_type'] == '43' && isset($request['owner']) && isset($request['token'])){
            try {

                $request = $this->deletePaymentez($request);

                $request = $this->addPaymentez($request);

                return $request;
            } catch (\Throwable $e) {
                $request['token'] = '';
                return $request;
            }
        }

        if(!isset($request['token']) && ctype_digit($request['glo_pag_m_tc_nu'])){
            $request = $this->addPaymentez($request);
            return $request;
        }

    }

    public function deletePaymentez($request){

        if($request['object_type'] == '43' && isset($request['owner']) && isset($request['token'])){
            try {
                $user = User::find($request['owner']);
                $user =['id' => (string) $user->id, 'email' => $user->email ];
                $applicationCode = getConfigValue('PAYMENTEZ_APP_CODE_CLIENT');
                $applicationKey = getConfigValue('PAYMENTEZ_APP_KEY_CLIENT');
                $productionEnable = filter_var(getConfigValue('PAYMENTEZ_PRODUCTION_MODE'), FILTER_VALIDATE_BOOLEAN);

                if (empty($applicationCode) || empty($applicationKey)) {
                    return $request;
                }

                Paymentez::init($applicationCode, $applicationKey,$productionEnable);
                if ($request['token'] != '') {
                    $card = Paymentez::card();
                    $deleteCard = $card->delete($request['token'], $user);
                    $request['token'] = '';
                }

                return $request;
            } catch (\Throwable $e) {
                $request['token'] = '';
                return $request;
            }
        }

        if(!isset($request['token']) && isset($request['glo_pag_m_tc_nu']) && ctype_digit($request['glo_pag_m_tc_nu'])){
            $request = $this->addPaymentez($request);
            return $request;
        }

    }

    function httpPostPaymentez($url,$data) {
        $auth_token = Paymentez::auth();
        $body = json_encode($data);
        $userAgent = getConfigValue('PAYMENTEZ_USERAGENT');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'Auth-Token:' . $auth_token),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return json_decode($response,true);
    }

    public function duplicate(Request $request){
        try {
            DB::beginTransaction();

            $input = $request->all();
            $object = $this->objectRepository->find($input['object']);

            $newObject = $object->replicate();
            $newObject->name = $newObject->name . " Copy";
            $newObject->created_at = date('Y-m-d H:i:s');
            $newObject->save();

            //Copy images
            $images = array_map(function($image){ return $image['id'];}, $object->images()->get()->toArray());
            $newObject->images()->syncWithPivotValues($images, ['model_type' => TheObject::class]);

            //Copy business
            $business = array_map(function($bs){ return $bs['id'];}, $object->business()->get()->toArray());
            $newObject->business()->syncWithPivotValues($business, ['model_type' => TheObject::class]);

            //Copy fields
            $fields = $object->field_value()->get()->toArray();
            foreach ($fields as $key => $field){
                $newObject->field_value()->attach($field['id'], ['value' => $field['pivot']['value']]);
            }

            //Copy relations
            $relations = $object->relation_value()->get()->toArray();
            foreach ($relations as $key => $relation){
                $newObject->relation_value()->attach($relation['id'], ['relation_object' => $relation['pivot']['relation_object']]);
            }


            $obj = parseWpData($newObject->id);

            $obj = syncWp($obj, 'save-object');

            $obj = json_decode($obj);
            if($obj != null && (isset($obj->Error) || isset($obj->error))){
                throw new Exception($obj->Error?? $obj->error);
            }
            if($obj != null && !isset($obj->Error)){
                $newObject->wp_id = $obj->post_id;
                $newObject->save();
            }
            $this->saveManipulation($newObject);

            DB::commit();

        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }

    public function logs(Request $request, string $lang): Response|JsonResponse
    {
        return getAllModelLogs($request,TheObject::class, $this->logRepository, function($logs) use ($request){
            return $logs->where('properties', 'like', '%' .'"object_type":'.$request['object_type']. ',%');
        });
    }

    public function report(Request $request ): Factory|View|Application
    {
        $object_type = [
            'properties' => ['width' => 4, 'label' => __('Object type')],
            'attributes' => [
                'type' => 'object',
                'readonly'=> 'true',
                'name' =>'object_type',
                'required' => true,
                'multiple' => false ,
                'data' => (new ObjectType())->publicAttributes()
            ]
        ];
        $owner_object = [
            'properties' => ['width' => 5, 'label' => __('Owner')],
            'attributes' => [
                'type' => 'object',
                'name' =>'owner',
                'required' => false,
                'multiple' => true ,
                'data' => (new User())->publicAttributes()
            ]
        ];


        return view('pages.objects.report', [
            'object_type' => $object_type,
            'owner_object' =>  $owner_object,
            'languages' => config('app.available_locales'),
            'language' => app()->getLocale(),
            'csrf' => csrf_token(),
            'filter_link' => route('object.filters',  app()->getLocale()),
            'filtered_link' => route('object.report.filtered', app()->getLocale())
        ]);
    }

    /**
     * @throws Exception
     */
    public function reportFilter(Request $request){
        try{
            $input = $request->all();
            if(!isset($input['object_type'])){
                throw new Exception(__('Please add an object_type'));
            }

            $response = [];

            $object_type = ObjectType::query()->where('id', $input['object_type'])
                ->with('relations_with', function ($q){
                    $q->whereNull('tab')->with('relation', function($query){
                        $query->where('enable', true);
                    });
                    $q->orderBy('order', 'ASC');
                })
                ->with('fields', function ($q){
                    $q->where(function($q){
                        $q->where(['layout'=> 'tab', 'enable' => true])->orderBy('order', 'ASC');
                    })->orWhere(function ($query){
                        $query->where(['layout'=> 'field', 'enable' => true])->whereNotIn('type', [3, 14, 15 , 20])
                            ->whereNull('tab')->orderBy('order', 'ASC');
                    });
                    $q->with('type')->with('fields', function ($q){
                        $q->whereNotIn('type', [3, 14, 15 , 20])->with('type');
                    })->with('relations', function ($q){
                        $q->with('object_type');
                        $q->with('relation', function($query){
                            $query->where('enable', true);
                        });
                    });
                    $q->where('enable', true);
                })->where('enable', true)
                ->first()->toArray();

            $object_type['fields'] = array_map(function($item) {
                $item['fields'] = array_merge($item['fields'], array_map(function($relation) {
                    $relation['status'] = 'relation';
                    $relation['selector'] = [
                        'properties' => ['width' => 6, 'label' => __('Object')],
                        'attributes' => [
                            'type' => 'object',
                            'name' =>'object',
                            'required' => false,
                            'multiple' => false ,
                            'data' => (new TheObject('?object_type=' . $relation['relation']['id']))->publicAttributes()
                        ]
                    ];
                    return $relation;
                }, $item['relations']));
                usort($item['fields'],fn($first,$second) => $first['order'] > $second['order']);
                unset($item['relations']);
                return $item;
            }, $object_type['fields']);


            $response = array_merge($response, array_map(function($f){
                $f['status'] = 'field';
                return $f;
            },  $object_type['fields']));

            $response = array_merge($response, array_map(function ($relation){
                $relation['status'] = 'relation';
                $relation['selector'] = [
                    'properties' => ['width' => 6, 'label' => __('Object')],
                    'attributes' => [
                        'type' => 'object',
                        'name' =>'object',
                        'required' => false,
                        'multiple' => false ,
                        'data' => (new TheObject('?object_type=' . $relation['relation']['id']))->publicAttributes()
                    ]
                ];
                return $relation;
            }, $object_type['relations_with']));

            usort($response,fn($first,$second) => $first['order'] > $second['order']);

            $object_type['custom_fields'] = $response;
            unset($object_type['fields']);
            unset($object_type['relations_with']);

            $object_type['custom_fields'] = array_map(function($ot){
                if(isset($ot['status']) && $ot['status'] == 'relation'){
                    $has_relations = ObjectTypeRelation::query()->where([
                        'object_type' => $ot['relation']['id']
                    ])->exists();

                    $has_fields = Field::query()->where(['object_type' => $ot['relation']['id'] ])->exists();

                    $ot['has_relations'] = $has_relations && $has_fields;

                }else if($ot['layout'] === 'tab'){
                    $ot['fields'] = array_map(function($r){
                        if(isset($r['status']) && $r['status'] === 'relation'){
                            $has_relations = ObjectTypeRelation::query()->where([
                                'object_type' => $r['relation']['id']
                            ])->exists();

                            $has_fields = Field::query()->where(['object_type' => $r['relation']['id'] ])->exists();

                            $r['has_relations'] = $has_relations  && $has_fields;
                        }
                        return $r;
                    },$ot['fields'] );
                }
                return $ot;
            }, $object_type['custom_fields']);

            return response()->json($object_type);
        }catch (\Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }
    public function reportFiltered(Request $request){
        $object_type = ObjectType::query()->where('id', $request['object_type'])->first();
        $request['object_type'] = $object_type->slug;
        $apiCtrl = new \App\Http\Controllers\ApiControllers\ObjectController($this->objectRepository, $this->businessRepository, $this->logRepository);
        return $apiCtrl->getObjects($request);
    }
}
