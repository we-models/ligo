@extends('layouts.app')
@section('content')
    <language-selector-component
        lngs="{{json_encode(config('app.available_locales'))}}"
        lng="{{app()->getLocale()}}"
        title = "{{__('Images')}}">
    </language-selector-component>
    <image-component
        post = "{{route('image.store', app()->getLocale())}}"
        csrf =   "{{csrf_token()}}"
        url =  "{{route('image.all', app()->getLocale())}}"
        :multiple = "true"
        :sorts = "{{$sorts}}"
        :quantity = "18"
        :selectable = "false"
        :itemsSelected = "null"
    />
@endsection
