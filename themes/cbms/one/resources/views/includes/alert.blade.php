@php
if ($type == 'error') {
    $alertClass = "danger";
} elseif ($type) {
    $alertClass = $type;
} else {
    $alertClass = "info";
}
@endphp
<div class="alert alert-{{$alertClass}} {{isset($textcenter) ? 'text-center' : '' }} {{isset($hide) ? 'd-none' : ''}} {{ isset($additionalClasses) ? $additionalClasses : '' }}" {{ isset($idname) ? 'id="'.$idname.'"' : '' }}>
@if (isset($errorshtml))
    <strong>{{Lang::get('client.clientareaerrors')}}</strong>
    <ul>
        {!!$errorshtml!!}
    </ul>
@else
    @if (isset($title))
        <h2>{{$title}}</h2>
    @endif
    {!!$msg!!}
@endif
</div>
