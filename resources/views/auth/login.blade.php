@extends('layouts.app')

@section('content')
    <div class="main-login d-flex  justify-content-center align-items-center flex-column ">
        <div class="logo " style="background-color: rgba(255,255,255,0.6);">
            <img src="../image_system/login/ligo.png" loading="lazy" alt="LIGO">
        </div>


        <div class="login-box d-flex  justify-content-center align-items-center flex-column">
           
            <div class="title">
                <p>
                    {{ __('welcome title login') }}
                </p>
            </div>


            <div class="form">
                <form method="POST" action="{{ route('login', app()->getLocale()) }}">
                    @csrf

                    <div class="error-msg">

                        @error('password')
                            <div class="icon-alert">
                                <i class="fa-solid fa-triangle-exclamation icon-warning-style"></i>
                            </div>
                        @enderror
                        @error('email')
                            <div class="icon-alert">
                                <i class="fa-solid fa-triangle-exclamation icon-warning-style"></i>
                            </div>
                        @enderror
                        @error('email')
                            <span>
                                <strong class="msg-error">{{ $message }}</strong>
                            </span>
                        @enderror

                        @error('password')
                            <span>
                                <strong class="msg-error">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>


                    <login-input-view old_value_email="{{ old('email') }}"></login-input-view>


                    <div class="link-forgot-password d-flex  justify-content-end">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request', app()->getLocale()) }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>


                    <div class="button-submit d-flex justify-content-center">
                        <button type="submit">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    {{-- <home-view/> --}}
@endsection
