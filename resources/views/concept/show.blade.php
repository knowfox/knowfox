@extends('layouts.app')

@inject('picture', 'Knowfox\Services\PictureService')

@section('content')

    <main class="container">

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

            <button class="btn btn-default pull-right" data-toggle="modal" data-target="#concept-edit-form">
                <i class="glyphicon glyphicon-edit"></i> Edit concept
            </button>

            <h1>{{$concept->title}}</h1>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

        </section>

        @if (!empty($concept->image))
            <img src="{{ url($picture->asset($concept->image, 'text')) }}">
        @endif

        <section class="meta">

            <h2>Meta Data</h2>

            <table class="table">
                <tbody>
                <tr>
                    <th>Created</th>
                    <td>{{$concept->created_at}}</td>
                </tr>
                <tr>
                    <th>Updated</th>
                    <td>{{$concept->updated_at}}</td>
                </tr>
                <tr>
                    <th>Language</th>
                    <td>{{$concept->language}}</td>
                </tr>
                <tr>
                    <th>Source</th>
                    <td><a href="{{$concept->source_url}}">{{parse_url($concept->source_url, PHP_URL_HOST)}}</a></td>
                </tr>
                </tbody>
            </table>

            @if ($concept->getSiblings()->count())

                <h2>Siblings</h2>

                <ul>
                @foreach ($concept->getSiblings() as $sibling)
                    <li><a href="{{route('concept.show', ['concept' => $sibling])}}">
                        {{$sibling->title}}
                    </a></li>
                @endforeach
                </ul>

            @endif

            @if ($concept->children()->count())

                <h2>Children</h2>

                <ul>
                @foreach ($concept->children()->get() as $child)
                    <li><a href="{{route('concept.show', ['concept' => $child])}}">
                        {{$child->title}}
                    </a></li>
                @endforeach
                </ul>

            @endif

            @if ($concept->tags->count())
                <h2>Tags</h2>

                <ul>
                    @foreach ($concept->tags as $tag)
                        <li><a href="{{route('concept.index', ['tag' => $tag->slug])}}">
                            {{$tag->name}}
                        </a></li>
                    @endforeach
                </ul>
            @endif

            @if ($concept->related()->count() || $concept->inverseRelated()->count())

                <h2>Related</h2>

                <ul>
                @foreach ($concept->related()->get() as $related)
                    <li>{{$related->pivot->type['labels'][0]}} <a href="{{route('concept.show', ['concept' => $related])}}">
                        {{$related->title}}
                    </a></li>
                @endforeach
                @foreach ($concept->inverseRelated()->get() as $related)
                    <li>{{$related->pivot->type['labels'][1]}} <a href="{{route('concept.show', ['concept' => $related])}}">
                            {{$related->title}}
                    </a></li>
                @endforeach
                </ul>

            @endif
        </section>

        @if ($concept->summary)
            <p class="summary">{{$concept->summary}}</p>
        @endif

        @if ($concept->rendered_body)
            <section>
                {!! $concept->rendered_body !!}
            </section>
        @endif

    </main>

    <div class="modal fade" id="concept-edit-form" role="dialog" aria-labelledby="form-label">
        <div class="modal-dialog" role="document">
            <form class="modal-content" enctype="multipart/form-data" action="{{route('concept.update', ['concept' => $concept])}}" method="POST">
                {{csrf_field()}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="form-label">Edit "{{$concept->title}}"</h4>
                </div>
                <div class="modal-body">
                    @include('partials.concept-edit-form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection