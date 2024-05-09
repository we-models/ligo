@extends('layouts.app')
@section('content')
    <div>
        <report-component
            object_type="{{json_encode($object_type)}}"
            owner = "{{ json_encode($owner_object)  }}"
            lngs="{{json_encode($languages)}}"
            lng="{{$language}}"
            csrf = "{{ $csrf  }}"
            title="{{__('Report of objects')}}"
            filter_link = "{{ $filter_link  }}"
            filtered_link = "{{ $filtered_link  }}"
        >

        </report-component>

    </div>
@endsection
