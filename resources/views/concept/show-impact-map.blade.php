@extends('concept.show')

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

    <table class="table">
        <thead>
        <tr>
            <th>Actors</th>
            <th>Impacts</th>
            <th>Measurements</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($map as $i => $row)
            <tr>
                <td{!! $row->rowspan > 1 ? " rowspan=\"{$row->rowspan}\"" : '' !!}>
                    {{ join(' &raquo; ', $row->path) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

