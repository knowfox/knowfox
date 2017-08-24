<section data-uuid="{{$concept->uuid}}">
    @if ($descendants)
        <section>
            <h1>{{$concept->title}}</h1>
            {!! $concept->rendered_body !!}
        </section>
    @else
        <h1>{{$concept->title}}</h1>
        {!! $concept->rendered_body !!}
    @endif
    {!! $descendants !!}
</section>