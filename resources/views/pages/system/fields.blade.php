@extends('layouts.app')
@section('content')

    <crud-component
        title       =   "{{__(strtoupper($object->singular))}}"
        csrf        =   "{{csrf_token()}}"
        fields      =   "{{$object->fields}}"
        icons       =   "{{(getAllIcons())}}"
        object_     =   "{{$object->values}}"
        index       =   "{{route($object->singular .  '.index',   app()->getLocale())}}"
        all         =   "{{route($object->singular .  '.all',     app()->getLocale()) . (isset($object_type) ? "?object_type=" . $object_type : "")}}"
        create      =   "{{route($object->singular .  '.store',   app()->getLocale())}}"
        lngs        =   "{{json_encode(config('app.available_locales'))}}"
        lng         =   "{{app()->getLocale()}}"
        permissions =   "{{json_encode($object->permissions)}}"
        logs        =   "{{route($object->singular .  '.logs',    app()->getLocale())}}"
    />


@endsection
