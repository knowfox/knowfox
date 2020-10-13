@extends('knowfox::concept.show')

@section('full-content')

    @if (!empty($concept->image))
        <img src="{{ url($picture->asset($concept->image, 'text')) }}">
    @endif

    @if ($concept->summary)
        <p class="summary">{{$concept->summary}}</p>
    @endif

    @if ($concept->rendered_body)
        <section class="body">
            {!! $concept->rendered_body !!}
        </section>
    @endif

    <table class="table impact-map">
        <thead>
        <tr>
            <th>Actors</th>
            <th>Impacts</th>
            <th>Deliverables</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($map as $i => $row)
            <tr>
                <td{!! $row->rowspan > 1 ? " rowspan=\"{$row->rowspan}\"" : '' !!}>
                    {{ join(' &raquo; ', $row->path) }}&nbsp;<a href="/{{$row->id}}" target="_blank"><i class="glyphicon glyphicon-new-window"></i></a>
                    @if (trim($row->rendered_body))
                        {!! $row->rendered_body !!}
                    @endif
                    @if (!empty($row->rendered_config))
                        {!! $row->rendered_config !!}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
