@extends('layouts.app')

@section('content')
    <div class="main-login d-flex  justify-content-center align-items-center flex-column ">
        <div class="logo ">
            <img src="/image_system/login/ligo.png" loading="lazy" alt="LIGO">
        </div>


        <div class="login-box d-flex  justify-content-center align-items-center flex-column">
            <div class="avatar">
                <i class="fa-regular fa-user icon-avatar"></i>
            </div>
            <div class="title">
                <p>
                    {{ __('Reset Password') }}
                </p>
            </div>


            <div class="form">
                <form method="POST" action="{{ route('password.email', app()->getLocale()) }}">
                    @csrf

                    <div class="error-msg">
                        @error('email')
                            <div class="icon-alert">
                                <i class="fa-solid fa-triangle-exclamation icon-warning-style"></i>
                            </div>
                            <span>
                                <strong class="msg-error">{{ $message }}</strong>
                            </span>
                        @enderror
                        @if (session('status'))
                            <span>
                                <strong class="msg-error">{{ session('status') }}</strong>
                            </span>
                        @endif
                    </div>

                    @if (!session('status'))

                        <div class="input-group mb-3">
                        <span class="input-group-text icon-input-left" id="basic-addon1">
                            <i class="fa-solid fa-user icon-user-style"></i>
                        </span>

                            <input id="email" type="email" class=" input-email form-control" name="email"
                                   value="{{ old('email') }}" required autocomplete="email" autofocus
                                   placeholder="{{ __('E-Mail Address"') }}" />
                        </div>

                        <div class="msg-reset">
                            <span class="msg-error">{{ __('msg-reset-password') }}</span>
                        </div>

                        <div class="button-recover d-flex justify-content-center">
                            <button type="submit">
                                {{ __('recover') }}
                            </button>
                        </div>

                    @endif

                </form>

                <div class="toback-link">
                    <a href="{{route('login', app()->getLocale())}}">{{__('Login')}}</a>
                </div>
            </div>

        </div>

    </div>
@endsection
