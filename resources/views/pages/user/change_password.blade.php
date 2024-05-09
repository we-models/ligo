@extends('layouts.app')
@section('content')
    <language-selector-component
        lngs="{{json_encode(config('app.available_locales'))}}"
        lng="{{app()->getLocale()}}"
        title = "{{__('Change Password')}}">
    </language-selector-component>

    @if(isset($success))
        <div class="alert alert-success" role="alert">
            {{$success}}
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-danger" role="alert">
            {{$error}}
        </div>
    @endif




    <div class="container-center">
        <form action="{{route('user.change_password_save', app()->getLocale())}}" method="POST">
            {{csrf_field()}}
            <div class="form-group">
                <label for="" class="form-label">{{__('Current password')}}</label>
                <input type="password" class="form-control" name="current_password">
            </div>

            <div class="form-group">
                <label for="" class="form-label">{{__('New password')}}</label>
                <input type="password" class="form-control" name="new_password">
            </div>

            <div class="form-group">
                <label for="" class="form-label">{{__('Repeat new password')}}</label>
                <input type="password" class="form-control" name="repeat_password">
            </div>

            <button class="btn btn-dark">{{__('Save')}}</button>


        </form>
    </div>

@endsection
