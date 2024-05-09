<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
@include('includes.head')
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div id="app" >
                <div style="height: 100%; ">
                    @include('includes.navbar')
                    <language-selector-component
                        lngs="{{json_encode(config('app.available_locales'))}}"
                        lng="{{app()->getLocale()}}"
                        title = "{{__('Select company')}}">
                    </language-selector-component>
                    @php
                        $business = \App\Models\Business::query();
                        $business = getBusiness($business)->get();
                    @endphp
                    <div>
                        <div class="business-container">
                            @foreach($business as $company)
                                <div>
                                    <div class="card mb-3 company-item" >
                                        <div class="row g-0">
                                            <div class="col-5 col-xl-4 col-lg-4">
                                                <img src="{{asset('images/logo.png')}}" class="img-fluid rounded-start" alt="{{$company->name}}">
                                            </div>
                                            <div class="col-7 col-xl-8 col-lg-8">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{$company->name}}</h5>
                                                    <div class="card-text">{!! $company->description !!}</div>
                                                    <a href="{{route('business.select.code', ['locale' =>app()->getLocale(), 'code' => $company->code ])}}" class="btn btn-primary">{{__('Get into')}}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('includes.scripts')
</body>
</html>