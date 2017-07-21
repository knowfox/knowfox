@extends('website.schettler_net.layout')

@inject('picture', 'Knowfox\Services\PictureService')

@section('header')

    <div class="blog-header">
        @if (!empty($concept->image))
            <img src="{{ url($picture->asset($concept->image, 'text')) }}">
        @endif

        <h1 class="blog-title">{{$concept->title}}</h1>
        @if ($concept->summary)
            <p class="lead blog-description">{{$concept->summary}}</p>
        @endif
    </div>

@endsection

@section('content')

    <p class="blog-post-meta">{{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }} by {{$concept->owner->name}}</p>

    @if ($concept->rendered_body)
        <section class="body" data-uuid="{{$concept->uuid}}">
            {!! $concept->rendered_body !!}
        </section>
    @endif

    <div id="kids" data-pages="{{$page_count}}">
        {!! $children !!}
    </div>

    <div style="margin-bottom:20px" class="text-center">
        <button id="more" class="btn btn-default">Mehr &hellip;</button>
    </div>

@endsection


@push('scripts')
<script>
    var page_loaded = 0,
        total_pages = parseInt($('#kids').data('pages'));

    if (page_loaded < total_pages) {
        $('#more').on('click', function () {
            page_loaded++;
            $.get('_page-' + page_loaded + '.html')
                .done(function (fragment) {
                    $('#kids').append(fragment);
                    if (page_loaded == total_pages - 1) {
                        $('#more').hide();
                    }
                });
        })
    }
</script>
@endpush
