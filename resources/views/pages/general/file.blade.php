@extends('layouts.app')
@section('content')
    <language-selector-component
        lngs="{{json_encode(config('app.available_locales'))}}"
        lng="{{app()->getLocale()}}"
        title = "{{__('Files')}}">
    </language-selector-component>
    <file-component
        post = "{{route('file.store', app()->getLocale())}}"
        csrf =   "{{csrf_token()}}"
        url =  "{{route('file.all', app()->getLocale())}}"
        :multiple = "true"
        :sorts = "{{$sorts}}"
        :quantity = "21"
        :selectable = "false"
        :itemsSelected = "null"
    />
@endsection
