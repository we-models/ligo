<?php

namespace App\Http\Controllers\WpControllers;

use App\Http\Controllers\Controller;
use App\Models\ObjectType;
use App\Models\TheObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectTypeController extends Controller
{
    public function all(Request $request){
        $object_types = ObjectType::query()
            ->with('relations_with', function ($q){
                $q->whereNull('tab')->with('relation')->with('object_type');
            })
            ->with('fields', function ($q){
                $q->where(['layout'=> 'tab', 'enable' => true])->orWhere(function ($query){
                    $query->where(['layout'=> 'field', 'enable' => true])->whereNull('tab');
                });
                $q->with('type')->with('fields.type')->with('relations', function ($q){
                    $q->with('object_type');
                    $q->with('relation');
                });
                $q->where('enable', true);
            })->where('enable', true)
            ->get()->toArray();

        return response()->json($this->getCustomFieldsRelations($object_types));
    }

    public function getCustomFieldsRelations(array $object_types):array{

        foreach ($object_types as $key=> $object_type){
            $response = [];
            $object_type['fields'] = array_map(function($item) use ($object_type){
                $resp = [];
                if($item['layout'] == 'field') return $item;
                $resp = array_map(function($field) use ($object_type) {
                    return $this->formatField($field, $object_type['id']);
                }, $item['fields']);
                $resp = array_merge($resp, array_map(function($relation) use ($object_type) {
                    return $this->formatRelation($relation, $object_type['id']);
                }, $item['relations']));
                unset($item['relations']);
                usort($resp,fn($first,$second) => $first['order'] > $second['order']);
                $item['fields'] = $resp;
                return $item;
            }, $object_type['fields']);

            $response = array_merge($response, array_map(function ($field) use ($object_type){
                return $this->formatField($field, $object_type['id']);
            }, $object_type['fields']));

            $response = array_merge($response, array_map(function ($relation) use ($object_type) {
                return $this->formatRelation($relation, $object_type['id']);
            }, $object_type['relations_with']));

            usort($response,fn($first,$second) => $first['order'] > $second['order']);
            $object_types[$key]['fields'] = $response;
        }
        return $object_types;
    }

    public function formatField($field, $object){
        $field['status'] = 'field';
        $field['value'] = null;
        return $field;
    }

    public function formatRelation($relation, $object){
        $relation['status'] = 'relation';
        $obj = new TheObject('?object_type=' . $relation['relation']['id']);
        $relation['data'] = [
            'type' => 'object',
            'name' => $relation['name'],
            'required' => false,
            'multiple' => $relation['type'] == 'multiple' ,
            'data' => $obj->publicAttributes()
        ];
        if($relation['type'] == 'unique'){
            $relation['values'] = null;
        }else{
            $relation['values'] = [];
        }
        $relation['entity'] = [$relation['slug'] => $relation['values']];
        unset ($relation['values']);
        return $relation;
    }
}
