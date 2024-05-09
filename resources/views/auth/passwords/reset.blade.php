@extends('layouts.app')

@section('content')
    <div class="main-login d-flex  justify-content-center align-items-center flex-column ">
        <div class="logo ">
            <div style="background-color: rgba(255,255,255,0.6);">
            <img src="/image_system/login/ligo.png" loading="lazy" alt="LIGO">
            </div>
        </div>


        <div class="login-box d-flex  justify-content-center align-items-center flex-column">
            <div class="title">
                <p>
                    {{ __('Reset Password') }}
                </p>
            </div>


            <div class="form">
                <form method="POST" action="{{ route('password.update', app()->getLocale()) }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">


                    <div class="error-msg">

                        @if ($errors->has('email') || $errors->has('password'))
                            <div class="icon-alert">
                                <i class="fa-solid fa-triangle-exclamation icon-warning-style"></i>
                            </div>
                            <span>
                                <strong class="msg-error">{{ $errors->first('email') ?: $errors->first('password') }}</strong>
                            </span>
                        @endif


                    </div>


                    <login-input-view show_two_password_input="true" name_password_two="password_confirmation"
                        placeholder_password_one="write_new_password" placeholder_password_two="confirm_new_password"
                        old_value_email="{{ $email ?? old('email') }}"></login-input-view>


                    <div class="button-submit d-flex justify-content-center">
                        <button type="submit">
                            {{ __('save') }}
                        </button>
                    </div>

                    <div class="toback-link">
                        <a href="{{route('login', app()->getLocale())}}">{{__('Login')}}</a>
                    </div>


                </form>
            </div>

        </div>

    </div>


@endsection
