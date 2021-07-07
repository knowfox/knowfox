@extends('knowfox::concept.show')

@section('main-content')

    <table class="table">
        <tbody>
            <tr>
                <th>Author</th>
                <td>{{$concept->config->author or '-'}}</td>
            </tr>
            <tr>
                <th>Publisher</th>
                <td>{{$concept->config->publisher or '-'}}</td>
            </tr>
            <tr>
                <th>Year</th>
                <td>{{$concept->config->year or '-'}}</td>
            </tr>
            <tr>
                <th>Filename</th>
                <td>{{$concept->config->filename or '-'}}</td>
            </tr>
            <tr>
                <th>Path</th>
                <td>{{$concept->config->path or '-'}}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{$concept->config->type or '-'}}</td>
            </tr>
            <tr>
                <th>Format</th>
                <td>{{$concept->config->format or '-'}}</td>
            </tr>
        </tbody>
    </table>

    @parent

@endsection

@section('siblings')
@endsection

