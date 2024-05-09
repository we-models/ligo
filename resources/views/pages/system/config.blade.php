@extends('layouts.app')
@section('content')

    @php
    $types = \Illuminate\Support\Facades\DB::table('data_type')->get()->toJson();
    @endphp
    <system-config-component
        types = "{{$types}}"
        title = "{{__('System Configurations')}}"
        csrf="{{csrf_token()}}"
        lngs="{{json_encode(config('app.available_locales'))}}"
        lng="{{app()->getLocale()}}"
        all="{{route('system.all', app()->getLocale())}}"
        create="{{route('system.store', app()->getLocale())}}"
        index="{{route('system.index', app()->getLocale())}}"
        store = {{route('system.store', app()->getLocale())}}
    >

    </system-config-component>

@endsection
