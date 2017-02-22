@extends('layouts.app')

@inject('picture', 'Knowfox\Services\PictureService')

@section('content')

    <main id="dropzone" class="container dropzone">

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

            <h1>{{$concept->title}}</h1>

            @include('partials.messages')

        </section>

        <div class="dropzone-previews"></div>

        <div class="row">
            <div class="col-md-8">
                @if (!empty($concept->image))
                    <img src="{{ url($picture->asset($concept->image, 'text')) }}">
                @endif

                @if ($concept->summary)
                    <p class="summary">{{$concept->summary}}</p>
                @endif

                @if ($concept->rendered_body)
                    <section>
                        {!! $concept->rendered_body !!}
                    </section>
                @endif
            </div>
            <div class="col-md-4">
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
                        <th>Weight</th>
                        <td>{{$concept->weight}}</td>
                    </tr>
                    <tr>
                        <th>Language</th>
                        <td>{{$concept->language}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$concept->status}}</td>
                    </tr>
                    <tr>
                        <th>Flagged</th>
                        <td>
                            <i class="glyphicon glyphicon-heart{{$concept->is_flagged ? '' : '-empty'}}"></i>
                        </td>
                    </tr>
                    @if ($concept->source_url)
                        <tr>
                            <th>Source</th>
                            <td><a href="{{$concept->source_url}}">{{parse_url($concept->source_url, PHP_URL_HOST)}}</a></td>
                        </tr>
                    @endif
                    @if ($concept->todoist_id)
                        <tr>
                            <th>Todoist-Link</th>
                            <td><a target="todo" href="https://todoist.com/showTask?id={{$concept->todoist_id}}">{{$concept->todoist_id}}</a></td>
                        </tr>
                    @endif

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
            </div>
        </div>

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

@section ('footer_scripts')
    <script>
        Dropzone.options.dropzone = {
            maxFilesize: 30,
            url: '/upload/{{$concept->uuid}}',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            previewsContainer: '.dropzone-previews'
        };

        $('#concept-edit-form').on('shown.bs.modal', function () {
            $.get('/attachments/{{$concept->id}}', function (data) {
                var txt = '';
                data.forEach(function (img) {
                    var tiny = img.replace(/rotated\.jpeg$/, 'h80.jpeg');
                    txt += '<div class="pull-left" style="margin:4px"><a href="' + img + '"><img src="' + tiny + '"></a></div>';
                });
                $('#images').html(txt + '');
            });
        });

    </script>
@endsection