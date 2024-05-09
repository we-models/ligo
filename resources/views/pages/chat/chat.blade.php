@extends('layouts.app')

@section('content')

    <messenger-component
        title = "{{__('Chat')}}"
        lngs            = "{{json_encode(config('app.available_locales'))}}"
        lng             = "{{app()->getLocale()}}"
        url             = "{{ route('chat.all', app()->getLocale())  }}"
        server          = "{{getConfigValue('CHAT_SERVER')}}"
        port            = "{{getConfigValue('CHAT_PORT')}}"
        channels_link   = "{{ route('chat.get_channels', app()->getLocale())   }}"
        individual      = "{{ route('chat.get_individual', app()->getLocale())  }}"
        chats_link           = "{{ route('chat.get_chats', app()->getLocale())  }}"
        send_chat = "{{ route('chat.send', app()->getLocale())   }}"
        intermediary = "{{ auth()->user()->hasRole('Contact') ? '1' : '0' }}"
        auth_identifier = "{{ auth()->user()->getAuthIdentifier() }}"
        csrf =   "{{csrf_token()}}"
    ></messenger-component>
@endsection
