@extends('layouts.app')
@section('content')
    <profile-view

    object="{{$object}}"
     fields="{{ json_encode($fields) }}"
      icons="{{$icons}}"
      csrf="{{$csrf}}"
      url_update_profile="{{$urlUpdateProfile}}"
      url_update_password="{{$urlUpdatePassword}}"
      title="{{$title}}"
     ></profile-view>
@endsection
