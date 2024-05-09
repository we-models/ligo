<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\Field;
use App\Models\ImageFile;
use App\Models\ObjectType;
use App\Models\ObjectTypeRelation;
use App\Models\TheObject;
use App\Models\User;
use App\Repositories\LogRepository;
use App\Repositories\ObjectRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation;
use Exception;
use Throwable;

class ObjectController extends BaseController implements MainControllerInterface
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
     * @var string
     */
    public string $object = TheObject::class;

    /**
     * @param ObjectRepository $objectRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ObjectRepository $objectRepo, LogRepository $logRepo)
    {
        $this->objectRepository = $objectRepo;
        $this->logRepository = $logRepo;
        $this->setIcons(false);
    }

    public function getCustomFieldsRelations(string $parameters, int $object = 0):array{
        return getCustomFieldsRelations($parameters, $this, $object, true);
    }

      /**
     * @param Request $request
     * @return View|Factory|Application
     */
    public function index(Request $request): View|Factory|Application
    {
        $obj = new $this->object();
        $objectType = ObjectType::find($request->all()['object_type']);
        if (! isset($objectType)) {
            abort(404);
        }
        $route = route($obj->singular.'.details', app()->getLocale()).$this->getParams($request);

        return view('pages.general.crud', ['details' => $route, 'isObject' => true]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function details(Request $request): JsonResponse {

        $objectType = ObjectType::select('name','show_description','show_image','editable_name', 'slug')->find($request->all()['object_type']);
        if (! isset($objectType))
            throw new Exception(__('object type doesnt exist'));

        $show_description = $objectType->show_description;
        $show_image = $objectType->show_image;

        $keysToExclude = [];
        if (!$show_description)
            $keysToExclude[] = 'description';

        if (!$show_image)
            $keysToExclude[] = 'images';

        $obj = $this->getObject($request);


        /* Logic to add the dynamic title */
        $title = $objectType->name;

        $fields = $obj->getFields(true,$keysToExclude);

        return response()->json([
            'object' => $this->object,
            'title' => $title,
            'csrf' => csrf_token(),
            'fields' => $fields,
            'icons' => $this->icons,
            'values' => $obj->newObject($this->getParams($request, false)),
            'index' => route($obj->singular .  '.index',   app()->getLocale()),
            'all' => route($obj->singular .  '.all',     app()->getLocale()) . $this->getParams($request, true),
            'create' => route($obj->singular .  '.store',   app()->getLocale()),
            'languages' => config('app.available_locales'),
            'language' => app()->getLocale(),
            'permissions' => $obj->getPermissionsForModel(),
            'logs' => route($obj->singular .  '.logs',    app()->getLocale()) . $this->getParams($request, true),
            'custom_fields' => $this->getCustomFieldsRelations($this->getParams($request, false))
        ]);
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
        $objects = $objects->sortable();
        if(isset($request['all_item']))  return response()->json($objects->get());
        return  $this->objectRepository->getResponse($objects, $rq);
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {
        try {
            set_time_limit(180);

            $request['visible'] = $request['visible'] == 'on';

            if (! isset($request['object_type'])) {
                throw new Exception(__('object type doesnt exist'));
            }

            $objectType = ObjectType::select('id', 'prefix', 'editable_name', 'autogenerated_name')->find($request['object_type']);

            /* If the name is not editable, I generate a dynamic name.. */
            if (isset($objectType) && $objectType->editable_name === 0) {
                $name = TheObject::generateDynamicName($objectType->id, $objectType->prefix);
                if ($objectType->autogenerated_name === 0) {
                    $name = TheObject::newId($request['object_type']);
                }
                $request['name'] = $name;
            }

            $request['internal_id'] = TheObject::newId($request['object_type']);

            $input = $request->all();

            DB::beginTransaction();

            $object = $this->objectRepository->create($input);

            $image = [];
            if (isset($input['images'])) {
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }

            $object->images()->syncWithPivotValues($image, ['model_type' => TheObject::class]);

            foreach ($input as $key => $field) {
                if (str_contains($key, '$$lat') || str_contains($key, '$$long')) {

                    if (str_contains($key, '$$lat')) {
                        $key = str_replace('$$lat_', '', $key);
                    }
                    if (str_contains($key, '$$long')) {
                        $key = str_replace('$$long_', '', $key);
                    }
                }
                $field_db = Field::query()->where(['slug' => $key, 'enable' => true])->first();
                if (! empty($field_db)) {
                    $object->field_value()->attach($field_db->id, ['value' => $field]);
                }

                $relation_db = ObjectTypeRelation::query()->where(['slug' => $key, 'enable' => true])->first();
                if (! empty($relation_db)) {
                    $vl = is_array($field) ? array_unique($field) : $field;

                    // Define a map of relationship types to corresponding models
                    $relationModelMap = [
                        'user' => User::class,
                        'object' => TheObject::class,
                    ];
                    // Define the default model in case the relationship type is not present
                    $defaultModel = TheObject::class;

                    // Determine the corresponding model based on the type of relationship
                    $modelType = $relationModelMap[$relation_db->type_relationship] ?? $defaultModel;

                    // Attach the related object to the many-to-many relationship using the given model
                    $object->relation_value()->attach($vl, ['model_type' => $modelType, 'relation_object' => $relation_db->id]);
                }
            }

            $object->save();
            DB::commit();



            $this->saveManipulation($object);

            if (isset($objectType) && $objectType->autogenerated_name === 1) {
                $name = TheObject::generateDynamicName($objectType->id, $objectType->prefix);
                $object['name'] = $name;
            }

            if (isset($objectType) && $objectType->autogenerated_name === 0 && $objectType->editable_name === 0) {
                $name = TheObject::newId($objectType->id);
                $object['name'] = $name;
            }

            return response([__('Success'), 'data' => $object], 200);
        } catch (Throwable $e) {
            DB::rollBack();

            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws Exception
     */
    public function show(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $object = $this->objectRepository->find($id);
        if (empty($object)) return response(__('Not found'), 404);

        $objectType =  $object->object_type()->select('name','show_description','show_image')->first();

        $show_description = $objectType->show_description;
        $show_image = $objectType->show_image;

        $keysToExclude = [];
        if (!$show_description)
            $keysToExclude[] = 'description';

        if (!$show_image)
            $keysToExclude[] = 'images';

        $fields = $this->objectRepository->makeModel()->getFields(false,$keysToExclude);

        return response()->json([
            'object' => $this->objectRepository->makeModel()->with($this->objectRepository->includes)->find($id)->toArray(),
            'fields' => $fields,
            'icons' => "[]",
            'csrf' => csrf_token(),
            'title'=> __($objectType->name),
            'custom_fields' => $this->getCustomFieldsRelations("object_type=" . $object->object_type, $id) ,
            'url' => '#'
        ]);
    }

    /**
     * @throws Exception
     */
    public function edit(string $lang, int $id): Response|JsonResponse|Application|ResponseFactory
    {
        $object = $this->objectRepository->find($id);
        if (empty($object)) return response(__('Not found'), 404);

        $objectType =  $object->object_type()->select('name','show_description','show_image','editable_name')->first();

        $show_description = $objectType->show_description;
        $show_image = $objectType->show_image;

        $keysToExclude = [];
        if (!$show_description)
            $keysToExclude[] = 'description';

        if (!$show_image)
            $keysToExclude[] = 'images';


        $fields = $this->objectRepository->makeModel()->getFields(false,$keysToExclude);

        return response()->json([
            'object' => $this->objectRepository->makeModel()->with($this->objectRepository->includes)->find($id)->toArray(),
            'fields' => $fields,
            'icons' => "[]",
            'csrf' => csrf_token(),
            'title'=> __($objectType->name),
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
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {
        set_time_limit(180);

        $request['visible'] = $request['visible'] == 'on';

        $input = $request->all();
        try {
            DB::beginTransaction();
            $object = $this->objectRepository->makeModel()->where('id', $id)->first();

            if($object == null) throw new Exception(__('The user can not update this item'));
            if($request['parent'] == $id)  throw new Exception(__('Can not use the same object as parent'));

            $object->update($input);

            $image = [];
            if(isset($input['images'])){
                $image = $input['images'];
                $image = ImageFile::query()->whereIn('id', [$image])
                    ->where('user', auth()->user()->getAuthIdentifier())->pluck('id')->toArray();
                unset($input['images']);
            }
            $object->images()->syncWithPivotValues($image, ['model_type' => TheObject::class]);

            $delete = true;

            DB::table('object_relations')->where(['object' => $id])->delete();

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

                    // DB::table('object_relations')->where(['relation_object'=> $relation_db->id, 'object' => $id])->delete();


                    // Define a map of relationship types to corresponding models
                    $relationModelMap = [
                        'user' => User::class,
                        'object' => TheObject::class,
                    ];
                    // Define the default model in case the relationship type is not present
                    $defaultModel = TheObject::class;

                    // Determine the corresponding model based on the type of relationship
                    $modelType = $relationModelMap[$relation_db->type_relationship] ?? $defaultModel;

                    // Attach the related object to the many-to-many relationship using the given model
                    $object->relation_value()->attach($field, ['model_type' => $modelType, 'relation_object' => $relation_db->id]);

                    // $object->relation_value()->attach($field, ['relation_object' => $relation_db->id]);
                }
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
        $object = $this->objectRepository->makeModel()->where('id', $id)->first();
        try {
            if($object == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($object, 'deleted');
            $object->field_value()->detach();
            $object->relation_value()->detach();
            $object->images()->detach();
            $object->delete();
            DB::commit();
            return response()->json(['delete' => 'success']);
        }catch (Throwable $e){
            DB::rollBack();
            return response($e->getMessage(), 403);
        }
    }


    public function getNew($object_type){
        try{

            $objectType = ObjectType::query()->where('slug', $object_type)->select('id')->first();

            $newObject = new TheObject();
            $newObject = $newObject->newObject('object_type='. $objectType->id);
            $newObject['custom_fields'] = getCustomFieldsRelations("object_type=" . $objectType->id, $this,  0, false, true);
            $newObject['has_custom_fields'] = count($newObject['custom_fields']) > 0;
            return $newObject;

        }catch (\Throwable $error){
            return [];
        }
    }


    public function logs(Request $request, string $lang): Response|JsonResponse
    {
        return getAllModelLogs($request,TheObject::class, $this->logRepository, function($logs) use ($request){
            return $logs->where('properties', 'like', '%' .'"object_type":'.$request['object_type']. ',%');
        });
    }
}
