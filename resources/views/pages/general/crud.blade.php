@extends('layouts.app')

@section('content')
    <div>
        <global-view details="{{$details}}" is-object="{{!empty($isObject)? $isObject : false}}" ></global-view>
    </div>
@endsection
