<section class="page-header">

    <ol class="breadcrumb">
        <li><a href="{{route('concept.index')}}">Concepts</a></li>

        @foreach ($concept->ancestors()->get() as $ancestor)
            <li><a href="{{route('concept.show', ['concept' => $ancestor])}}">
                    {{$ancestor->title}}
                </a>
            </li>
        @endforeach

        <li class="active">{{$concept->title}}</li>
    </ol>

    @if ($can_update)

        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#concept-edit-form">
                <i class="glyphicon glyphicon-edit"></i> Edit concept
            </button>
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                @if ($is_owner)
                    <li>
                        <a href="#" data-toggle="modal" data-target="#concept-share-form">
                            <i class="glyphicon glyphicon-share"></i> Share
                            @if ($concept->shares->count() > 0)
                                <span class="badge">{{ $concept->shares->count() }}</span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('attachment.index', [$concept]) }}">
                            <i class="glyphicon glyphicon-paperclip"></i> Attachments
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('concept.slides', [$concept]) }}">
                            <i class="glyphicon glyphicon-blackboard"></i> Slides
                        </a>
                    </li>

                    @if (!empty(env('WEBSITE_' . $concept->id)))
                        <li>
                            <a href="{{route('website.publish', [$concept])}}"
                               onclick="event.preventDefault(); document.getElementById('publish-form').submit();"><i class="glyphicon glyphicon-globe"></i> Publish Website</a>

                            <form id="publish-form" action="{{route('website.publish', [$concept])}}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    @endif
                @endif
                <li><a href="{{route('concept.create', ['parent_id' => $concept->id])}}"><i class="glyphicon glyphicon-plus-sign"></i> Add child</a></li>
                <li><a href="{{route('concept.versions', [$concept])}}"><i class="glyphicon glyphicon-time"></i> Versions</a></li>
                @if ($is_owner)
                    <li role="separator" class="divider"></li>
                    <li>
                        <a href="{{route('concept.destroy', [$concept])}}"
                           onclick="event.preventDefault(); document.getElementById('delete-form').submit();"><i class="glyphicon glyphicon-remove"></i> Delete</a>

                        <form id="delete-form" action="{{route('concept.destroy', [$concept])}}" method="POST" style="display: none;">
                            <input type="hidden" name="_method" value="DELETE">
                            {{ csrf_field() }}
                        </form>
                    </li>
                @endif
            </ul>
        </div>

    @endif

    <h1>
        {{$concept->title}}<small>
        @if ($concept->is_flagged)
            <i class="glyphicon glyphicon-heart"></i>
        @endif
        @if ($concept->source_url)
            <a href="{{$concept->source_url}}">
                <i class="glyphicon glyphicon-link"></i>
            </a>
        @endif
        @if ($concept->shares->count() > 0)
            <i style="color:red" class="glyphicon glyphicon-share"></i>
        @endif
        </small>
    </h1>

    <p class="meta">
        Concept <a href="{{ route('concept.short', $concept) }}">{{$concept->id}}</a>,
        <?php
        $created = strftime('%Y-%m-%d', strtotime($concept->created_at));
        $updated = strftime('%Y-%m-%d', strtotime($concept->updated_at));
        ?>
        created {{ $created }}, @if ($created != $updated)
            updated {{ $updated }},
        @endif
        viewed {{$concept->viewed_count}} time{{$concept->viewed_count > 1 ? 's' : ''}}.

        @if ($concept->type != 'concept')
            <span class="label label-info">{{ucfirst($concept->type)}}</span>
        @endif

        @if ($concept->tags->count())
            @foreach ($concept->tags as $tag)
                <a class="label label-default" href="{{route('concept.index', ['tag' => $tag->slug])}}">{{$tag->name}}</a>
            @endforeach
        @endif
    </p>

    @include('partials.messages')

</section>
