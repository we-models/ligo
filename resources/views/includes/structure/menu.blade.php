{{-- <div  class="navbar-nav  sidebar sidebar-dark accordion principal_layout" id="accordionSidebar"> --}}
<div class="menu-container " id="menu-container">

    <div class="icon-arrow-box" id="icon-arrow-box">
        <i class="fa-solid fa-arrow-left"></i>
    </div>

    <div class="menu-main">

        <div class="img-movil">
          <div style="background-color: rgba(255,255,255,0.6);">
          <img src="/image_system/menu/ligo.png" />
          </div>
        </div>


        <div class="user-section">
            @php
                $img = auth()->user()->images()->pluck('url');
                $img = count($img) > 0 ? $img[0] : '';
            @endphp
            <div class="icon-user-box">
                @if($img == '')
                    <i class="fa-solid fa-user"></i>
                @else
                    <img src="{{$img}}" class="profile-img" alt="{{auth()->user()->name}}">
                @endif
            </div>
            <div class="user-info">
                <div class="user-name">{{auth()->user()->name}} </div>
                <div class="user-email">{{auth()->user()->email}}</div>
            </div>
        </div>

        <div class="menu-structure">
            @php
                $groups = getGroupsForMenu();
            @endphp

            <ul class="list">
                @foreach ($groups as $group)
                    @if (count($group->permissions) == 1 && count($group->links) == 0)
                        @can($group->permissions[0]->name)
                            <li class="list-item ">
                                <a href="{{ route($group->permissions[0]->name, app()->getLocale()) }}">
                                    <div class="icon-item-box">
                                        <i class="{{ $group->icon()->first()->name }}"></i>
                                    </div>
                                </a>
                                <div class="info-item">
                                    <div class="group-name">
                                        <a href="{{ route($group->permissions[0]->name, app()->getLocale()) }}">
                                            <span>{{ $group->name }}</span>
                                        </a>

                                    </div>
                                    <div class="spacer">
                                        <hr>
                                    </div>
                                </div>

                            </li>
                        @endcan
                    @else
                        <li class="list-item list-drop desk ">
                            <div class="dropend">
                                <a class="" id="link_icon_{{ $group->id }}" data-bs-toggle="dropdown"
                                    href="#" role="button" aria-expanded="false">
                                    <div class="icon-item-box">
                                        <i class="{{ $group->icon()->first()->name }}"></i>
                                    </div>
                                </a>
                                <ul id="compact-menu-dropdown" class="dropdown-menu full-menu-dropdown menu-float" >
                                    @include('includes.structure.menu_items')
                                </ul>
                            </div>
                            <div class="info-item">
                                <div class="group-name dropend">
                                    <a class="dropdown-toggle" id="link_icon_{{ $group->id }}"
                                        data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                                        <span>{{ $group->name }}</span>
                                    </a>
                                    <ul id="ul_{{ $group->id }}" class="dropdown-menu menu-float" >
                                        @include('includes.structure.menu_items')
                                    </ul>
                                </div>
                                <div class="spacer">
                                    <hr>
                                </div>
                            </div>
                        </li>

                        {{-- drop menu mobile  --}}
                        <div class="movil-list" id="movil-list">

                            <li class="list-item list-drop ">
                                <div class="icon-item-box">
                                    <i class="{{ $group->icon()->first()->name }}"></i>
                                </div>
                                <div class="info-item">
                                    <div class="group-name ">
                                        <a class="dropdown-toggle" data-bs-toggle="collapse"
                                            href="#grp_{{ $group->id }}" role="button" aria-expanded="false"
                                            aria-controls="collapseExample">

                                            <span>{{ $group->name }}</span>
                                        </a>

                                    </div>
                                    <div class="spacer">
                                        <hr>
                                    </div>
                                </div>
                            </li>

                            <div class="collapse" id="grp_{{ $group->id }}">
                                <ul>
                                    @foreach ($group->permissions as $permission)
                                        @can($permission->name)
                                            @if (Route::has($permission->name))
                                                <li>
                                                    <a class=""
                                                        href="{{ route($permission->name, app()->getLocale()) }}">{!! $permission->identifier !!}</a>
                                                </li>
                                            @endif
                                        @endcan
                                    @endforeach
                                    @foreach ($group->links as $link)
                                        <li>
                                            <a class="" href="{{ $link->url }}">{{ $link->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    @endif
                @endforeach

            </ul>
        </div>
    </div>

</div>
