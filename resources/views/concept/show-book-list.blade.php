@extends('concept.show')

@section('main-content')
    @parent

    @if ($concept->children()->count())

        <?php
            $books = $concept->children()->orderBy('title')->paginate();
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Author</th>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($books as $book)
                <tr>
                    <td>
                    @if (!empty($book->config->image))
                        <img src="/{{$book->id}}/{{$book->config->image}}?style=thumbnail">
                    @endif
                    </td>
                    <td>{{$book->config->author}}</td>
                    <td><a href="{{route('concept.show', ['concept' => $book])}}">
                        {{$book->title}}
                        </a>
                    </td>
                    <td>{{$book->config->year}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-center">{{$books}}</div>

    @endif

@endsection

@section('children')
@endsection