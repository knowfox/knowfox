@extends('knowfox::concept.show')

@section('kids-header')
    <thead>
        <tr>
            <th>Cover</th>
            <th>Author</th>
            <th>Title</th>
            <th>Year</th>
        </tr>
    </thead>
@endsection

@section('kids-body')
    <tbody>
    @foreach ($children as $book)
        <tr>
            <td>
                @if (!empty($book->config->image))
                    <img src="/{{$book->id}}/{{$book->config->image}}?style=thumbnail">
                @endif
            </td>
            <td>{{$book->config->author or ''}}</td>
            <td><a href="{{route('concept.show', ['concept' => $book])}}">
                    {{$book->title}}
                </a>
                @if ($book->is_flagged)
                    <i class="glyphicon glyphicon-heart"></i>
                @endif
                <?php $sep = '<br>'; ?>
                @foreach ($book->tags as $tag)
                    {!! $sep !!}<a href="{{route('concept.index', ['tag' => $tag->slug])}}" class="label label-default">{{$tag->name}}</a>
                    <?php $sep = ''; ?>
                @endforeach
            </td>
            <td>{{$book->config->year or ''}}</td>
        </tr>
    @endforeach
    </tbody>
@endsection

@section('siblings')
@endsection