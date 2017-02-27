<outline text="{{$concept->title}}">
    @if ($concept->summary)
        <outline isComment="true" type="text" text="{{$concept->summary}}"></outline>
    @endif

    {!! $descendants !!}
</outline>