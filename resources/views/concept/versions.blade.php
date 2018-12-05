@extends('concept.show')

<?php use \Illuminate\Support\Facades\Input; ?>

@section('main-content')
    @parent

    @if ($concept->versions()->count())

        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>What</th>
                <th>Changes</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($concept->versions as $version)
                <tr>
                    <td>
                        {{$version->created_at}}
                    </td>
                    <td>
                        {{$version->reason}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="well">
                        {!! $concept->renderedDiff($version) !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endif

@endsection

@section('children')
@endsection

@section('siblings')
@endsection