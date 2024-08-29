<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Repositories\LogRepository;
use App\Repositories\ObjectRepository;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ObjectController extends BaseController
{
    /**
     * @var ObjectRepository
     */
    public ObjectRepository $objectRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @param ObjectRepository $objectRepo
     */
    public function __construct(ObjectRepository $objectRepo, LogRepository $logRepo)
    {
        $this->objectRepository = $objectRepo;
        $this->logRepository = $logRepo;
        define('RV', 'relation_value');
        define('FV', 'field_value');
        define('OB_TYPE', 'object_type');
        define('OTR', 'object_relations.relation');
    }

    public function getObjects(Request $request){
        $input = $request->all();
        $conditions = $input['condition'] ?? '';
        $rq = getRequestParams($request);
        $rq->object_type =  explode(',', $request['object_type']);
        $objectType = ObjectType::query()->whereIn('slug', $rq->object_type)->get()->toArray();
        if(empty($objectType)) {
            throw new Exception(__("Object type doesn't exists"));
        }
        $objects = $this->objectRepository->search($rq->search);

        if(isset($input['enabled'])){
            $enable = (bool) $input['enabled'] ? 1 : 0;
            $objects = $objects->where('visible', $enable);
        }


        if(isset($input['owners']) && $input['owners'] != 0){
            $objects->whereIn('owner', explode(',', $input['owners']));
        }
        if(isset($input['user_owner']) && $input['user_owner'] != 0){
            $objects->where('owner', auth()->user()->getAuthIdentifier());
        }
        if(isset($input['relation']) &&  $input['relation'] == 'null'){
            unset($input['relation']);
        }
        // if($objectType['type'] == 'post' && isset($input['relation'])){
        //     $rl = ObjectTypeRelation::query()->where('slug', $input['relation'] )->first();
        //     if(empty($rl)) throw new \Exception(__("Relation not found") . " " . $input['relation'] );
        // }

        $can_get = auth()->user()->roles()->whereHas('permissions', function ($query) {
            $query->where('name', 'object.all');
        })->exists();
        //$can_get = auth()->user()->hasPermissionTo('object.all');
        $objects = $objects->where(function($q) use($objectType, $input, $can_get){
            foreach ($objectType as $ot){
                $q->orWhere(function($q) use ($ot, $input, $can_get){
                    $q->where('object_type', $ot['id']);
                    if($ot['public'] == 0  || !$ot['public']){
                        if( !$can_get || ( isset($input['owner']) && $input['owner'] !== $ot['access_code'])){
                            $q->where('owner', auth()->user()->getAuthIdentifier());
                        }
                    }
                });
            }
        });


        if($conditions != ''){
            $conditions = json_decode($conditions);
            $conditions->expressions = array_map(function($exp){
                $exp->left = substr($exp->left, 1);
                if(is_array($exp->right)){
                    $exp->right = implode(',', $exp->right);
                }
                return $exp;
            },$conditions->expressions);
            $objects = $this->filterObjects($objects, $conditions);
        }

         $objects = $objects->sortable();
         if(isset($input['all_object'])){
            $objects = $objects->get()->toArray();
            return $objects;
         }else{
            $objects = $objects->paginate($rq->paginate)->toArray();
         }

        if(isset($input['get_fields'])){
           if($input['get_fields'] == 0) return $objects;
        }

        $objects['data'] = array_map(fn ($object) => array_filter($object, fn ($v) => $v !== null), $objects['data']);

        $objects['data'] = array_map(function($object){
            $object['custom_fields'] = getCustomFieldsRelations(
                "object_type=" . $object['object_type']['id'],
                $this,
                $object['id'],
                false,
                true
            );
            $object['has_custom_fields'] = count($object['custom_fields']) > 0;
            return $object;
        }, $objects['data']);

        $objects['data'] = simplifyData($objects['data']);

        return $objects;
    }


    public function all(Request $request){

        try{
            return response()->json($this->getObjects($request));

        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }


    /**
     * @throws Exception
     */
    public function filterObjects($q, $expression ){

        if(isset($expression->expressions)){
            $q = $q->where(function($q) use($expression){

                if($expression->type ==  'AND'){
                    foreach ($expression->expressions as $exp){
                        $exp->layout = $exp->layout ?? 'relation';
                        $left = explode('.', $exp->left);
                        if(count($left) > 1){
                            $exp->{'build'} = true;
                            if(isset($exp->expressions)){
                                $q = $q->where($this->reFilter($exp));
                            }elseif ($exp->layout !== 'base'){
                                $q = $q->whereHas($exp->layout == 'relation'? RV : FV, $this->reFilter($exp));
                            }
                        }else{
                            if(isset($exp->expressions)){
                                $q = $q->where($this->reFilter($exp));
                            }elseif ($exp->layout !== 'base'){
                                $q = $q->whereHas($exp->layout == 'relation'? RV : FV, $this->reFilter($exp));
                            }else{
                                if(in_array($exp->left, ['name', 'description', 'excerpt'])){
                                    $q= $q->where($exp->left, $exp->type, $exp->right);
                                }elseif ($exp->left == 'visible'){
                                    $q= $q->where($exp->left, '' . $exp->right == '1' );
                                }else{
                                    $q = $q->where($exp->left, $exp->right);
                                }
                            }
                        }
                    }
                }
                if($expression->type ==  'OR'){
                    foreach ($expression->expressions as $exp){
                        $exp->layout = $exp->layout ?? 'relation';
                        $left = explode('.', $exp->left);

                        if(count($left) > 1){
                            $exp->{'build'} = true;
                            $q->orWhere($this->reFilter($exp));
                        }else{
                            if(isset($exp->expressions)){
                                $q->orWhere($this->reFilter($exp));
                            }elseif ($exp->layout !== 'base'){
                                $q->orWhereHas($exp->layout == 'relation'? RV : FV, $this->reFilter($exp));
                            }else{
                                if(in_array($exp->left, ['name', 'description', 'excerpt'])){
                                    $q= $q->orWhere($exp->left, $exp->type, $exp->right);
                                }elseif ($exp->left == 'visible'){
                                    $q= $q->orWhere($exp->left, '' . $exp->right == '1' );
                                }else{
                                    $q = $q->orWhere($exp->left, $exp->right);
                                }
                            }
                        }
                    }
                }
                if($expression->type ==  'NOT'){
                    foreach ($expression->expressions as $exp){
                        $exp->layout = $exp->layout ?? 'relation';

                        $left = explode('.', $exp->left);

                        if(count($left) > 1){
                            $exp->{'build'} = true;
                            $q->whereNot($this->reFilter($exp));
                        }else{
                            if(isset($exp->expressions)){
                                $q->whereNot($this->reFilter($exp));
                            }elseif ($exp->layout !== 'base'){
                                $q->whereDoesntHave($exp->layout == 'relation'? RV : FV, $this->reFilter($exp));
                            }else{
                                if(in_array($exp->left, ['name', 'description', 'excerpt'])){
                                    $q= $q->whereNot($exp->left, $exp->type, $exp->right);
                                }elseif ($exp->left == 'visible'){
                                    $q= $q->whereNot($exp->left, '' . $exp->right == '1' );
                                }else{
                                    $q = $q->whereNot($exp->left, $exp->right);
                                }
                            }
                        }
                    }
                }
                return $q;
            });
        }else{

            $left = explode('.', $expression->left);

            if(count($left) > 1 && isset($expression->build)){
                $left = array_shift($lefts);
                $expression->left = implode('.', $lefts);
                if($expression->layout == 'relation'){
                    $rl = ObjectTypeRelation::query()->where('slug', $left)->first();
                    if(empty($rl)){
                        throw new \Exception(__("Relation not found") . " " . $left);
                    }

                    $q = $q->where(OB_TYPE, $rl->relation);

                    $q = $q->whereHas(RV, $this->reFilter($expression));
                }
            }else{
                if($expression->layout == 'relation'){
                    $rl = ObjectTypeRelation::query()->where('slug', $expression->left)->first();
                    if(empty($rl)){
                        throw new \Exception(__("Relation not found") . " " . $expression->left);
                    }
                    $right = explode(',', $expression->right);
                    $q = $q->where(OB_TYPE, $rl->relation);

                    if($expression->type == '='){
                        $q = count($right) > 1 ? $q->whereIn(OTR, $right) : $q->where(OTR, $expression->right);
                    }
                    if($expression->type == '<>'){
                        $q = count($right) > 1 ?  $q->whereNotIn(OTR, $right) : $q->whereNot(OTR, $expression->right);
                    }
                }

                if($expression->layout == 'field'){
                    $field = Field::query()->where('slug', $expression->left)->with('type')->first();
                    if(empty($field)){
                        throw new \Exception(__("Field not found") . " " . $expression->left);
                    }
                    $q = $q->where('object_field_value.field', $field->id)
                        ->where(function($q) use ($expression, $field){
                            $toFill = match ($field->type()->first()->name) {
                                'Integer' => 'SIGNED',
                                'Decimal' => 'FLOAT',
                                'Double' => 'DOUBLE',
                                'Date' => 'DATE',
                                'Time' => 'TIME',
                                'DateTime' => 'DATETIME',
                                'Boolean' => 'UNSIGNED',
                                default => "",
                            };
                            if($toFill == ""){
                                return $q->where('object_field_value.value', $expression->type,  $expression->right );
                            }else{
                                return $q->whereRaw("CAST(object_field_value.value AS {$toFill}) {$expression->type} {$expression->right}");
                            }
                        });
                }
            }
        }

        return $q;
    }

    function reFilter(mixed $exp): \Closure  {
        return fn ($q) => $this->filterObjects($q, $exp);
    }


    public function store(Request $request){
        set_time_limit(180);

        $data = $request->all();
        try {
            DB::beginTransaction();
            $object = $this->saveObject($data);
            $this->saveManipulation($object);

            return response(__('Success'), 200);
        }catch (\Throwable $error){
            DB::rollBack();
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    /**
     * @throws GuzzleException
     */
    function saveObject($data){
        $owner = auth()->user()->getAuthIdentifier();

        if(isset($data['pay_method'])){
            $data = paymentezMethod($data);
        }

        if($data['id'] == 0){
            //Create a base object
            $object = $this->objectRepository->create([
                'name' => $data['name'] ?? "",
                'description' => $data['description'] ?? "",
                'excerpt' => $data['excerpt'] ?? "",
                'object_type' => $data['object_type']['id'],
                'visible' => true,
                'parent' => isset($data['parent']) ? $data['parent']['id'] : null,
                'owner' => $owner,
                'wp_id' => null
            ]);
        }else{
            //On Update case only get the object
            $object = $this->objectRepository->find($data['id']);
        }

        //Set images to object
        if(isset($data['images'])){
            $images = array_map(function ($img){
                return $img['id'];
            }, $data['images']);
            $object->images()->syncWithPivotValues($images, ['model_type' => TheObject::class]);
        }

        foreach ($data['custom_fields'] as $cf){
            if(isset($cf['layout']) && $cf['layout'] == 'tab'){
                array_map(fn($field_relation) => $this->setCustomFieldsRelations($object, $field_relation), $cf['fields']);
            }else{
                $this->setCustomFieldsRelations($object, $cf);
            }
        }
        return $object;
    }

    public function setCustomFieldsRelations($object, $field_relation){
        //If object has fields. Then fill them
        if($field_relation['status'] == 'field'){

            //First remove the old
            DB::table('object_field_value')->where(['object' => $object->id, 'field' => $field_relation['id']])->delete();

            if($field_relation['type']['name'] == 'Map'){
                $object->field_value()->attach($field_relation['id'], [
                    ['value' => $field_relation['value']['latitude']],
                    ['value' => $field_relation['value']['longitude']]
                ]);

            }else{
                //Then add the new value
                $object->field_value()->attach($field_relation['id'], ['value' => $field_relation['value']['value']]);
            }
        }
        if($field_relation['status'] == 'relation'){

            //First remove all relations to object
            DB::table('object_relations')
                ->where(['relation_object'=>$field_relation['relation']['id'], 'object' => $object->id])
                ->delete();

            if($field_relation['type'] == 'unique'){
                $relations = $field_relation['entity']['id'];
                if($relations == 0 && in_array($field_relation['filling_method'], ['creation', 'all'])){
                    //If object relation not exists create;
                    $obj = $this->saveObject($field_relation['entity']);
                    $relations = [$obj->id];
                    $this->saveManipulation($obj);

                }
            }else{
                $relations  = array_map(function($rl) use($field_relation){
                    if($rl['id'] == 0 && in_array($field_relation['filling_method'], ['creation', 'all'])){
                        //If object relation not exists create
                        $rl = $this->saveObject($rl);
                        $this->saveManipulation($rl);
                        $rl = $rl->toArray();
                    }
                    return $rl;
                }, $field_relation['entity']);
                $relations = array_map(fn($rl) => $rl['id'] ,$relations);
            }

            //Verify not include elements if not exists
            $relations = array_filter($relations, fn($value) => $value !== 0);

            //Assign the new values
            $object->relation_value()
                ->attach($relations, [
                    'relation_object' => $field_relation['relation']['id']
                ]);

        }
    }

    public function setNewObject($obj){
       $wp_obj = parseWpData($obj->id);
       $wp_obj = syncWp($wp_obj, 'save-object');
       $wp_obj = json_decode($wp_obj);
       if($wp_obj != null && (isset($wp_obj->Error) || isset($wp_obj->error))){
           throw new Exception($wp_obj->Error?? $wp_obj->error);
       }
       if($wp_obj != null && !isset($wp_obj->Error)){
           $obj->wp_id = $wp_obj->post_id;
           $obj->save();
       }
        return $obj;
    }

    public function getTabs(Request $request): \Illuminate\Http\JsonResponse
    {
        try{
            $rq = getRequestParams($request);

            $rq->object_type =  $request['object_type'];

            $objectType = ObjectType::query()->where('slug', $rq->object_type)->select('id')->first();
            if(empty($objectType)) new Exception(__("Not exists"));

            $tabs = Field::query()
                ->where([
                    'layout' => 'tab',
                    'object_type' => $objectType->id
                ])
                ->orderBy('order', 'ASC')
                ->paginate($rq->paginate)->toArray();

            return response()->json($tabs);
        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    public function getNew(Request $request){
        try{
            $rq = getRequestParams($request);

            $rq->object_type =  $request['object_type'];
            $objectType = ObjectType::query()->where('slug', $rq->object_type)->select('id')->first();

            $newObject = new TheObject();
            $newObject = $newObject->newObject('object_type='. $objectType->id);
            $newObject['custom_fields'] = getCustomFieldsRelations("object_type=" . $objectType->id, $this,  0, false, true);

            return response()->json($newObject);
        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    public function show(Request $request, $lang, $id){
        try{
            $object = TheObject::query()->where('id', $id)->with($this->objectRepository->includes)->first();

            if(!$object->object_type()->first()->public && $object->owner != auth()->user()->getAuthIdentifier()) {
                throw new Exception("This user can not get the item");
            }
            $object = $object->toArray();
            $object =  array_filter($object, function ($value) {
                return $value !== null;
            });

            $object['custom_fields'] = getCustomFieldsRelations("object_type=" . $object['object_type']['id'], $this,  $object['id'], false, true);
            return response()->json($object);
        }catch (\Throwable $error){
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    public function getAvailableTerms(Request $request){

        $input = $request->all();

        $object_type = ObjectType::query()->where('slug', $input['object_type'] )->first();

        $existence = array_map(function($rl){
            return ObjectTypeRelation::query()->where('slug', $rl)->select('id','object_type', 'relation')->first()->toArray();
        }, explode('.', $input['existence']) );

        $existence = array_map(function($rl){
            return $rl['object_type'];
        }, $existence);

        $existences = array_reverse($existence);


        $objects = TheObject::query()->where('object_type', $object_type->id);

        $objects = $this->buildWhereHasQuery($objects, $existences);
        //$objects = $this->buildWithQuery($objects, $existences);


        $objects = $objects->get()->toArray();
        return response()->json($objects);
    }

    function buildWhereHasQuery($objects, $existences, $index = 0)
    {
        if ($index === count($existences)) {
            return $objects;
        }

        return $objects->whereHas('value_for_relation', function ($q) use ($existences, $index) {
            $q = $q->where('object_type', $existences[$index]);
            $q = $this->buildWhereHasQuery($q, $existences, $index + 1);
        });
    }

    function buildWithQuery($objects, $existences, $index = 0)
    {
        if ($index === count($existences)) {
            return $objects;
        }

        return $objects->whereHas('value_for_relation')->with('value_for_relation', function ($q) use ($existences, $index) {
            $q = $q->where('object_type', $existences[$index]);
            $q = $this->buildWithQuery($q, $existences, $index + 1);
        });
    }

    function preview(Request $request, $lang, $id){
        $object = $this->objectRepository->makeModel()->where('id', $id)->whereHas('object_type', function($q){
            $q->where('public', true);
        })->with('images')->first();
        if($object == null) return response()->json(['error' => 'Not found'], 404);
        return response()->json($object);
    }

    /**
     * @throws Exception
     */
    function talentDescription(Request $request, $lang){
        $input = $request->all();
        $element = json_decode($input['element']);

        $age_from = $this->findObjectByCondition($element, 'glo_protalca_d_ed', null);
        $age_to = $this->findObjectByCondition($element, 'glo_protalca_d_eh', null);

        $age_from = (new DateTime($age_from))->diff( new DateTime());
        $age_to = (new DateTime($age_to))->diff( new DateTime());

        $response = "";
        $response = $response . $this->findObjectByCondition($element, 'sexo_genero', null);
        $response = $response . "\n";
        $response = $response . __('from') . " " . $age_from->y . " " . __('to') . " " . $age_to->y . " " . __('years');
        $response = $response . "\n";
        $response = $response . __('nationality'). ": " . $this->findObjectByCondition($element, 'pais', null);

        $stature_from = $this->findObjectByCondition($element, 'glo_protalca_d_est', null);
        $stature_to = $this->findObjectByCondition($element, 'glo_protalca_d_esta', null);
        $stature_measure = $this->findObjectByCondition($element, 'medida_estatura', null);

        $response = $response . "\n";
        $response = $response . __('stature') . ": " . $stature_from . " - " . $stature_to . " " . $stature_measure;

        $skills = $this->findObjectByCondition($element, 'pro_newp_tal_habde', null);
        if(!empty($skills)){
            $response = $response . "\n";
            $response = $response . __('skills') . ": " . $this->findObjectByCondition($element, 'pro_newp_tal_habde', null);
        }

        return response()->json(['text' => $response], 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }


    function findObjectByCondition($data, $conditionValue, $parent) {
        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result = $this->findObjectByCondition($value, $conditionValue, $data);
                if(!empty($result)) return $result;
            }else{
                if ($key === 'slug' && $value === $conditionValue) {
                    if (isset($data->value)) return $data->value;
                    if (is_array($parent->entity)) {
                        $ent = array_map(function($e) {
                            return $e->name;
                        }, $parent->entity);

                        if(count($ent) == 1) return $ent[0];
                        if(count($ent) == 2) return implode(" " . __('or') . " ", $ent);
                        $last = array_pop($ent);
                        return implode(', ', $ent) . " ". __('or') . " " . $last;
                    }
                    return $parent->entity->name;
                }
            }

        }
        return null;
    }

    public function ValidateQr(Request $request){
        set_time_limit(180);

        $data = $request->all();
        try {
            DB::beginTransaction();
            $object = $this->objectRepository->find($data['sucursal_id']);
            $codeQr = getFieldValue($object, 'adnewsuc_11');

            if ($codeQr == $data['code']) {
                return response(__('QR validate Success'), 200);
            }

            return response(__('error'), 403);
        }catch (\Throwable $error){
            DB::rollBack();
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    public function getQr(String $lang, int $id){
        set_time_limit(180);

        try {
            DB::beginTransaction();
            $object = $this->objectRepository->find($id);
            $codeQr = getFieldValue($object, 'adnewsuc_11');

            // Generar la imagen QR
            $qrCode = QrCode::format('png')->size(200)->generate($codeQr);

            // Retornar la imagen QR en respuesta para mostrarla o descargarla
            return response($qrCode)->header('Content-Type', 'image/png');

        }catch (\Throwable $error){
            DB::rollBack();
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }
    }

    function paymentezMethodCard(Request $request){

        try {
            DB::beginTransaction();
            $dataCard = $request->all();
            $owner = auth()->user()->getAuthIdentifier();

            $objectType = ObjectType::query()->where('slug', 'pay_card_01')->first();

            $cardID = null;

            if(isset($dataCard['id'])){
                $cardID = $dataCard['id'];
                unset($dataCard['id']);
            }


            if($cardID == null){
                $data = addPaymentez($dataCard,$owner);
                $object = $this->objectRepository->create([
                    'name' => $data['name'] ?? "",
                    'description' => $data['description'] ?? "",
                    'excerpt' => $data['excerpt'] ?? "",
                    'object_type' =>  $objectType->id,
                    'internal_id' => TheObject::newId($objectType->id),
                    'visible' => true,
                    'owner' => $owner
                ]);
            }else{
                $object = $this->objectRepository->find($cardID);
                $data = updatePaymentez($object,$dataCard,$owner);
                $object->name = $data['name'] ?? "";
                $object->save();
            }

            $fields = [
                [
                    'key' => 'card_bin',
                    'value' =>  'bin'
                ],
                [
                    'key' => 'card_status',
                    'value' =>  'status'
                ],
                [
                    'key' => 'card_token',
                    'value' =>  'token'
                ],
                [
                    'key' => 'card_expiry_year',
                    'value' =>  'expiry_year'
                ],
                [
                    'key' => 'card_expiry_month',
                    'value' =>  'expiry_month'
                ],
                [
                    'key' => 'card_message',
                    'value' =>  'message'
                ],
                [
                    'key' => 'card_trans_ref',
                    'value' =>  'transaction_reference'
                ],
                [
                    'key' => 'card_type',
                    'value' =>  'type'
                ],
                [
                    'key' => 'card_number',
                    'value' =>  'number'
                ],
                [
                    'key' => 'card_origin',
                    'value' =>  'origin'
                ]
            ];

            foreach ($fields as $key => $field) {
                $object = detachField($object, $field['key']);
                $object = fillField($object, $field['key'], $data[$field['value']]);
            }

            $objectTypeRegisterUSer = ObjectType::query()->where('slug', 'regusu_cli_01')->first();
            if($objectTypeRegisterUSer == null) throw __('object type not exists');

            $objectUserRegister = TheObject::query()->where([
                'object_type' => $objectTypeRegisterUSer->id,
                'owner' => $owner
            ])->latest()->first();
            if($objectUserRegister == null) throw __('object data user register not exists');

            $relation_db = ObjectTypeRelation::query()->where(['slug'=> 'card_user', 'enable' => true] )->first();
            if(!empty($relation_db) && !empty($objectUserRegister)) {
                $object->relation_value()->detach();
                $object->relation_value()->attach($objectUserRegister->id, ['model_type' => TheObject::class, 'relation_object' => $relation_db->id]);
            }

            DB::commit();

            return $object;
        } catch (\Throwable $error) {
            DB::rollBack();
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }



    }

    function destroy(String $lang, int $id){

        $object = $this->objectRepository->makeModel()->where('id', $id)->first();
        try {
            if($object == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();

            $objectType = ObjectType::query()->where('slug', 'pay_card_01')->first();

            if($object->object_type == $objectType->id){
                $owner = auth()->user()->getAuthIdentifier();
                $data = deletePaymentez($object,$owner);
            }

            $this->saveManipulation($object, 'deleted');
            $object->field_value()->detach();
            $object->relation_value()->detach();
            $object->images()->detach();
            $object->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (Throwable $error){
            DB::rollBack();
            return new JsonResponse([
                'error' => $error->getMessage(),
                'line' => $error->getLine(),
                'file' => $error->getFile()
            ], 403);
        }

    }
}
