@extends('layouts.app')
@section('content')

    <global-component details="{{$details}}" ></global-component>
@endsection

@section('custom_styles')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ getConfigValue('GOOGLE_MAPS_API_KEY') }}&libraries=places&v=weekly"
        defer
    ></script>
@endsection
