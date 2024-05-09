<?php

namespace App\Http\Controllers;

use App\Models\BaseModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

class BaseController extends Controller {

    /**
     * @var string
     * SET THE CLASSNAME FOR THE CRUD
     * EXAMPLE: \App\Models\User::class
     */
    public string $object;

    public $icons;

    /**
     * @param Request $request
     * @return View|Factory|Application
     */
    public function index(Request $request): View|Factory|Application {
        $obj = new $this->object();
        $route = route($obj->singular .  '.details',   app()->getLocale()) . $this->getParams($request);
        return view('pages.general.crud', ['details' => $route]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getParams(Request $request, bool $showAsParam = true):string{
        $request = $request->all();
        $params = [];
        foreach ($request as $key =>$value){
            $params[] = "$key=$value";
        }
        if(count($params) > 0) {
            $params = implode('&', $params);
            return ($showAsParam ? "?" : "") ."$params";
        }
        return "";
    }

    /**
     * @return array
     */
    public function getCustomFieldsRelations(string $parameters) : array{
        return [];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse {
        $obj = $this->getObject($request);
        return response()->json([
            'rating_types_url' => route('comment.rating_types', app()->getLocale()),
            'comments' => route('comment.all', app()->getLocale()),
            'object' => $this->object,
            'title' => __(strtoupper($obj->singular)),
            'csrf' => csrf_token(),
            'fields' => $obj->getFields(true),
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

    public function getObject(Request $request){
        return new $this->object($this->getParams($request, false));
    }


    /**
     * @param bool $show
     * @return void
     */
    public function setIcons(bool $show = true) : void
    {
        $this->icons = $show? getAllIcons() : [];
    }



    /**
     * @param $model
     * @param $status
     * @return void
     */
    public function saveManipulation($model, $status = 'created'){
        $model->manipulated_by()->attach(auth()->user()->getAuthIdentifier(), ['model_type' => get_class($model), 'type' => $status]);
    }

}
