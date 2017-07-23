@inject('picture', 'Knowfox\Services\PictureService')

@section('header')

    <div class="blog-header">
        <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            @foreach ($breadcrumbs as $breadcrumb)
                <li><a href="{{$breadcrumb->url}}">
                        {{$breadcrumb->title}}
                    </a>
                </li>
            @endforeach

            <li class="active">{{$page_title}}</li>
        </ol>

        <h1 class="blog-title">{{$page_title}}</h1>

        @if ($concept->summary)
            <p class="lead blog-description">{{$concept->summary}}</p>
        @endif
    </div>

@endsection

@section('content')

    @if (!empty($concept->image_text))
        <img src="{{$concept->image_text}}">
    @endif

    @if (!isset($concept->config->show_date) || $concept->config->show_date)
        <p class="blog-post-meta">{{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }} by {{$concept->owner->name}}</p>
    @endif

    @if ($rendered_body)
        {!! $rendered_body !!}
    @endif

    <div id="kids" data-pages="{{$page_count}}">
        {!! $children !!}
    </div>

    @if ($page_count > 1)
        <div style="margin-bottom:20px" class="text-center">
            <button id="more" class="btn btn-default">Mehr &hellip;</button>
        </div>
    @endif

    @if ((!isset($concept->config->show_nav) || $concept->config->show_nav) && ($concept->prev || $concept->next))
        <nav>
            <ul class="pager">
                @if ($concept->prev)
                    <li class="previous"><a href="{{$concept->prev->url}}" title="{{$concept->prev->title}}"><span aria-hidden="true">&larr;</span> Zurück</a></li>
                @endif
                @if ($concept->next)
                    <li class="next"><a href="{{$concept->next->url}}" title="{{$concept->next->title}}">Weiter <span aria-hidden="true">&rarr;</span></a></li>
                @endif
            </ul>
        </nav>
    @endif

@endsection


@push('scripts')
    @if ($page_count > 1)
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
    @endif
@endpush
