@extends('layouts.app')

@section('content')
    <media-file-component
    :is-image="false"
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
