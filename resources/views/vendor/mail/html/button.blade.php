@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<div class="button-email">
    <a class="link-button-a" href="{{ $url }}">{{ $slot }}</a>
</div>
