<?php

namespace App\Http\Controllers;

use App\Interfaces\MainControllerInterface;
use App\Models\ObjectTypeRelation;
use App\Repositories\LogRepository;
use App\Repositories\ObjectTypeRelationRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation;
use Exception;
use Throwable;

class ObjectTypeRelationController extends BaseController implements MainControllerInterface
{


    /**
     * @var ObjectTypeRelationRepository
     */
    private ObjectTypeRelationRepository $objectTypeRelationRepository;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var string
     */
    public string $object = ObjectTypeRelation::class;

    /**
     * @param ObjectTypeRelationRepository $objectTypeRelationRepo
     * @param LogRepository $logRepo
     */
    public function __construct(ObjectTypeRelationRepository $objectTypeRelationRepo, LogRepository $logRepo)
    {
        $this->objectTypeRelationRepository = $objectTypeRelationRepo;
        $this->logRepository = $logRepo;
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request): Response|JsonResponse  {
        $rq = getRequestParams($request);
        $object_type_relations = $this->objectTypeRelationRepository->search($rq->search)->sortable();
        return  $this->objectTypeRelationRepository->getResponse($object_type_relations, $rq);
    }

    public function store(Request $request): Response|Foundation\Application|ResponseFactory {

        try {
            $request->validate([
                'slug' => 'unique:App\Models\ObjectTypeRelation,slug',
                'type_relationship' => 'required',
                'object_type' => 'required',
                'relation' => $request->type_relationship === 'object' ? 'required' : '',
                'roles' => $request->type_relationship === 'user' ? 'required' : ''
            ],[
                'slug.unique' => __('The [slug] field must be unique, a record with this slug already exists'),
                'type_relationship.required' => __('The [type_relationship] field is required'),
                'object_type.required' => __('The [object_type] field is required'),
                'relation.required' => __('The [relation] field is required'),
                'roles.required' => __('The [roles] field is required')
            ]);

            $request['enable'] = $request['enable'] == 'on';
            $request['editable'] = $request['editable'] == 'on';
            $request['required'] = $request['required'] == 'on';

            $input = $request->all();

            DB::beginTransaction();

            $object_type_relation = $this->objectTypeRelationRepository->create($input);

            if ($request->has('roles') && is_array($request['roles'])){

                foreach($input['roles'] as $role){
                     DB::table('model_has_roles')->insert([
                    'role_id' => $role,
                    'model_type' => ObjectTypeRelation::class,
                    'model_id' =>$object_type_relation->id
                    ]);
                }
            }
            $this->saveManipulation($object_type_relation);
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
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
    public function show(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->objectTypeRelationRepository->makeModel()->getFields();
        $object_type_relation = $this->objectTypeRelationRepository->find($id);
        if (empty($object_type_relation)) return response(__('Not found'), 404);
        return response([
            'object' => $this->objectTypeRelationRepository->makeModel()->with($this->objectTypeRelationRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> 'object_type_relation',
            'url' => '#'
        ]);
    }

    /**
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|JsonResponse|Response
     * @throws Exception
     */
    public function edit(String $lang, int $id): Response|JsonResponse|Foundation\Application|ResponseFactory {
        $fields = $this->objectTypeRelationRepository->makeModel()->getFields();
        $object_type_relation = $this->objectTypeRelationRepository->find($id);
        if (empty($object_type_relation)) return response(__('Not found'), 404);
        return response([
            'object' => $this->objectTypeRelationRepository->makeModel()->with($this->objectTypeRelationRepository->includes)->find($id),
            'fields' => $fields,
            'icons' => getAllIcons(),
            'csrf' => csrf_token(),
            'title'=> 'object_type_relation',
            'url' => route('object_type_relation.update', ['locale' => $lang, 'object_type_relation' => $id])
        ]);
    }

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Foundation\Application|ResponseFactory|Response
     * @throws Exception
     */
    public function update(Request $request, String $lang, int $id): Response|Foundation\Application|ResponseFactory {

        try {
            $request->validate([
                'type_relationship' => 'required',
                'object_type' => 'required',
                'relation' => $request->type_relationship === 'object' ? 'required' : '',
                'roles' => $request->type_relationship === 'user' ? 'required' : ''
            ],[
                'type_relationship.required' => __('The [type_relationship] field is required'),
                'object_type.required' => __('The [object_type] field is required'),
                'relation.required' => __('The [relation] field is required'),
                'roles.required' => __('The [roles] field is required')
            ]);

            $request['enable'] = $request['enable'] == 'on';
            $request['editable'] = $request['editable'] == 'on';
            $request['required'] = $request['required'] == 'on';

            $input = $request->all();
            $object_type_relation = $this->objectTypeRelationRepository->makeModel()->where('id', $id)->first();

            if($object_type_relation == null){
                throw new Exception(__('The user can not update this item'));
            }
            DB::beginTransaction();
            $object_type_relation->update($input);

            if ($request->has('roles') && is_array($request['roles'])){
                $mapIdsRoles = [];
                foreach ($input['roles'] as $value) {
                    $mapIdsRoles[$value] = ['model_type'=> ObjectTypeRelation::class ];
                }
                /*
                * https://laravel.com/docs/11.x/eloquent-relationships#syncing-associations
                * Saves only the ids that are in the $mapIdsRoles array,
                  if there is an id that is in the database but not in the $mapIdsRoles array it will be eliminated from the db.
                */
                $object_type_relation->roles()->sync($mapIdsRoles);
            }
            $this->saveManipulation($object_type_relation, 'updated');
            DB::commit();
            return response(__('Success'), 200);
        }catch (Throwable $e){
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
        $object_type_relation = $this->objectTypeRelationRepository->makeModel()->where('id', $id)->first();
        try {
            if($object_type_relation == null){
                throw new Exception(__('The user can not delete this item'));
            }
            DB::beginTransaction();
            $this->saveManipulation($object_type_relation, 'deleted');
            $object_type_relation->delete();
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
    public function logs(Request $request, string $lang): Response|JsonResponse {
        return getAllModelLogs($request,ObjectTypeRelation::class, $this->logRepository);
    }
}
