@extends('concept.show')

@section('main-content')
    <table class="table">
        <tbody>
            <tr>
                <th>Author</th>
                <td>{{$concept->config->author}}</td>
            </tr>
            <tr>
                <th>Publisher</th>
                <td>{{$concept->config->publisher}}</td>
            </tr>
            <tr>
                <th>Year</th>
                <td>{{$concept->config->year}}</td>
            </tr>
            <tr>
                <th>Filename</th>
                <td>{{$concept->config->filename}}</td>
            </tr>
            <tr>
                <th>Path</th>
                <td>{{$concept->config->path}}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{$concept->config->type}}</td>
            </tr>
            <tr>
                <th>Format</th>
                <td>{{$concept->config->format}}</td>
            </tr>
        </tbody>
    </table>

    @parent

@endsection

@section('siblings')
@endsection