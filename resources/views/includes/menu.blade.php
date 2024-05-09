<div class="navbar-nav  sidebar sidebar-dark accordion principal_layout" id="accordionSidebar">

    <ul class="nav justify-content-end">
        <li class="nav-item">
            <button id="sidebarToggleTop" class="btn d-md-none rounded-circle">
                <i class="fa fa-bars"></i>
            </button>
        </li>
    </ul>


    <a class=" align-items-center justify-content-center" href="{{route('home', app()->getLocale())}}" >
        <div class="sidebar-brand-icon" style="padding-bottom:20px; text-align: center">
            <img src="{{asset('images/logo.png')}}" width="80%" alt="{{env('APP_NAME')}}">
        </div>
    </a>

    <div class="menu-system">
        @php
            $groups = getGroupsForMenu();
        @endphp
        <ul class="nav flex-column text-white">
        @foreach($groups as $group)
            @if(count($group->permissions) == 1 && count($group->links) == 0)

                @can($group->permissions[0]->name)
                    <li class="nav-item dropdown dropend menu-item">
                        <a class="nav-link" href="{{route($group->permissions[0]->name, app()->getLocale() )}}"  aria-expanded="false">
                            <i class="{{$group->icon}}"></i>
                            <span>{{$group->name}}</span>
                        </a>
                    </li>
                @endcan
            @else
                <li class="nav-item dropdown dropend menu-item">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <i class="{{$group->icon}}"></i>
                        <span>{{$group->name}}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @foreach($group->permissions as $permission)
                            @can($permission->name)
                                @if(Route::has($permission->name))
                                    <li>
                                        <a class="dropdown-item" href="{{route($permission->name, app()->getLocale())}}">{!! $permission->identifier !!}</a>
                                    </li>
                                @endif
                            @endcan
                        @endforeach
                        @foreach($group->links as $link)
                            <li>
                                <a class="dropdown-item" href="{{$link->url}}">{{$link->name}}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif
        @endforeach

        </ul>
    </div>


    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</div>

<script>
    let is_mobile = document.documentElement.clientWidth <= 768;
    if(is_mobile){
        document.getElementById('accordionSidebar').classList.add('toggled');
    }
</script>
