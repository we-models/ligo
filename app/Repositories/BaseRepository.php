<?php

namespace App\Repositories;

use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @param Application $app
     *
     * @throws Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable(): array;

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model(): string;


    /**
     * @return array
     */
    abstract public function getReportable(): array;

    /**
     * Make Model instance
     *
     * @throws Exception
     *
     * @return Model
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @return Builder
     */
    public function allQuery(array $search = [], $skip = null,  $limit = null): Builder
    {
        $query = $this->model->newQuery();
        if (count($search)) {
            $query->where(function ($q) use($search){
                foreach($search as $key => $value) {
                    if (in_array($key, $this->getFieldsSearchable())) {
                        $q->orWhere($key, 'like', '%' . $value . '%');
                    }
                }
            });
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }


    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return Builder[]|Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*']): Collection|array
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }


    public function getPDF( $items, $rq): Response
    {
        $items = $items->take($rq->paginate)->offset($rq->offset * $rq->paginate)->get()->toArray();
        $headers = $this->getReportable();
        $pdf = Pdf::loadView('exports.list', ['items' => $items, 'headers' => $headers])
            ->setPaper('a4', sizeof($headers) > 5 ? 'landscape' : 'portrait');
        return $pdf->download(__('List').'.pdf');
    }

    /**
     * @param $items
     * @param $rq
     * @return JsonResponse|Response
     */
    public function getResponse($items, $rq): Response|JsonResponse
    {
        if($rq->pdf) return $this->getPDF($items, $rq);
        return response()->json($items->paginate($rq->paginate));
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input): Model
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function find($id, $columns = ['*']): Model|Collection|Builder|array|null
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return Builder|Builder[]|Collection|Model
     */
    public function update($input, $id): Model|Collection|Builder|array
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id): mixed
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }
}
