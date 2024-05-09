@foreach ($group->permissions as $permission)
    @can($permission->name)
        @if (Route::has($permission->name))
            <li>
                <a class="dropdown-item"
                   href="{{ route($permission->name, app()->getLocale()) }}">{!! $permission->identifier !!}</a>
            </li>
        @endif
    @endcan
@endforeach
@foreach ($group->links as $link)
    <li>
        <a class="dropdown-item" href="{{ $link->url }}">{{ $link->name }}</a>
    </li>
@endforeach
