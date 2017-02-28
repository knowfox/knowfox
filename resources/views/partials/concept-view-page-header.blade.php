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

    <div class="btn-group pull-right">
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#concept-edit-form"><i class="glyphicon glyphicon-edit"></i> Edit concept</button>
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="{{route('concept.create', ['parent_id' => $concept->id])}}"><i class="glyphicon glyphicon-plus-sign"></i> Add child</a></li>
            <li role="separator" class="divider"></li>
            <li>
                <a href="{{route('concept.destroy', [$concept])}}"
                   onclick="event.preventDefault(); document.getElementById('delete-form').submit();"><i class="glyphicon glyphicon-remove"></i> Delete</a>

                <form id="delete-form" action="{{route('concept.destroy', [$concept])}}" method="POST" style="display: none;">
                    <input type="hidden" name="_method" value="DELETE">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>
    </div>

    <h1>
        {{$concept->title}}
        @if ($concept->is_flagged)
            <i class="glyphicon glyphicon-heart"></i>
        @endif
        @if ($concept->source_url)
            <a href="{{$concept->source_url}}">
                <i class="glyphicon glyphicon-link"></i>
            </a>
        @endif
    </h1>

    <p class="meta">
        Created {{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }},
        updated {{ strftime('%Y-%m-%d', strtotime($concept->updated_at)) }}

        @if ($concept->tags->count())
            @foreach ($concept->tags as $tag)
                <a class="label label-default" href="{{route('concept.index', ['tag' => $tag->slug])}}">
                    {{$tag->name}}
                </a>
            @endforeach
        @endif
    </p>

    @include('partials.messages')

</section>
