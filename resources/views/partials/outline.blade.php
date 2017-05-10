<outline id="{{$concept->id}}" text="{{$concept->title}}" summary="{{$concept->summary}}" config="{{ json_encode($concept->config) }}">
    {!! $descendants !!}
</outline>