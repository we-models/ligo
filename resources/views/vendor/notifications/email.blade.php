<x-mail::message>
{{-- Greeting --}}
<div class="title">
<p class="title-typography">
@if (! empty($greeting))
 {{ $greeting }}
@else
@if ($level === 'error')
 @lang('Whoops!')
@else
 @lang('Hello!')
@endif
@endif
</p>
</div>

{{-- Intro Lines --}}
<div class="primary-text">
@foreach ($introLines as $line)
<p>{{ $line }}</p>
@endforeach
</div>

{{-- Action Button --}}
@isset($actionText)
<?php
$color = match ($level) {
'success', 'error' => $level,
default => 'primary',
};
?>
<x-mail::button :url="$actionUrl" >
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
<div class="text-body">
@foreach ($outroLines as $line)
{{ $line }}

@endforeach
</div>

{{-- Salutation --}}
<div class="final-text">
<p>
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards'),<br>
{{ config('app.name') }}
@endif
</p>
</div>

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang("If you're having trouble clicking the button, copy and paste the URL below into your web browser")
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
