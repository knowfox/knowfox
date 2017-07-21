@extends('layouts.app')

@inject('picture', 'Knowfox\Services\PictureService')

@section('main-content')

    @if (!empty($concept->image))
        <img src="{{ url($picture->asset($concept->image, 'text')) }}">
    @endif

    @if ($concept->summary)
        <p class="summary">{{$concept->summary}}</p>
    @endif

    @if ($concept->rendered_body)
        {!! $concept->rendered_body !!}
    @endif

@endsection

@section('content')

    <main id="dropzone" class="container dropzone">

        @include('partials.concept-view-page-header')

        <div class="dropzone-previews"></div>

        @include('partials.view-tabs', ['active' => 'view'])

        @section('full-content')

            <div class="row">
                <div class="col-md-8">

                    @yield('main-content')

                    @if (in_array($concept->type, ['folder', 'book list']))
                        @if ($concept->type == 'book list' || !empty($concept->config->sort) && $concept->config->sort == 'alpha')
                            @include('partials.alpha-nav')
                        @endif

                        <table class="table">
                            @section('kids-header')
                                @include('partials.table-header')
                            @show
                            @section('kids-body')
                                <tbody>
                                @foreach ($children as $child)
                                    @include('partials.table-row', ['concept' => $child])
                                @endforeach
                                </tbody>
                            @show
                        </table>

                        <div class="text-center">{{$children}}</div>
                    @endif

                </div>
                <div class="col-md-4">

                    @section('config')

                        @if (!empty((array)$concept->config))
                            <h2>Configuration</h2>

                            {!! $concept->rendered_config !!}
                        @endif

                    @show

                    @section('children')

                        @if (!in_array($concept->type, ['folder', 'book list']) && $concept->children()->count())

                            <h2>Children ({{$concept->getDescendantCount()}})</h2>

                            <ul>
                                @foreach ($children as $child)
                                    <li><a href="{{route('concept.show', ['concept' => $child])}}">
                                            {{$child->title}} {{ ($descendents_count = $child->getDescendantCount()) ? "({$descendents_count})" : '' }}
                                        </a></li>
                                @endforeach
                            </ul>

                        @endif

                    @show

                    @if ($concept->related()->count() || $concept->inverseRelated()->count())

                        <h2>Related</h2>

                        <ul>
                            @foreach ($concept->related()->get() as $related)
                                <li>{{$related->pivot->forwardLabel()}} <a href="{{route('concept.show', ['concept' => $related])}}">
                                        {{$related->title}}
                                    </a></li>
                            @endforeach
                            @foreach ($concept->inverseRelated()->get() as $related)
                                <li>{{$related->pivot->reverseLabel()}} <a href="{{route('concept.show', ['concept' => $related])}}">
                                        {{$related->title}}
                                    </a></li>
                            @endforeach
                        </ul>

                    @endif

                    @if ($same_day_query = $concept->sameDay())

                        <h2>Same day</h2>

                        <ul>
                            @foreach ($same_day_query->get() as $same_day)
                                <li><a href="{{route('concept.show', ['concept' => $same_day])}}">
                                        {{$same_day->title}}
                                    </a></li>
                            @endforeach
                        </ul>
                    @endif

                    @section('siblings')

                        @if ($concept->getSiblings()->count())

                            <h2>Siblings</h2>

                            <ul>
                                @foreach ($concept->siblings()->where('owner_id', Auth::id())->get() as $sibling)
                                    <li><a href="{{route('concept.show', ['concept' => $sibling])}}">
                                            {{$sibling->title}} {{ ($descendents_count = $sibling->getDescendantCount()) ? "({$descendents_count})" : '' }}
                                        </a></li>
                                @endforeach
                            </ul>

                        @endif

                    @show

                </div>
            </div>

        @show

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

    <div class="modal fade" id="concept-share-form" role="dialog" aria-labelledby="form-label">
        <div id="app" class="modal-dialog" role="document">
            <form v-on:submit.prevent="updateShares" class="modal-content" action="{{route('share', [$concept])}}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="form-label">Share "{{$concept->title}}"</h4>
                </div>
                <div class="modal-body">
                    <shares :shares="shares"></shares>
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
            maxFilesize: 100,
            url: '/upload/{{$concept->uuid}}',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            previewsContainer: '.dropzone-previews',
            init: function() {
                this.on("success", function(file) { location.reload(); });
            }
        };

        $('#concept-edit-form').on('shown.bs.modal', function () {
            $.get('/images/{{$concept->id}}', function (data) {
                var txt = '';
                data.forEach(function (img) {
                    txt += '<div class="pull-left" style="margin:4px"><a href="' + img + '"><img src="' + img + '?style=h80"></a></div>';
                });
                $('#images').html(txt + '');
            });
        });

        $('#concept-share-form').one('shown.bs.modal', function () {
            var app = new Vue({
                el: '#app',
                data: {
                    shares: {!! json_encode($concept->shares) !!}
                },
                methods: {
                    updateShares: function (e) {
                        $.ajax({
                            url: '/share/{{$concept->id}}',
                            type: 'POST',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                shares: this.shares
                            },
                            success: function (res) {
                                location.href = '/{{$concept->id}}';
                            }
                        })
                    }
                }
            });
        });

    </script>
@endsection