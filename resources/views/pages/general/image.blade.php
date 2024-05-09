@extends('layouts.app')

@section('content')
    <media-file-component
    :is-image="true"
    post = "{{ route('image.store', app()->getLocale()) }}"
    csrf = "{{ csrf_token() }}"
    url = "{{ route('image.all', app()->getLocale()) }}"
    :multiple="true"
        :sorts="{{ $sorts }}"
        :quantity="18"
        :selectable="false"
        :itemsSelected="null" />
@endsection
