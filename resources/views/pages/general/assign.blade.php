@extends('layouts.app')
@section('content')

    <assign-component
        rows = '{{$rows}}'
        columns ="{{$columns}}"
        the_key = '{{$key}}'
        csrf="{{csrf_token()}}"
        url = "{{$url}}"
        url_to_save = "{{route('assign.save', app()->getLocale())}}"
        lngs="{{json_encode(config('app.available_locales'))}}"
        lng="{{app()->getLocale()}}"
        unique = "{{$unique??false}}"
        general = {{$general??false}}
    ></assign-component>

@endsection
@include('includes.structure.scriptAssign')
