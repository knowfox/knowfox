<section data-uuid="{{$concept->uuid}}">
    <h1>{{$concept->title}}</h1>
    {!! $concept->rendered_body !!}
    {!! $descendants !!}
</section>