<div class="card text-white card-{{$type}} bg-{{$type}}">
    @if (isset($bodyContent))
        <div class="card-body{{isset($bodyTextCenter)? ' text-center' : ''}}">
            @if (isset($headerTitle))
                <h3 class="card-title text-white"><strong>{{$headerTitle}}</strong></h3>
            @endif
            {!!$bodyContent!!}
        </div>
    @endif
    @if (isset($footerContent))
        <div class="card-footer{{isset($footerTextCenter)? ' text-center' : ''}}">
            {!!$footerContent!!}
        </div>
    @endif
</div>
